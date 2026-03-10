<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.user_management.index');
    }
    
    public function getData(Request $request)
    {
        $users = User::where('email', '!=', 'root@gmail.com')
            ->select(['id', 'first_name', 'last_name', 'email', 'user_type', 'status']);

        if ($request->filled('status') && $request->status !== 'All') {
            if ($request->status === 'Active') {
                $users->where('status', true);
            } elseif ($request->status === 'Inactive') {
                $users->where('status', false);
            }
        }
        
        return DataTables::of($users)
            ->editColumn('id', function ($row) {
                return Crypt::encryptString($row->id);
            })
            ->make(true);
    }

    public function getStats()
    {
        return response()->json([
            'total' => User::count(),
            'active' => User::where('status', true)->count(),
            'inactive' => User::where('status', false)->count(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'     => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'user_type'     => 'required|in:USER,ADMIN',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'user_type'  => $validated['user_type'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
        ]);

        return response()->json(['success' => true, 'message' => 'User created successfully.']);
    }

    public function edit($id) 
    {
        $decrypted = Crypt::decryptString($id);

        $user = User::findOrFail($decrypted);

        return response()->json([
            'id'        => Crypt::encryptString($user->id),
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'user_type' => $user->user_type,
            'email' => $user->email,
        ]);
    }

    public function update(Request $request, $id)
    {
        $decrypted = Crypt::decryptString($id);

        $validated = $request->validate([
            'first_name'     => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'user_type'     => 'required|in:USER,ADMIN',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($decrypted),
            ],
            'password' => 'nullable|string|min:6',
        ]);

        $user = User::findOrFail($decrypted);
        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->user_type = $validated['user_type'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json(['success' => true, 'message' => 'User updated successfully.']);
    }

    public function destroy($id)
    {
        $decrypted = Crypt::decryptString($id);
        User::findOrFail($decrypted)->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.'
        ]);
    }

    public function toggle($id)
    {
        try {
            $decrypted = Crypt::decryptString($id);
            
            $user = User::findOrFail($decrypted);
            $user->status = !$user->status;
            $user->update();

            return response()->json([
                'success' => true,
                'message' => 'User status toggled successfully.'
            ]);
        } catch (DecryptException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid user ID.'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }
}
