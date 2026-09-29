<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Presupuesto {{ $presupuesto->numero }}</title>
    <style>
        @page {
            margin: 28px 32px 35px 32px;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #2d3748;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* Utilidades */
        .w-100 { width: 100%; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .text-muted { color: #718096; }

        /* Cabecera */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #2b6cb0;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        .company-title {
            font-size: 20px;
            font-weight: bold;
            color: #1a365d;
            margin: 0 0 4px 0;
        }

        .doc-title {
            font-size: 22px;
            font-weight: bold;
            color: #2b6cb0;
            margin: 0 0 4px 0;
            letter-spacing: 1px;
        }

        .badge-estado {
            display: inline-block;
            padding: 3px 8px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .badge-borrador { background-color: #e2e8f0; color: #4a5568; }
        .badge-pendiente { background-color: #feebc8; color: #c05621; }
        .badge-aceptado { background-color: #c6f6d5; color: #22543d; }
        .badge-rechazado { background-color: #fed7d7; color: #9b2c2c; }

        /* Bloque Emisor y Receptor */
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-card {
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 10px 12px;
            vertical-align: top;
        }

        .card-header {
            font-size: 10px;
            font-weight: bold;
            color: #4a5568;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        /* Tabla de líneas */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        .items-table th {
            background-color: #2b6cb0;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 10px;
            border: 1px solid #2b6cb0;
        }

        .items-table td {
            padding: 7px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10.5px;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .product-ref {
            font-size: 9.5px;
            color: #4a5568;
            font-weight: bold;
        }

        /* Cuadro de Totales */
        .totals-table {
            width: 42%;
            margin-left: auto;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .totals-table td {
            padding: 5px 8px;
            font-size: 11px;
        }

        .totals-table tr.total-row {
            background-color: #ebf8ff;
            border-top: 2px solid #2b6cb0;
            border-bottom: 2px solid #2b6cb0;
        }

        .totals-table tr.total-row td {
            font-size: 13px;
            font-weight: bold;
            color: #2b6cb0;
            padding: 7px 8px;
        }

        /* Notas y condiciones */
        .notes-card {
            background-color: #ffffff;
            border: 1px solid #cbd5e0;
            border-left: 4px solid #2b6cb0;
            border-radius: 4px;
            padding: 10px 12px;
            margin-top: 15px;
        }

        .notes-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            color: #2b6cb0;
            margin-bottom: 4px;
        }

        .notes-content {
            font-size: 10px;
            color: #4a5568;
            white-space: pre-line;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #a0aec0;
            border-top: 1px solid #edf2f7;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    <!-- CABECERA -->
    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                @if ($empresa)
                    <div class="company-title">{{ $empresa->nombre_comercial }}</div>
                    @if ($empresa->razon_social && $empresa->razon_social !== $empresa->nombre_comercial)
                        <div class="text-muted" style="font-size: 10px;">{{ $empresa->razon_social }}</div>
                    @endif
                    <div style="margin-top: 4px; font-size: 10px;">
                        @if ($empresa->nif)<strong>NIF/CIF:</strong> {{ $empresa->nif }}<br>@endif
                        @if ($empresa->direccion){{ $empresa->direccion }}<br>@endif
                        @if ($empresa->codigo_postal || $empresa->localidad)
                            {{ $empresa->codigo_postal }} {{ $empresa->localidad }}@if($empresa->provincia), {{ $empresa->provincia }}@endif<br>
                        @endif
                        @if ($empresa->telefono)<strong>Tel:</strong> {{ $empresa->telefono }}@endif
                        @if ($empresa->email) &bull; <strong>Email:</strong> {{ $empresa->email }}@endif
                    </div>
                @else
                    <div class="company-title">GestionPro</div>
                @endif
            </td>
            <td style="width: 45%; vertical-align: top; text-align: right;">
                <div class="doc-title">PRESUPUESTO</div>
                <div style="font-size: 13px; font-weight: bold; color: #1a202c; margin-bottom: 6px;">
                    N.º: {{ $presupuesto->numero }}
                </div>
                <div style="font-size: 10px; margin-bottom: 4px;">
                    <strong>Fecha:</strong> {{ $presupuesto->fecha ? $presupuesto->fecha->format('d/m/Y') : '-' }}
                </div>
                @if ($presupuesto->valido_hasta)
                    <div style="font-size: 10px; margin-bottom: 6px;">
                        <strong>Válido hasta:</strong> {{ $presupuesto->valido_hasta->format('d/m/Y') }}
                    </div>
                @endif
                <div>
                    @php
                        $estadoClass = match($presupuesto->estado) {
                            'Borrador' => 'badge-borrador',
                            'Pendiente' => 'badge-pendiente',
                            'Aceptado' => 'badge-aceptado',
                            'Rechazado' => 'badge-rechazado',
                            default => 'badge-borrador',
                        };
                    @endphp
                    <span class="badge-estado {{ $estadoClass }}">
                        Estado: {{ $presupuesto->estado }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <!-- DATOS DEL CLIENTE Y DETALLES -->
    <table class="info-table">
        <tr>
            <td style="width: 58%; vertical-align: top; padding-right: 12px;">
                <div class="info-card">
                    <div class="card-header">Destinatario / Cliente</div>
                    @if ($cliente)
                        <div style="font-size: 12px; font-weight: bold; color: #1a202c; margin-bottom: 3px;">
                            {{ $cliente->nombre }}
                        </div>
                        <div style="font-size: 10px; color: #4a5568;">
                            @if ($cliente->nif)<div><strong>NIF/CIF:</strong> {{ $cliente->nif }}</div>@endif
                            @if ($cliente->direccion)<div>{{ $cliente->direccion }}</div>@endif
                            @if ($cliente->codigo_postal || $cliente->localidad)
                                <div>{{ $cliente->codigo_postal }} {{ $cliente->localidad }}@if($cliente->provincia), {{ $cliente->provincia }}@endif</div>
                            @endif
                            @if ($cliente->telefono)<div><strong>Teléfono:</strong> {{ $cliente->telefono }}</div>@endif
                            @if ($cliente->email)<div><strong>Email:</strong> {{ $cliente->email }}</div>@endif
                        </div>
                    @else
                        <div class="text-muted" style="font-style: italic;">Cliente sin asignar</div>
                    @endif
                </div>
            </td>
            <td style="width: 42%; vertical-align: top;">
                <div class="info-card">
                    <div class="card-header">Resumen de Operación</div>
                    <table style="width: 100%; font-size: 10px; border-collapse: collapse;">
                        <tr>
                            <td class="text-muted" style="padding: 2px 0;">Referencia Doc.:</td>
                            <td class="text-right font-bold" style="padding: 2px 0;">{{ $presupuesto->numero }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 2px 0;">Emisión:</td>
                            <td class="text-right" style="padding: 2px 0;">{{ $presupuesto->fecha ? $presupuesto->fecha->format('d/m/Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted" style="padding: 2px 0;">Vencimiento:</td>
                            <td class="text-right" style="padding: 2px 0;">{{ $presupuesto->valido_hasta ? $presupuesto->valido_hasta->format('d/m/Y') : 'A consultar' }}</td>
                        </tr>
                        @if ($presupuesto->fecha_aceptacion)
                            <tr>
                                <td class="text-muted" style="padding: 2px 0; color: #22543d;">Aceptado el:</td>
                                <td class="text-right font-bold" style="padding: 2px 0; color: #22543d;">{{ $presupuesto->fecha_aceptacion->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- TABLA DE LÍNEAS -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-left" style="width: 50%;">Descripción / Concepto</th>
                <th class="text-center" style="width: 10%;">Cant.</th>
                <th class="text-right" style="width: 14%;">Precio Unit.</th>
                <th class="text-center" style="width: 11%;">IVA %</th>
                <th class="text-right" style="width: 15%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lineas as $linea)
                <tr>
                    <td>
                        @if ($linea->producto)
                            <div class="product-ref">REF: {{ $linea->producto->referencia }}</div>
                        @endif
                        <div>{{ $linea->descripcion }}</div>
                    </td>
                    <td class="text-center">
                        {{ number_format($linea->cantidad, 2, ',', '.') }}
                    </td>
                    <td class="text-right">
                        {{ number_format($linea->precio_unitario, 2, ',', '.') }} €
                    </td>
                    <td class="text-center">
                        {{ number_format($linea->porcentaje_iva, 2, ',', '.') }}%
                    </td>
                    <td class="text-right font-bold">
                        {{ number_format($linea->subtotal, 2, ',', '.') }} €
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted" style="padding: 16px;">
                        No hay conceptos especificados en este presupuesto.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TOTALES -->
    <table class="totals-table">
        <tr>
            <td class="text-muted">Base Imponible:</td>
            <td class="text-right font-bold">{{ number_format($presupuesto->base_imponible, 2, ',', '.') }} €</td>
        </tr>
        <tr>
            <td class="text-muted">Impuestos (IVA):</td>
            <td class="text-right font-bold">{{ number_format($presupuesto->iva, 2, ',', '.') }} €</td>
        </tr>
        <tr class="total-row">
            <td>TOTAL:</td>
            <td class="text-right">{{ number_format($presupuesto->total, 2, ',', '.') }} €</td>
        </tr>
    </table>

    <!-- NOTAS / CONDICIONES -->
    @if ($presupuesto->notas)
        <div class="notes-card">
            <div class="notes-title">Observaciones / Condiciones</div>
            <div class="notes-content">{{ $presupuesto->notas }}</div>
        </div>
    @endif

    <!-- FOOTER -->
    <div class="footer">
        Presupuesto generado por GestionPro &bull; Documento informativo emitido según las condiciones comerciales acordadas.
    </div>

</body>
</html>
