<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Purchase {{ $purchase->purchase_number }}</title>
<style>
body{font-family: DejaVu Sans, Arial, sans-serif; font-size:12px; color:#0f1b2d;}
table{width:100%; border-collapse:collapse; margin-top:12px;}
th,td{border-bottom:1px solid #e2e8f0; text-align:left; padding:6px;}
h1{font-size:16px;} .muted{color:#64748b;}
.totals td{border:none; text-align:right;}
</style>
</head>
<body>
<h1>Purchase Order — {{ $purchase->purchase_number }}</h1>
<p class="muted">Factory Medicine Store · {{ $purchase->purchase_date->format('d-m-Y') }}</p>
<p><strong>Received From:</strong> {{ $purchase->sourceLabel() }} &nbsp; <strong>Invoice #:</strong> {{ $purchase->supplier_invoice_number ?? '—' }}</p>

<table>
<thead><tr><th>Medicine</th><th>Batch</th><th>Expiry</th><th>Qty</th><th>Unit Price</th><th>Line Total</th></tr></thead>
<tbody>
@foreach($purchase->items as $item)
<tr><td>{{ $item->medicine->displayName() }}</td><td>{{ $item->batch_number }}</td><td>{{ $item->expiry_date?->format('d-m-Y') ?? '—' }}</td><td>{{ number_format($item->purchase_quantity,2) }} {{ $item->purchase_unit }}</td><td>{{ number_format($item->unit_purchase_price,4) }}</td><td>{{ number_format($item->line_total,2) }}</td></tr>
@endforeach
</tbody>
<tfoot>
<tr class="totals"><td colspan="5">Grand Total</td><td><strong>{{ config('erp.currency_symbol') }}{{ number_format($purchase->grand_total,2) }}</strong></td></tr>
</tfoot>
</table>
</body>
</html>
