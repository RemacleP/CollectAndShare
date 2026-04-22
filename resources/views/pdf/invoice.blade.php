<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; }
        .header { text-align: right; margin-bottom: 50px; }
        .details { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f4f4f4; padding: 10px; border: 1px solid #ddd; }
        td { padding: 10px; border: 1px solid #ddd; }
        .total { text-align: right; font-size: 1.2em; font-bold: bold; margin-top: 20px; }
    </style>
</head>
<body>
<div class="header">
    <h1>FACTURE</h1>
    <p>Date : {{ $date }}</p>
</div>

<div class="details">
    <strong>Destinataire :</strong><br>
    {{ $user['firstname'] }} {{ $user['lastname'] }}<br>
    {{ $user['email'] }}
</div>

<table>
    <thead>
    <tr>
        <th>Description</th>
        <th>Prix Unitaire</th>
        <th>Quantité</th>
        <th>Total HT</th>
    </tr>
    </thead>
    <tbody>
    @foreach($items as $item)
        <tr>
            <td>{{ $item['label'] }}</td>
            <td>{{ number_format($item['price'], 2) }} €</td>
            <td>{{ $item['quantity'] }}</td>
            <td>{{ number_format($item['total'], 2) }} €</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="total">
    <strong>TOTAL À PAYER : {{ number_format($total, 2) }} €</strong>
</div>
</body>
</html>
