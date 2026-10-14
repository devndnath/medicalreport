@extends('layouts.app')
@section('page-title', 'Import Legacy Workbook')
@section('content')
<x-card title="Upload the Excel Workbook" class="mb-6">
    <p class="text-sm text-slate-500 mb-4">Upload the original "Medicine Report" workbook. It is stored as-is and never modified. You'll get a preview with validation and duplicate detection before anything is committed to the database.</p>
    <form method="POST" action="{{ route('import.store') }}" enctype="multipart/form-data" class="flex gap-3">
        @csrf
        <input type="file" name="workbook" accept=".xlsx,.xls" required class="text-sm">
        <button class="bg-accent-500 text-white text-sm px-4 py-2 rounded-lg">Upload & Preview</button>
    </form>
</x-card>

<x-card title="Recent Import Batches">
<table class="w-full text-sm">
<thead><tr class="text-left text-slate-500 border-b"><th class="py-2 pr-3">File</th><th class="py-2 pr-3">Rows</th><th class="py-2 pr-3">Valid</th><th class="py-2 pr-3">Errors</th><th class="py-2 pr-3">Status</th><th></th></tr></thead>
<tbody>
@foreach($batches as $b)
<tr class="border-b border-slate-50">
    <td class="py-2 pr-3">{{ $b->original_filename }}</td>
    <td class="py-2 pr-3">{{ $b->total_rows }}</td>
    <td class="py-2 pr-3 text-emerald-600">{{ $b->valid_rows }}</td>
    <td class="py-2 pr-3 text-rose-600">{{ $b->error_rows }}</td>
    <td class="py-2 pr-3 capitalize">{{ $b->status }}</td>
    <td class="py-2 pr-3 text-right"><a href="{{ route('import.show',$b) }}" class="text-navy-700 hover:underline text-xs">Review</a></td>
</tr>
@endforeach
@if($batches->isEmpty())<tr><td colspan="6" class="py-6 text-center text-slate-400">No imports yet.</td></tr>@endif
</tbody>
</table>
</x-card>
@endsection
