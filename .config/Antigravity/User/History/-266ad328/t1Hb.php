@extends('layout.sidebar.bar')

@section('menu_items')

<x-sidebar.item route='admin.dashboard' name='Dashboard' icon='menu-icon tf-icons bx bxs-dashboard' />

<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Core</span>
</li>

<x-sidebar.item route='academic_periods.index' name='Academic Period' icon='fa-solid fa-school-flag me-2' />

<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Academic Information</span>
</li>

<x-sidebar.item route='departments.index' name='Departments' icon='fa-solid fa-building-user me-2' />

<x-sidebar.item route='programs.index' name='Programs' icon='fa-solid fa-table-list me-2' />

<x-sidebar.item route='curricula.index' name='Curricula' icon='fa-solid fa-file-pen me-2' class="{{ request()->routeIs('subjects.*') ? 'active' : '' }}" />

<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Data Imports</span>
</li>

<x-sidebar.item route='grades.import.index' name='Grades Import' class="{{ request()->routeIs('grades.import.*') ? 'active' : '' }}" icon='fa-solid fa-file-import me-2' />

<li class="menu-header small text-uppercase">
    <span class="menu-header-text">User Management</span>
</li>

<x-sidebar.item route='students.index' name='Student accounts' icon='fa-solid fa-user-graduate me-2' />

<x-sidebar.item route='officers.index' name='E-R Officer accounts' icon='fa-solid fa-user-gear me-2' />

<x-sidebar.item route='admins.index' param='admin' name='Admin accounts' icon='fa-solid fa-user-shield me-2' />

<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Communications</span>
</li>

<x-sidebar.item route='announcements.index' name='Email Announcements' icon='fa-solid fa-bullhorn me-2' />

<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Reports</span>
</li>

<x-sidebar.item route='admin.reports.students' name='Student Directory' icon='fa-solid fa-address-book me-2' />
<x-sidebar.item route='admin.reports.grades' name='Grade Performance' icon='fa-solid fa-chart-line me-2' />
<x-sidebar.item route='admin.reports.imports' name='Grade Import History' icon='fa-solid fa-clock-rotate-left me-2' />

@endsection