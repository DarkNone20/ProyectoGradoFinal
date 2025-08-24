<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Devolución con Observaciones</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse; margin-top: 20px; margin-bottom: 20px; border: 1px solid #cccccc;">
        <!-- Encabezado del Correo -->
        <tr>
            <td align="center" bgcolor="#003366" style="padding: 20px 0; color: #ffffff; font-size: 24px; font-weight: bold;">
                Reporte de Devolución
            </td>
        </tr>
        <!-- Cuerpo del Correo -->
        <tr>
            <td bgcolor="#ffffff" style="padding: 40px 30px;">
                <h1 style="font-size: 20px; margin: 0; color: #333333;">Se ha registrado una devolución con observaciones</h1>
                <p style="margin: 20px 0; color: #555555; font-size: 16px; line-height: 1.5;">
                    A continuación, se detallan los datos del préstamo y la nota dejada por el usuario.
                </p>

                <!-- Sección de Datos -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                    <tr>
                        <!-- Datos del Usuario -->
                        <td width="48%" valign="top">
                            <h3 style="color: #003366; border-bottom: 2px solid #eeeeee; padding-bottom: 10px;">Datos del Usuario</h3>
                            <p style="color: #555555; margin: 10px 0;">
                                <strong>Nombre:</strong> {{ $datos['usuario']->Nombre }} {{ $datos['usuario']->Apellidos }}<br>
                                <strong>Documento:</strong> {{ $datos['usuario']->DocumentoId }}<br>
                                <strong>Email:</strong> {{ $datos['usuario']->email }}
                            </p>
                        </td>
                        <td width="4%"></td>
                        <!-- Datos del Equipo y Préstamo -->
                        <td width="48%" valign="top">
                            <h3 style="color: #003366; border-bottom: 2px solid #eeeeee; padding-bottom: 10px;">Datos del Equipo y Préstamo</h3>
                            <p style="color: #555555; margin: 10px 0;">
                                <strong>Marca:</strong> {{ $datos['equipo']->Marca }}<br>
                                <strong>Modelo:</strong> {{ $datos['equipo']->Modelo }}<br>
                                <strong>Serial:</strong> {{ $datos['prestamo']->Serial }}<br>
                                <!-- CAMPO AÑADIDO -->
                                <strong>Sala/Móvil:</strong> {{ $datos['prestamo']->SalaMovil ?? 'No especificado' }}
                            </p>
                        </td>
                    </tr>
                </table>

                <!-- Observaciones -->
                <h3 style="color: #003366; border-bottom: 2px solid #eeeeee; padding-bottom: 10px; margin-top: 30px;">Observaciones del Usuario</h3>
                <div style="background-color: #f9f9f9; border: 1px solid #dddddd; padding: 15px; border-radius: 5px; font-size: 16px; color: #333;">
                    <p style="margin: 0;"><em>"{{ $datos['observaciones'] }}"</em></p>
                </div>

                <!-- Datos de la Devolución -->
                 <h3 style="color: #003366; border-bottom: 2px solid #eeeeee; padding-bottom: 10px; margin-top: 30px;">Detalles de la Devolución</h3>
                 <p style="color: #555555; margin: 10px 0;">
                    <strong>Fecha de Devolución:</strong> {{ $datos['fechaDevolucion'] }}<br>
                    <strong>Hora de Devolución:</strong> {{ $datos['horaDevolucion'] }}
                 </p>

                <p style="margin-top: 30px; color: #555555; font-size: 16px;">
                    Por favor, proceda a revisar el estado del equipo.
                </p>
            </td>
        </tr>
        <!-- Pie de Página -->
        <tr>
            <td bgcolor="#eeeeee" style="padding: 20px 30px;">
                <p style="margin: 0; color: #888888; font-size: 12px; text-align: center;">
                    Este es un correo generado automáticamente por el Sistema de Préstamo de Portátiles.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>