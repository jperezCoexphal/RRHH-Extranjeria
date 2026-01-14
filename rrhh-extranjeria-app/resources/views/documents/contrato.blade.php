<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrato de Trabajo - {{ $expediente_codigo ?? '' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #333;
            padding: 20mm;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .header h2 {
            font-size: 12pt;
            font-weight: normal;
            color: #666;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 8px 12px;
            margin-bottom: 15px;
            border-left: 4px solid #333;
        }
        .data-row {
            display: flex;
            margin-bottom: 8px;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 5px;
        }
        .data-label {
            width: 40%;
            font-weight: bold;
            color: #555;
        }
        .data-value {
            width: 60%;
        }
        .two-columns {
            display: flex;
            gap: 20px;
        }
        .column {
            width: 50%;
        }
        .clause {
            margin-bottom: 15px;
            text-align: justify;
        }
        .clause-number {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .signatures {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 10px;
        }
        .footer {
            position: fixed;
            bottom: 10mm;
            left: 20mm;
            right: 20mm;
            text-align: center;
            font-size: 9pt;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Contrato de Trabajo</h1>
        <h2>Para trabajador extranjero en virtud de autorización de residencia y trabajo</h2>
    </div>

    <!-- Datos del Empleador -->
    <div class="section">
        <div class="section-title">DATOS DEL EMPLEADOR</div>
        <div class="data-row">
            <span class="data-label">Razón Social:</span>
            <span class="data-value">{{ $razon_social ?? '' }}</span>
        </div>
        <div class="two-columns">
            <div class="column">
                <div class="data-row">
                    <span class="data-label">NIF/CIF:</span>
                    <span class="data-value">{{ $nif ?? '' }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">CCC:</span>
                    <span class="data-value">{{ $ccc ?? '' }}</span>
                </div>
            </div>
            <div class="column">
                <div class="data-row">
                    <span class="data-label">CNAE:</span>
                    <span class="data-value">{{ $cnae ?? '' }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Forma Jurídica:</span>
                    <span class="data-value">{{ $forma_juridica ?? '' }}</span>
                </div>
            </div>
        </div>
        <div class="data-row">
            <span class="data-label">Domicilio:</span>
            <span class="data-value">{{ $direccion ?? '' }}</span>
        </div>
        <div class="data-row">
            <span class="data-label">Representante Legal:</span>
            <span class="data-value">{{ $representante_nombre ?? '' }} ({{ $representante_cargo ?? '' }})</span>
        </div>
    </div>

    <!-- Datos del Trabajador -->
    <div class="section">
        <div class="section-title">DATOS DEL TRABAJADOR</div>
        <div class="data-row">
            <span class="data-label">Nombre Completo:</span>
            <span class="data-value">{{ $nombre_completo ?? '' }}</span>
        </div>
        <div class="two-columns">
            <div class="column">
                <div class="data-row">
                    <span class="data-label">Pasaporte:</span>
                    <span class="data-value">{{ $pasaporte ?? '' }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">NIE:</span>
                    <span class="data-value">{{ $nie ?? '' }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">NISS:</span>
                    <span class="data-value">{{ $niss ?? '' }}</span>
                </div>
            </div>
            <div class="column">
                <div class="data-row">
                    <span class="data-label">Fecha Nacimiento:</span>
                    <span class="data-value">{{ $fecha_nacimiento ?? '' }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Nacionalidad:</span>
                    <span class="data-value">{{ $nacionalidad ?? '' }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Estado Civil:</span>
                    <span class="data-value">{{ $estado_civil ?? '' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Condiciones Laborales -->
    <div class="section">
        <div class="section-title">CONDICIONES LABORALES</div>
        <div class="two-columns">
            <div class="column">
                <div class="data-row">
                    <span class="data-label">Puesto de Trabajo:</span>
                    <span class="data-value">{{ $puesto_trabajo ?? '' }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Fecha de Inicio:</span>
                    <span class="data-value">{{ $fecha_inicio ?? '' }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Fecha de Fin:</span>
                    <span class="data-value">{{ $fecha_fin ?? 'Indefinido' }}</span>
                </div>
            </div>
            <div class="column">
                <div class="data-row">
                    <span class="data-label">Tipo de Jornada:</span>
                    <span class="data-value">{{ $tipo_jornada ?? '' }}</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Horas Semanales:</span>
                    <span class="data-value">{{ $horas_semanales ?? '' }} horas</span>
                </div>
                <div class="data-row">
                    <span class="data-label">Salario Bruto:</span>
                    <span class="data-value">{{ $salario ?? '' }}</span>
                </div>
            </div>
        </div>
        @if($periodo_prueba ?? false)
        <div class="data-row">
            <span class="data-label">Período de Prueba:</span>
            <span class="data-value">{{ $periodo_prueba }}</span>
        </div>
        @endif
        @if($direccion_trabajo ?? false)
        <div class="data-row">
            <span class="data-label">Centro de Trabajo:</span>
            <span class="data-value">{{ $direccion_trabajo }}</span>
        </div>
        @endif
    </div>

    <div class="page-break"></div>

    <!-- Cláusulas del Contrato -->
    <div class="section">
        <div class="section-title">CLÁUSULAS</div>

        <div class="clause">
            <div class="clause-number">PRIMERA - Objeto del Contrato</div>
            <p>El trabajador se compromete a prestar sus servicios como <strong>{{ $puesto_trabajo ?? '' }}</strong>,
            realizando las funciones propias de dicha categoría profesional, bajo la dirección del empresario
            o persona en quien éste delegue.</p>
        </div>

        <div class="clause">
            <div class="clause-number">SEGUNDA - Duración</div>
            <p>El presente contrato tendrá una duración desde el <strong>{{ $fecha_inicio ?? '' }}</strong>
            @if($fecha_fin ?? false)
            hasta el <strong>{{ $fecha_fin }}</strong>.
            @else
            con carácter indefinido.
            @endif
            </p>
        </div>

        <div class="clause">
            <div class="clause-number">TERCERA - Jornada Laboral</div>
            <p>La jornada de trabajo será de <strong>{{ $horas_semanales ?? '40' }} horas semanales</strong>,
            distribuidas de lunes a viernes, salvo necesidades del servicio que requieran trabajo en fines
            de semana o festivos, compensándose de acuerdo con la legislación vigente.</p>
        </div>

        <div class="clause">
            <div class="clause-number">CUARTA - Retribución</div>
            <p>El trabajador percibirá una retribución bruta de <strong>{{ $salario ?? '' }}</strong>,
            distribuida en catorce pagas (doce mensualidades y dos pagas extraordinarias),
            sometida a las retenciones legales correspondientes.</p>
        </div>

        <div class="clause">
            <div class="clause-number">QUINTA - Vacaciones</div>
            <p>El trabajador tendrá derecho a un período de vacaciones anuales retribuidas de 30 días
            naturales, o la parte proporcional que corresponda si el contrato es de duración inferior al año.</p>
        </div>

        <div class="clause">
            <div class="clause-number">SEXTA - Convenio Colectivo</div>
            <p>En lo no previsto en este contrato, se estará a lo dispuesto en el Convenio Colectivo
            aplicable al sector y en el Estatuto de los Trabajadores.</p>
        </div>

        <div class="clause">
            <div class="clause-number">SÉPTIMA - Protección de Datos</div>
            <p>El trabajador consiente el tratamiento de sus datos personales por parte de la empresa
            para las finalidades derivadas de la relación laboral, conforme al Reglamento General de
            Protección de Datos.</p>
        </div>
    </div>

    <!-- Firmas -->
    <div class="signatures">
        <div class="signature-box">
            <p><strong>EL EMPLEADOR</strong></p>
            <div class="signature-line">
                <p>{{ $representante_nombre ?? '' }}</p>
                <p>{{ $representante_cargo ?? '' }}</p>
            </div>
        </div>
        <div class="signature-box">
            <p><strong>EL TRABAJADOR</strong></p>
            <div class="signature-line">
                <p>{{ $nombre_completo ?? '' }}</p>
                <p>{{ $nie ?? $pasaporte ?? '' }}</p>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Expediente: {{ $expediente_codigo ?? '' }} | Generado el {{ $fecha_generacion ?? '' }} a las {{ $hora_generacion ?? '' }}</p>
        <p>Documento generado automáticamente - Sistema de Gestión de Extranjería</p>
    </div>
</body>
</html>
