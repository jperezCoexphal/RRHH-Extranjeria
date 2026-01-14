<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modelo {{ $tipo_solicitud ?? 'EX' }} - {{ $expediente_codigo ?? '' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.4;
            color: #000;
            padding: 10mm;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }
        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .header h2 {
            font-size: 10pt;
            font-weight: normal;
        }
        .header .subtitle {
            font-size: 8pt;
            color: #333;
            margin-top: 5px;
        }
        .section {
            margin-bottom: 12px;
            border: 1px solid #000;
            padding: 8px;
        }
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            background-color: #e0e0e0;
            padding: 5px 8px;
            margin: -8px -8px 8px -8px;
            border-bottom: 1px solid #000;
        }
        .form-row {
            display: flex;
            margin-bottom: 6px;
            flex-wrap: wrap;
        }
        .form-group {
            flex: 1;
            min-width: 45%;
            margin-right: 10px;
            margin-bottom: 4px;
        }
        .form-group.full {
            min-width: 100%;
            margin-right: 0;
        }
        .form-group.third {
            min-width: 30%;
        }
        .form-label {
            font-size: 8pt;
            color: #333;
            display: block;
            margin-bottom: 2px;
        }
        .form-value {
            font-size: 9pt;
            font-weight: bold;
            border-bottom: 1px dotted #666;
            min-height: 14px;
            padding: 2px 0;
        }
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 5px;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            font-size: 8pt;
        }
        .checkbox {
            width: 12px;
            height: 12px;
            border: 1px solid #000;
            margin-right: 4px;
            display: inline-block;
            text-align: center;
            line-height: 10px;
            font-weight: bold;
        }
        .checkbox.checked {
            background-color: #000;
            color: #fff;
        }
        .two-columns {
            display: flex;
            gap: 15px;
        }
        .column {
            flex: 1;
        }
        .signatures {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 5px;
            font-size: 8pt;
        }
        .footer {
            position: fixed;
            bottom: 5mm;
            left: 10mm;
            right: 10mm;
            text-align: center;
            font-size: 7pt;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
        .page-break {
            page-break-after: always;
        }
        .small-text {
            font-size: 7pt;
            color: #666;
        }
        .notice {
            background-color: #f5f5f5;
            border: 1px solid #ccc;
            padding: 8px;
            margin: 10px 0;
            font-size: 8pt;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
        }
        table.data-table th,
        table.data-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: left;
            font-size: 8pt;
        }
        table.data-table th {
            background-color: #e0e0e0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    {{-- PAGINA 1: DATOS DEL SOLICITANTE --}}
    <div class="header">
        <h1>SOLICITUD DE AUTORIZACION DE RESIDENCIA TEMPORAL Y TRABAJO</h1>
        <h2>{{ $tipo_solicitud ?? 'EX-03' }} - POR CUENTA AJENA</h2>
        <div class="subtitle">Expediente: {{ $expediente_codigo ?? '' }} | Campaña: {{ $campaña ?? '' }}</div>
    </div>

    {{-- DATOS DEL EXTRANJERO/SOLICITANTE --}}
    <div class="section">
        <div class="section-title">1. DATOS DEL EXTRANJERO/SOLICITANTE</div>

        <div class="form-row">
            <div class="form-group">
                <span class="form-label">Primer Apellido</span>
                <div class="form-value">{{ $apellidos ?? '' }}</div>
            </div>
            <div class="form-group">
                <span class="form-label">Nombre</span>
                <div class="form-value">{{ $nombre ?? '' }}</div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group third">
                <span class="form-label">Numero de Pasaporte</span>
                <div class="form-value">{{ $pasaporte ?? '' }}</div>
            </div>
            <div class="form-group third">
                <span class="form-label">NIE (si dispone)</span>
                <div class="form-value">{{ $nie ?? '' }}</div>
            </div>
            <div class="form-group third">
                <span class="form-label">NISS</span>
                <div class="form-value">{{ $niss ?? '' }}</div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group third">
                <span class="form-label">Fecha de Nacimiento</span>
                <div class="form-value">{{ $fecha_nacimiento ?? '' }}</div>
            </div>
            <div class="form-group third">
                <span class="form-label">Nacionalidad</span>
                <div class="form-value">{{ $nacionalidad ?? '' }}</div>
            </div>
            <div class="form-group third">
                <span class="form-label">Pais de Nacimiento</span>
                <div class="form-value">{{ $pais_nacimiento ?? '' }}</div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group third">
                <span class="form-label">Sexo</span>
                <div class="form-value">{{ $sexo ?? '' }}</div>
            </div>
            <div class="form-group third">
                <span class="form-label">Estado Civil</span>
                <div class="form-value">{{ $estado_civil ?? '' }}</div>
            </div>
            <div class="form-group third">
                <span class="form-label">Lugar de Nacimiento</span>
                <div class="form-value">{{ $lugar_nacimiento ?? '' }}</div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <span class="form-label">Nombre del Padre</span>
                <div class="form-value">{{ $nombre_padre ?? '' }}</div>
            </div>
            <div class="form-group">
                <span class="form-label">Nombre de la Madre</span>
                <div class="form-value">{{ $nombre_madre ?? '' }}</div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group full">
                <span class="form-label">Domicilio en España (si tiene)</span>
                <div class="form-value">{{ $direccion ?? '' }}</div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <span class="form-label">Telefono</span>
                <div class="form-value">{{ $telefono ?? '' }}</div>
            </div>
            <div class="form-group">
                <span class="form-label">Correo Electronico</span>
                <div class="form-value">{{ $email ?? '' }}</div>
            </div>
        </div>
    </div>

    {{-- DATOS DEL EMPLEADOR --}}
    <div class="section">
        <div class="section-title">2. DATOS DEL EMPLEADOR</div>

        <div class="form-row">
            <div class="form-group full">
                <span class="form-label">Razon Social / Nombre</span>
                <div class="form-value">{{ $razon_social ?? '' }}</div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group third">
                <span class="form-label">NIF/CIF</span>
                <div class="form-value">{{ $nif ?? '' }}</div>
            </div>
            <div class="form-group third">
                <span class="form-label">Codigo Cuenta Cotizacion</span>
                <div class="form-value">{{ $ccc ?? '' }}</div>
            </div>
            <div class="form-group third">
                <span class="form-label">CNAE</span>
                <div class="form-value">{{ $cnae ?? '' }}</div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group full">
                <span class="form-label">Domicilio Social</span>
                <div class="form-value">{{ $direccion ?? '' }}</div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <span class="form-label">Telefono</span>
                <div class="form-value">{{ $telefono ?? '' }}</div>
            </div>
            <div class="form-group">
                <span class="form-label">Correo Electronico</span>
                <div class="form-value">{{ $email ?? '' }}</div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <span class="form-label">Representante Legal</span>
                <div class="form-value">{{ $representante_nombre ?? '' }}</div>
            </div>
            <div class="form-group">
                <span class="form-label">Cargo / Calidad</span>
                <div class="form-value">{{ $representante_cargo ?? '' }}</div>
            </div>
        </div>
    </div>

    <div class="page-break"></div>

    {{-- PAGINA 2: DATOS DE LA ACTIVIDAD LABORAL --}}
    <div class="header">
        <h1>SOLICITUD DE AUTORIZACION DE RESIDENCIA TEMPORAL Y TRABAJO</h1>
        <h2>{{ $tipo_solicitud ?? 'EX-03' }} - DATOS DE LA ACTIVIDAD LABORAL</h2>
    </div>

    <div class="section">
        <div class="section-title">3. DATOS DE LA ACTIVIDAD LABORAL</div>

        <div class="form-row">
            <div class="form-group full">
                <span class="form-label">Puesto de Trabajo / Ocupacion</span>
                <div class="form-value">{{ $puesto_trabajo ?? '' }}</div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <span class="form-label">Fecha Prevista de Inicio</span>
                <div class="form-value">{{ $fecha_inicio ?? '' }}</div>
            </div>
            <div class="form-group">
                <span class="form-label">Fecha Prevista de Fin</span>
                <div class="form-value">{{ $fecha_fin ?? 'INDEFINIDO' }}</div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group third">
                <span class="form-label">Tipo de Jornada</span>
                <div class="form-value">{{ $tipo_jornada ?? '' }}</div>
            </div>
            <div class="form-group third">
                <span class="form-label">Horas Semanales</span>
                <div class="form-value">{{ $horas_semanales ?? '' }}</div>
            </div>
            <div class="form-group third">
                <span class="form-label">Salario Bruto</span>
                <div class="form-value">{{ $salario ?? '' }}</div>
            </div>
        </div>

        @if($direccion_trabajo ?? false)
        <div class="form-row">
            <div class="form-group full">
                <span class="form-label">Centro de Trabajo</span>
                <div class="form-value">{{ $direccion_trabajo }}</div>
            </div>
        </div>
        @endif
    </div>

    {{-- TIPO DE AUTORIZACION SOLICITADA --}}
    <div class="section">
        <div class="section-title">4. TIPO DE AUTORIZACION SOLICITADA</div>

        <div class="checkbox-group">
            <div class="checkbox-item">
                <span class="checkbox {{ ($tipo_solicitud ?? '') == 'EX-03' ? 'checked' : '' }}">{{ ($tipo_solicitud ?? '') == 'EX-03' ? 'X' : '' }}</span>
                Residencia temporal y trabajo por cuenta ajena
            </div>
            <div class="checkbox-item">
                <span class="checkbox {{ ($tipo_solicitud ?? '') == 'EX-10' ? 'checked' : '' }}">{{ ($tipo_solicitud ?? '') == 'EX-10' ? 'X' : '' }}</span>
                Residencia por circunstancias excepcionales (arraigo)
            </div>
            <div class="checkbox-item">
                <span class="checkbox {{ ($tipo_solicitud ?? '') == 'EX-04' ? 'checked' : '' }}">{{ ($tipo_solicitud ?? '') == 'EX-04' ? 'X' : '' }}</span>
                Trabajo de temporada o campaña
            </div>
        </div>
    </div>

    {{-- DOCUMENTACION APORTADA --}}
    <div class="section">
        <div class="section-title">5. DOCUMENTACION QUE SE ACOMPANA</div>

        <div class="checkbox-group">
            <div class="checkbox-item">
                <span class="checkbox checked">X</span>
                Copia del pasaporte completo
            </div>
            <div class="checkbox-item">
                <span class="checkbox checked">X</span>
                Contrato de trabajo firmado
            </div>
            <div class="checkbox-item">
                <span class="checkbox checked">X</span>
                Memoria justificativa
            </div>
            <div class="checkbox-item">
                <span class="checkbox checked">X</span>
                Alta en Seguridad Social (CCC)
            </div>
            <div class="checkbox-item">
                <span class="checkbox checked">X</span>
                Certificado de antecedentes penales
            </div>
            <div class="checkbox-item">
                <span class="checkbox checked">X</span>
                Certificado medico
            </div>
        </div>
    </div>

    {{-- DECLARACION Y FIRMA --}}
    <div class="section">
        <div class="section-title">6. DECLARACION RESPONSABLE Y FIRMA</div>

        <p class="small-text" style="margin-bottom: 10px;">
            El/la solicitante y el empleador declaran bajo su responsabilidad que los datos consignados en esta solicitud
            son ciertos y que se comprometen a cumplir las obligaciones derivadas de la autorizacion solicitada.
            Asimismo, autorizan a la Administracion a verificar la exactitud de los datos declarados.
        </p>

        <div class="notice">
            <strong>PROTECCION DE DATOS:</strong> Los datos personales recogidos seran tratados conforme al Reglamento (UE) 2016/679
            y la Ley Organica 3/2018, de Proteccion de Datos Personales.
        </div>

        <div class="signatures">
            <div class="signature-box">
                <p><strong>EL EMPLEADOR</strong></p>
                <div class="signature-line">
                    <p>{{ $representante_nombre ?? '' }}</p>
                    <p>NIF: {{ $nif ?? '' }}</p>
                </div>
            </div>
            <div class="signature-box">
                <p><strong>EL SOLICITANTE</strong></p>
                <div class="signature-line">
                    <p>{{ $nombre_completo ?? '' }}</p>
                    <p>Pasaporte: {{ $pasaporte ?? '' }}</p>
                </div>
            </div>
        </div>

        <p style="text-align: center; margin-top: 20px; font-size: 8pt;">
            En ________________________, a {{ $fecha_generacion ?? '' }}
        </p>
    </div>

    <div class="footer">
        <p>Modelo {{ $tipo_solicitud ?? 'EX' }} - Expediente: {{ $expediente_codigo ?? '' }} - Generado el {{ $fecha_generacion ?? '' }} a las {{ $hora_generacion ?? '' }}</p>
        <p>Documento generado automaticamente - Sistema de Gestion de Extranjeria</p>
    </div>
</body>
</html>
