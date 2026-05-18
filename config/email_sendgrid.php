<?php
/**
 * Funciones para envío de emails usando SendGrid API
 * Usa variables de entorno directamente (Railway)
 */

require_once __DIR__ . '/../vendor/autoload.php';

use SendGrid\Mail\Mail;

/**
 * Envía email usando SendGrid API
 */
function enviarEmailSendGrid($destinatario, $asunto, $html, $textoPlano = '') {
    // Usar variables de entorno directamente (Railway las inyecta)
    $apiKey = $_ENV['SENDGRID_API_KEY'] ?? '';
    $fromEmail = $_ENV['SENDGRID_FROM_EMAIL'] ?? 'santiagolizcanosuarez@gmail.com';
    $fromName = $_ENV['SENDGRID_FROM_NAME'] ?? 'Sistema PQRS';
    
    if (empty($apiKey)) {
        error_log("SendGrid API Key no configurada en variables de entorno");
        return false;
    }

    $email = new Mail();
    $email->setFrom($fromEmail, $fromName);
    $email->addTo($destinatario);
    $email->setSubject($asunto);
    
    if (!empty($textoPlano)) {
        $email->addContent("text/plain", $textoPlano);
    }
    $email->addContent("text/html", $html);

    $sendgrid = new \SendGrid($apiKey);
    
    try {
        $response = $sendgrid->send($email);
        $statusCode = $response->statusCode();
        
        error_log("SendGrid Response Status: " . $statusCode);
        
        if ($statusCode >= 200 && $statusCode < 300) {
            return true;
        } else {
            error_log("SendGrid Error Body: " . $response->body());
            return false;
        }
    } catch (Exception $e) {
        error_log("Error SendGrid Exception: " . $e->getMessage());
        return false;
    }
}