
<x-modals.creation-and-update-modal
    id="add-or-update-modal"
    title="New Data Entry"
    action=""
    submitButtonName="Submit"
>

{{-- Student Number --}}
<div class="col-sm-12 form-control-validation">
    <x-input.input-field
        id="student_number"
        name="student_number"
        label="Student Number"
        type="text"
        icon="fa-solid fa-id-card fa-1x"
        placeholder="Student Number (e.g. 23-12345)"
        help=""
    />
</div>

{{-- Name --}}
<div class="col-sm-12 form-control-validation">
    <x-input.input-field
        id="name"
        name="name"
        label="Name"
        type="text"
        icon="fa-solid fa-user fa-1x"
        placeholder="Full Name (e.g. Juan Dela Cruz)"
        help=""
    />
</div>

{{-- Program Code --}}
<div class="col-sm-12 form-control-validation">
    <x-input.input-field
        id="program_code"
        name="program_code"
        label="Program Code"
        type="text"
        icon="fa-solid fa-graduation-cap fa-1x"
        placeholder="Program Code (e.g. BSCS, BSIT)"
        help=""
    />
</div>

</x-modals.creation-and-update-modal>
