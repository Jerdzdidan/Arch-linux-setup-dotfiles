@extends('layout.sidebar.bar')

@section('menu_items')

<x-sidebar.item route='officer.dashboard' name='Dashboard' icon='menu-icon tf-icons bx bxs-dashboard' />

<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Academic Information</span>
</li>

<x-sidebar.item route='officer.students' name='Student Progress' class="{{ request()->routeIs('officer.student.*') ? 'active' : '' }}" icon='fa-solid fa-user-graduate me-2' />

<li class="menu-header small text-uppercase">
    <span class="menu-header-text">Communications</span>
</li>

<x-sidebar.item route='officer.announcements.index' name='Email Announcements' icon='fa-solid fa-bullhorn me-2' />

@endsection