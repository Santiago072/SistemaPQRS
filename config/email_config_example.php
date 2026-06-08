<?php
/**
 * ARCHIVO DE EJEMPLO — Configuración de Correo SMTP (PHPMailer)
 *
 * INSTRUCCIONES:
 * 1. Copia este archivo y renómbralo como: config/email_config.php
 * 2. Completa los valores con tus credenciales SMTP reales
 * 3. NUNCA subas config/email_config.php al repositorio (ya está en .gitignore)
 *
 * CÓMO OBTENER UNA CONTRASEÑA DE APLICACIÓN EN GMAIL:
 * 1. Activa la verificación en dos pasos en tu cuenta Google
 * 2. Ve a: Cuenta Google → Seguridad → Contraseñas de aplicaciones
 * 3. Crea una nueva con nombre "Sistema PQRS"
 * 4. Copia los 16 caracteres generados en smtp_password
 */

return [
    // ── Servidor SMTP ──────────────────────────────────────────────────────────
    'smtp_host'       => 'smtp.gmail.com',    // Gmail: smtp.gmail.com
                                               // Outlook: smtp.office365.com
                                               // Yahoo: smtp.mail.yahoo.com
    'smtp_port'       => 587,                  // 587 = TLS (recomendado) | 465 = SSL
    'smtp_encryption' => 'tls',               // 'tls' o 'ssl'

    // ── Credenciales SMTP ──────────────────────────────────────────────────────
    'smtp_user'       => 'tu_correo@gmail.com',        // Tu dirección de correo
    'smtp_password'   => 'xxxx xxxx xxxx xxxx',        // Contraseña de aplicación (16 chars)
                                                        // NO uses tu contraseña normal de Gmail

    // ── Remitente ──────────────────────────────────────────────────────────────
    'from_email'      => 'tu_correo@gmail.com',        // Correo que aparece como remitente
    'from_name'       => 'Sistema PQRS',               // Nombre que aparece como remitente
];
