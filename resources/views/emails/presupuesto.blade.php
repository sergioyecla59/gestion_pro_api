<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presupuesto {{ $presupuesto->numero }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f7fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #2d3748; line-height: 1.6;">

    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f7fafc; padding: 30px 15px;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); border: 1px solid #e2e8f0;">
                    
                    <!-- CABECERA -->
                    <tr>
                        <td style="background-color: #2b6cb0; padding: 25px 30px; text-align: left;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 20px; font-weight: 700; letter-spacing: 0.5px;">
                                {{ $empresa?->nombre_comercial ?? 'GestionPro' }}
                            </h1>
                            <p style="margin: 4px 0 0 0; color: #bee3f8; font-size: 13px;">
                                Presupuesto Comercial N.º {{ $presupuesto->numero }}
                            </p>
                        </td>
                    </tr>

                    <!-- CONTENIDO -->
                    <tr>
                        <td style="padding: 30px;">
                            <p style="margin: 0 0 16px 0; font-size: 15px; color: #1a202c;">
                                Estimado/a <strong>{{ $cliente?->nombre ?? 'Cliente' }}</strong>,
                            </p>

                            @if ($mensajePersonalizado)
                                <div style="margin: 0 0 20px 0; padding: 12px 16px; background-color: #edf2f7; border-left: 4px solid #4299e1; border-radius: 4px; font-size: 14px; color: #2d3748; white-space: pre-line;">
                                    {{ $mensajePersonalizado }}
                                </div>
                            @else
                                <p style="margin: 0 0 20px 0; font-size: 14px; color: #4a5568;">
                                    Le remitimos el presupuesto solicitado con los detalles y condiciones acordadas. A continuación, puede consultar el resumen de la propuesta:
                                </p>
                            @endif

                            <!-- TARJETA RESUMEN -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 25px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #edf2f7; font-size: 13px; color: #718096; width: 40%;">Número de Presupuesto:</td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #edf2f7; font-size: 14px; font-weight: 600; color: #1a202c; text-align: right;">{{ $presupuesto->numero }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #edf2f7; font-size: 13px; color: #718096;">Fecha de Emisión:</td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #edf2f7; font-size: 13px; color: #2d3748; text-align: right;">{{ $presupuesto->fecha ? $presupuesto->fecha->format('d/m/Y') : '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #edf2f7; font-size: 13px; color: #718096;">Válido Hasta:</td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #edf2f7; font-size: 13px; color: #2d3748; text-align: right;">{{ $presupuesto->valido_hasta ? $presupuesto->valido_hasta->format('d/m/Y') : 'Sin fecha límite especificada' }}</td>
                                </tr>
                                <tr style="background-color: #ebf8ff;">
                                    <td style="padding: 14px 16px; font-size: 14px; font-weight: 700; color: #2b6cb0;">IMPORTE TOTAL:</td>
                                    <td style="padding: 14px 16px; font-size: 17px; font-weight: 700; color: #2b6cb0; text-align: right;">{{ number_format($presupuesto->total, 2, ',', '.') }} €</td>
                                </tr>
                            </table>

                            <!-- BOTÓN DE ACCIÓN / ENLACE PÚBLICO -->
                            <div style="text-align: center; margin: 30px 0 25px 0;">
                                <a href="{{ $urlPublica }}" target="_blank" style="background-color: #2b6cb0; color: #ffffff; text-decoration: none; padding: 13px 28px; font-size: 14px; font-weight: 600; border-radius: 6px; display: inline-block; box-shadow: 0 2px 4px rgba(43, 108, 176, 0.25);">
                                    Ver y Responder Presupuesto en Línea &rarr;
                                </a>
                            </div>

                            <p style="margin: 0 0 16px 0; font-size: 12px; color: #718096; text-align: center;">
                                También puede consultar y gestionar su presupuesto copiando este enlace en su navegador:<br>
                                <a href="{{ $urlPublica }}" style="color: #3182ce; word-break: break-all;">{{ $urlPublica }}</a>
                            </p>

                            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 25px 0;">

                            <p style="margin: 0; font-size: 12px; color: #a0aec0; text-align: center;">
                                @if ($presupuesto->empresa?->email || $presupuesto->empresa?->telefono)
                                    ¿Dudas sobre esta propuesta? Contáctenos en
                                    @if ($presupuesto->empresa->email)<a href="mailto:{{ $presupuesto->empresa->email }}" style="color: #4a5568;">{{ $presupuesto->empresa->email }}</a>@endif
                                    @if ($presupuesto->empresa->telefono) o al {{ $presupuesto->empresa->telefono }}@endif.
                                @else
                                    Documento emitido por {{ $empresa?->nombre_comercial ?? 'GestionPro' }}.
                                @endif
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
