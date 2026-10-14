<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Issue Voucher {{ $voucher->voucher_number }}</title>
<style>
body{font-family: DejaVu Sans, Arial, sans-serif; font-size:12px; color:#0f1b2d;}
table{width:100%; border-collapse:collapse; margin-top:12px;}
th,td{border-bottom:1px solid #e2e8f0; text-align:left; padding:6px;}
h1{font-size:16px;} .muted{color:#64748b;}
.sign{margin-top:60px; display:flex; justify-content:space-between;}
.sign div{border-top:1px solid #0f1b2d; width:200px; text-align:center; padding-top:4px; font-size:11px;}
</style>
</head>
<body>
<h1>Medicine Issue Voucher — {{ $voucher->voucher_number }}</h1>
<p class="muted">{{ $voucher->issued_at->format('d-m-Y H:i') }}</p>
<p><strong>Employee:</strong> {{ $voucher->employee->name }} ({{ $voucher->employee->employee_code }}) &nbsp; <strong>Department:</strong> {{ $voucher->department->name ?? '—' }}</p>
<p><strong>Symptoms:</strong> {{ $voucher->symptoms ?? '—' }}</p>
<table>
<thead><tr><th>Medicine</th><th>Qty</th></tr></thead>
<tbody>
@foreach($voucher->items as $item)
<tr><td>{{ $item->medicine->displayName() }}</td><td>{{ number_format($item->quantity_issued,2) }} {{ $item->issue_unit }}</td></tr>
@endforeach
</tbody>
</table>
<div class="sign">
    <div>Issued By</div>
    <div>Receiver Signature / Acknowledgment</div>
</div>
</body>
</html>
