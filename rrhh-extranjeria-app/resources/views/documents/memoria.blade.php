<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memoria Justificativa - {{ $expediente_codigo ?? '' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
            padding: 20mm;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px double #333;
            padding-bottom: 20px;
        }
        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .header h2 {
            font-size: 14pt;
            font-weight: normal;
            color: #555;
        }
        .header .subtitle {
            font-size: 11pt;
            color: #666;
            margin-top: 10px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 13pt;
            font-weight: bold;
            background-color: #e8e8e8;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-left: 5px solid #444;
        }
        .subsection-title {
            font-size: 11pt;
            font-weight: bold;
            color: #444;
            margin: 15px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th,
        .data-table td {
            border: 1px solid #ccc;
            padding: 8px 12px;
            text-align: left;
        }
        .data-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            width: 40%;
        }
        .paragraph {
            text-align: justify;
            margin-bottom: 15px;
        }
        .highlight-box {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .list {
            margin-left: 20px;
            margin-bottom: 15px;
        }
        .list li {
            margin-bottom: 8px;
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
            margin-top: 80px;
            padding-top: 10px;
        }
        .declaration {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
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
        <h1>Memoria Justificativa</h1>
        <h2>Necesidad de Contratación de Trabajador Extranjero</h2>
        <div class="subtitle">
            Campaña: {{ $campaña ?? '' }} | Expediente: {{ $expediente_codigo ?? '' }}
        </div>
    </div>

    <!-- Datos del Solicitante (Empleador) -->
    <div class="section">
        <div class="section-title">1. DATOS DEL EMPLEADOR SOLICITANTE</div>

        <table class="data-table">
            <tr>
                <th>Razón Social</th>
                <td>{{ $razon_social ?? '' }}</td>
            </tr>
            <tr>
                <th>NIF/CIF</th>
                <td>{{ $nif ?? '' }}</td>
            </tr>
            <tr>
                <th>Código Cuenta Cotización</th>
                <td>{{ $ccc ?? '' }}</td>
            </tr>
            <tr>
                <th>Actividad Económica (CNAE)</th>
                <td>{{ $cnae ?? '' }}</td>
            </tr>
            <tr>
                <th>Domicilio Social</th>
                <td>{{ $direccion ?? '' }}</td>
            </tr>
            <tr>
                <th>Representante Legal</th>
                <td>{{ $representante_nombre ?? '' }} ({{ $representante_cargo ?? '' }})</td>
            </tr>
            <tr>
                <th>Documento Representante</th>
                <td>{{ $representante_documento ?? '' }}</td>
            </tr>
        </table>
    </div>

    <!-- Datos del Puesto de Trabajo -->
    <div class="section">
        <div class="section-title">2. CARACTERÍSTICAS DEL PUESTO DE TRABAJO</div>

        <table class="data-table">
            <tr>
                <th>Denominación del Puesto</th>
                <td>{{ $puesto_trabajo ?? '' }}</td>
            </tr>
            <tr>
                <th>Tipo de Solicitud</th>
                <td>{{ $tipo_solicitud ?? '' }}</td>
            </tr>
            <tr>
                <th>Fecha Prevista de Inicio</th>
                <td>{{ $fecha_inicio ?? '' }}</td>
            </tr>
            <tr>
                <th>Fecha Prevista de Fin</th>
                <td>{{ $fecha_fin ?? 'Indefinido' }}</td>
            </tr>
            <tr>
                <th>Tipo de Jornada</th>
                <td>{{ $tipo_jornada ?? '' }}</td>
            </tr>
            <tr>
                <th>Horas Semanales</th>
                <td>{{ $horas_semanales ?? '' }} horas</td>
            </tr>
            <tr>
                <th>Salario Bruto Mensual</th>
                <td>{{ $salario ?? '' }}</td>
            </tr>
            @if($direccion_trabajo ?? false)
            <tr>
                <th>Centro de Trabajo</th>
                <td>{{ $direccion_trabajo }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Datos del Trabajador Propuesto -->
    <div class="section">
        <div class="section-title">3. DATOS DEL TRABAJADOR PROPUESTO</div>

        <table class="data-table">
            <tr>
                <th>Nombre y Apellidos</th>
                <td>{{ $nombre_completo ?? '' }}</td>
            </tr>
            <tr>
                <th>Número de Pasaporte</th>
                <td>{{ $pasaporte ?? '' }}</td>
            </tr>
            <tr>
                <th>NIE (si dispone)</th>
                <td>{{ $nie ?? 'Pendiente de asignación' }}</td>
            </tr>
            <tr>
                <th>Fecha de Nacimiento</th>
                <td>{{ $fecha_nacimiento ?? '' }}</td>
            </tr>
            <tr>
                <th>Nacionalidad</th>
                <td>{{ $nacionalidad ?? '' }}</td>
            </tr>
            <tr>
                <th>País de Nacimiento</th>
                <td>{{ $pais_nacimiento ?? '' }}</td>
            </tr>
        </table>
    </div>

    <div class="page-break"></div>

    <!-- Justificación de la Necesidad -->
    <div class="section">
        <div class="section-title">4. JUSTIFICACIÓN DE LA NECESIDAD DE CONTRATACIÓN</div>

        <div class="subsection-title">4.1. Situación del Sector y la Empresa</div>
        <div class="paragraph">
            La empresa <strong>{{ $razon_social ?? '' }}</strong>, dedicada a la actividad agrícola
            con código CNAE {{ $cnae ?? '' }}, se encuentra en la necesidad de incorporar personal
            para cubrir las demandas de producción de la campaña {{ $campaña ?? '' }}.
        </div>

        <div class="subsection-title">4.2. Descripción del Puesto y Funciones</div>
        <div class="paragraph">
            El puesto de <strong>{{ $puesto_trabajo ?? '' }}</strong> requiere la realización de las
            siguientes funciones principales:
        </div>
        <ul class="list">
            <li>Tareas propias del sector agrícola según campaña y cultivo.</li>
            <li>Recolección, manipulación y preparación de productos agrícolas.</li>
            <li>Mantenimiento básico de instalaciones y herramientas de trabajo.</li>
            <li>Cumplimiento de las normas de seguridad e higiene en el trabajo.</li>
            <li>Cualquier otra tarea relacionada con la actividad agrícola encomendada por el responsable.</li>
        </ul>

        <div class="subsection-title">4.3. Motivos de la Contratación</div>
        <div class="highlight-box">
            <p><strong>Incremento de la actividad productiva:</strong> La campaña agrícola requiere
            un refuerzo de la plantilla para atender el volumen de trabajo previsto.</p>
            <p style="margin-top: 10px;"><strong>Dificultad para cubrir el puesto:</strong> No ha sido
            posible encontrar candidatos nacionales o comunitarios disponibles para ocupar este puesto
            en las condiciones ofertadas.</p>
        </div>

        <div class="subsection-title">4.4. Idoneidad del Trabajador Propuesto</div>
        <div class="paragraph">
            El trabajador <strong>{{ $nombre_completo ?? '' }}</strong>, de nacionalidad
            {{ $nacionalidad ?? '' }}, reúne las condiciones necesarias para desempeñar las funciones
            del puesto ofertado, contando con experiencia previa en el sector agrícola y disponibilidad
            para incorporarse en las fechas previstas.
        </div>
    </div>

    <!-- Compromiso del Empleador -->
    <div class="section">
        <div class="section-title">5. COMPROMISOS DEL EMPLEADOR</div>

        <div class="declaration">
            <p><strong>DECLARO BAJO MI RESPONSABILIDAD:</strong></p>
            <ul class="list" style="margin-top: 10px;">
                <li>Que la empresa se encuentra al corriente de sus obligaciones tributarias y con la Seguridad Social.</li>
                <li>Que el puesto de trabajo ofertado cumple con las condiciones laborales establecidas en el convenio colectivo aplicable.</li>
                <li>Que me comprometo a mantener la relación laboral durante el tiempo establecido en el contrato.</li>
                <li>Que garantizo al trabajador un alojamiento adecuado, en caso de ser necesario.</li>
                <li>Que los datos consignados en esta memoria son ciertos y me responsabilizo de su veracidad.</li>
            </ul>
        </div>
    </div>

    <!-- Firmas -->
    <div class="section">
        <div class="section-title">6. FIRMA Y FECHA</div>

        <div class="paragraph">
            En virtud de lo expuesto, solicito la autorización de residencia y trabajo para el trabajador
            extranjero indicado, comprometiéndome a cumplir todas las obligaciones legales derivadas de
            la contratación.
        </div>

        <div class="signatures">
            <div class="signature-box">
                <p><strong>EL EMPLEADOR/REPRESENTANTE</strong></p>
                <div class="signature-line">
                    <p>{{ $representante_nombre ?? '' }}</p>
                    <p>DNI/NIF: {{ $representante_documento ?? $nif ?? '' }}</p>
                </div>
            </div>
            <div class="signature-box">
                <p><strong>FECHA Y LUGAR</strong></p>
                <div class="signature-line">
                    <p>{{ $fecha_generacion ?? '' }}</p>
                    <p>&nbsp;</p>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Memoria Justificativa - Expediente: {{ $expediente_codigo ?? '' }}</p>
        <p>Generado el {{ $fecha_generacion ?? '' }} a las {{ $hora_generacion ?? '' }} | Sistema de Gestión de Extranjería</p>
    </div>
</body>
</html>
