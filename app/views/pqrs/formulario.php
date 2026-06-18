<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario PQRS - Sistema PQRS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>public/css/estilos.css">
</head>
<body class="formulario-page">

    <header style="background:#ffffff;box-shadow:0 1px 3px rgba(0,0,0,0.1);padding:1rem 0;margin-bottom:2rem;">
        <div style="max-width:800px;margin:0 auto;padding:0 1rem;display:flex;align-items:center;gap:0.5rem;">
            <i class="bi bi-clipboard-data" style="color:#1e40af;font-size:1.5rem;"></i>
            <span style="font-weight:700;color:#1e40af;font-size:1.25rem;">Sistema PQRS</span>
        </div>
    </header>

    <div class="formulario-container">
        <div class="formulario-card">

            <div class="formulario-header">
                <div class="formulario-tipo-badge">
                    <i class="bi bi-file-text"></i>
                    <span><?php echo $nombreTipo; ?></span>
                </div>
                <h1 class="formulario-titulo">Complete su solicitud</h1>
            </div>

            <div class="persona-selector">
                <button type="button" class="persona-btn activo" onclick="cambiarPersona('natural')" data-persona="natural">
                    <i class="bi bi-person"></i>
                    <span>Persona Natural</span>
                </button>
                <button type="button" class="persona-btn" onclick="cambiarPersona('juridica')" data-persona="juridica">
                    <i class="bi bi-building"></i>
                    <span>Persona Juridica</span>
                </button>
                <button type="button" class="persona-btn" onclick="cambiarPersona('anonima')" data-persona="anonima">
                    <i class="bi bi-incognito"></i>
                    <span>Anonima</span>
                </button>
            </div>

            <form id="formPQRS" action="<?php echo BASE_PATH; ?>index.php?ruta=pqrs/radicar" method="POST" enctype="multipart/form-data" onsubmit="return validarFormulario()">

                <input type="hidden" name="tipo_pqrs" value="<?php echo $tipoPQRS; ?>">
                <input type="hidden" id="tipo_persona" name="tipo_persona" value="natural">

                <!-- PERSONA NATURAL -->
                <div id="seccion-natural" class="form-body">
                    <div class="form-row">
                        <div class="form-grupo">
                            <label class="form-label">Nombre completo <span class="requerido">*</span></label>
                            <input type="text" name="nombre" class="form-input" placeholder="Ej: Juan Perez Garcia" onblur="validarCampo(this, 'nombre')">
                            <span class="error-mensaje" id="error-nombre">Ingrese su nombre completo</span>
                        </div>
                        <div class="form-grupo">
                            <label class="form-label">Tipo de documento <span class="requerido">*</span></label>
                            <select name="tipo_documento" class="form-select" onblur="validarCampo(this, 'tipo_documento')">
                                <option value="">Seleccione...</option>
                                <option value="CC">Cedula de Ciudadania</option>
                                <option value="CE">Cedula de Extranjeria</option>
                                <option value="TI">Tarjeta de Identidad</option>
                                <option value="PAS">Pasaporte</option>
                            </select>
                            <span class="error-mensaje" id="error-tipo_documento">Seleccione un tipo de documento</span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-grupo">
                            <label class="form-label">Numero de documento <span class="requerido">*</span></label>
                            <input type="text" name="numero_documento" class="form-input" placeholder="Ej: 1234567890" onblur="validarCampo(this, 'numero_documento')">
                            <span class="error-mensaje" id="error-numero_documento">Ingrese su numero de documento</span>
                        </div>
                        <div class="form-grupo">
                            <label class="form-label">Telefono <span class="requerido">*</span></label>
                            <input type="tel" name="telefono" class="form-input" placeholder="Ej: 3001234567" onblur="validarCampo(this, 'telefono')">
                            <span class="error-mensaje" id="error-telefono">Ingrese un numero de telefono valido</span>
                        </div>
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Correo electronico <span class="requerido">*</span></label>
                        <input type="email" name="correo" class="form-input" placeholder="Ej: correo@ejemplo.com" onblur="validarCampo(this, 'correo')">
                        <span class="error-mensaje" id="error-correo">Ingrese un correo electronico valido</span>
                    </div>
                </div>

                <!-- PERSONA JURIDICA -->
                <div id="seccion-juridica" class="form-body oculto">
                    <div class="form-row">
                        <div class="form-grupo">
                            <label class="form-label">Razon social <span class="requerido">*</span></label>
                            <input type="text" name="razon_social" class="form-input" placeholder="Ej: Empresa S.A.S." onblur="validarCampo(this, 'razon_social')">
                            <span class="error-mensaje" id="error-razon_social">Ingrese la razon social</span>
                        </div>
                        <div class="form-grupo">
                            <label class="form-label">NIT <span class="requerido">*</span></label>
                            <input type="text" name="nit" class="form-input" placeholder="Ej: 900123456-7" onblur="validarCampo(this, 'nit')">
                            <span class="error-mensaje" id="error-nit">Ingrese el NIT</span>
                        </div>
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Nombre del representante legal <span class="requerido">*</span></label>
                        <input type="text" name="representante" class="form-input" placeholder="Ej: Carlos Rodriguez" onblur="validarCampo(this, 'representante')">
                        <span class="error-mensaje" id="error-representante">Ingrese el nombre del representante</span>
                    </div>
                    <div class="form-row">
                        <div class="form-grupo">
                            <label class="form-label">Correo corporativo <span class="requerido">*</span></label>
                            <input type="email" name="correo_corporativo" class="form-input" placeholder="Ej: contacto@empresa.com" onblur="validarCampo(this, 'correo_corporativo')">
                            <span class="error-mensaje" id="error-correo_corporativo">Ingrese un correo corporativo valido</span>
                        </div>
                        <div class="form-grupo">
                            <label class="form-label">Telefono <span class="requerido">*</span></label>
                            <input type="tel" name="telefono_juridica" class="form-input" placeholder="Ej: 6011234567" onblur="validarCampo(this, 'telefono_juridica')">
                            <span class="error-mensaje" id="error-telefono_juridica">Ingrese un numero de telefono valido</span>
                        </div>
                    </div>
                </div>

                <!-- ANONIMA -->
                <div id="seccion-anonima" class="form-body oculto">
                    <div style="padding:1rem;background:#fef3c7;border-radius:0.5rem;margin-bottom:1rem;">
                        <p style="margin:0;color:#92400e;font-size:0.875rem;">
                            <i class="bi bi-info-circle" style="margin-right:0.25rem;"></i>
                            Al ser anonima, no se requieren datos personales. Solo se necesita el asunto y descripcion de su solicitud.
                        </p>
                    </div>
                </div>

                <!-- CAMPOS COMUNES -->
                <div class="form-body" style="border-top:1px solid #e5e7eb;">
                    <div class="form-grupo">
                        <label class="form-label">Asunto <span class="requerido">*</span></label>
                        <input type="text" name="asunto" class="form-input" placeholder="Resumen breve de su solicitud" maxlength="250" onblur="validarCampo(this, 'asunto')">
                        <span class="error-mensaje" id="error-asunto">Ingrese el asunto de su solicitud</span>
                    </div>
                    <div class="form-grupo">
                        <label class="form-label">Descripcion detallada <span class="requerido">*</span></label>
                        <textarea name="descripcion" class="form-textarea" placeholder="Describa detalladamente su solicitud..." maxlength="5000" onblur="validarCampo(this, 'descripcion')"></textarea>
                        <span class="error-mensaje" id="error-descripcion">Ingrese la descripcion de su solicitud</span>
                    </div>

                    <div id="notificacion-correo" class="form-grupo">
                        <label class="checkbox-container" style="display:flex;align-items:center;gap:0.75rem;cursor:pointer;padding:0.75rem;background:#f0fdf4;border-radius:0.5rem;border:1px solid #bbf7d0;">
                            <input type="checkbox" name="notificar_correo" value="1" checked style="width:18px;height:18px;accent-color:#059669;">
                            <span style="font-size:0.875rem;color:#065f46;">
                                <i class="bi bi-envelope-check" style="margin-right:0.25rem;color:#059669;"></i>
                                <strong>Deseo recibir notificacion por correo electronico</strong> con el codigo de radicado y estado de mi solicitud
                            </span>
                        </label>
                    </div>

                    <div class="form-grupo">
                        <label class="form-label">Archivo adjunto (opcional)</label>
                        <div class="form-adjunto">
                            <input type="file" id="adjunto" name="adjunto" class="form-adjunto-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" onchange="mostrarArchivo(this)">
                            <label for="adjunto" class="form-adjunto-label">
                                <i class="bi bi-paperclip"></i>
                                <span id="nombre-archivo">Haga clic para adjuntar archivo (PDF, Word, JPG, PNG)</span>
                            </label>
                        </div>
                        <p class="form-ayuda">Tamano maximo: 5MB</p>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="<?php echo BASE_PATH; ?>index.php?ruta=pqrs/tipos" class="btn-volver">
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </a>
                    <button type="submit" class="btn-enviar">
                        <i class="bi bi-send"></i>
                        Enviar Solicitud
                    </button>
                </div>

            </form>
        </div>
    </div>

    <script>
    function cambiarPersona(tipo) {
        document.querySelectorAll('.persona-btn').forEach(btn => btn.classList.remove('activo'));
        document.querySelector(`.persona-btn[data-persona="${tipo}"]`).classList.add('activo');
        ['natural','juridica','anonima'].forEach(t => {
            document.getElementById(`seccion-${t}`).classList.add('oculto');
        });
        document.getElementById(`seccion-${tipo}`).classList.remove('oculto');
        document.getElementById('tipo_persona').value = tipo;
        document.querySelectorAll('.error-mensaje').forEach(e => e.classList.remove('visible'));
        document.querySelectorAll('.form-input,.form-select,.form-textarea').forEach(i => i.classList.remove('error'));

        // Ocultar opcion de notificacion por correo si es anonima
        const notificacionDiv = document.getElementById('notificacion-correo');
        if (notificacionDiv) {
            if (tipo === 'anonima') {
                notificacionDiv.style.display = 'none';
                // Desmarcar el checkbox para que no se envie al servidor
                const chk = notificacionDiv.querySelector('input[type="checkbox"]');
                if (chk) chk.checked = false;
            } else {
                notificacionDiv.style.display = '';
                // Volver a marcar por defecto
                const chk = notificacionDiv.querySelector('input[type="checkbox"]');
                if (chk) chk.checked = true;
            }
        }
    }

    function mostrarArchivo(input) {
        if (input.files && input.files[0]) {
            document.getElementById('nombre-archivo').textContent = input.files[0].name;
        }
    }

    function validarCampo(input, campo) {
        const valor = input.value.trim();
        const errorMsg = document.getElementById(`error-${campo}`);
        let valido = !!valor;
        if (campo.includes('correo') && valor && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor)) valido = false;
        if (campo.includes('telefono') && valor && !/^[0-9]{7,15}$/.test(valor.replace(/\s/g,''))) valido = false;
        input.classList.toggle('error', !valido);
        if (errorMsg) errorMsg.classList.toggle('visible', !valido);
        return valido;
    }

    function validarFormulario() {
        const tipoPersona = document.getElementById('tipo_persona').value;
        let campos = tipoPersona === 'natural'
            ? ['nombre','tipo_documento','numero_documento','telefono','correo']
            : tipoPersona === 'juridica'
            ? ['razon_social','nit','representante','correo_corporativo','telefono_juridica']
            : [];
        campos = campos.concat(['asunto','descripcion']);
        const valido = campos.every(campo => {
            const input = document.querySelector(`[name="${campo}"]`);
            return input ? validarCampo(input, campo) : true;
        });
        if (!valido) alert('Por favor complete todos los campos obligatorios marcados.');
        return valido;
    }
    </script>

</body>
</html>
