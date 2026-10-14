@extends('layouts.app')
@section('page-title', 'New Employee')
@section('content')
<x-card>
<form method="POST" action="{{ route('employees.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
    @csrf
    @include('employees._fields')
    <div class="md:col-span-3"><button class="bg-accent-500 hover:bg-accent-600 text-white text-sm px-5 py-2.5 rounded-lg">Save Employee</button></div>
</form>
</x-card>
@endsection
