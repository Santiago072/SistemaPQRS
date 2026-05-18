<?php
/**
 * Funciones para envío de emails usando SendGrid API
 */

require_once __DIR__ . '/../vendor/autoload.php';

use SendGrid\Mail\Mail;

/**
 * Envía email usando SendGrid API
 */
function enviarEmailSendGrid($destinatario, $asunto, $html, $textoPlano = '') {
    $config = require __DIR__ . '/email_config.php';
    
    if (empty($config['sendgrid_api_key'])) {
        error_log("SendGrid API Key no configurada");
        return false;
    }

    $email = new Mail();
    $email->setFrom($config['from_email'], $config['from_name']);
    $email->addTo($destinatario);
    $email->setSubject($asunto);
    
    if (!empty($textoPlano)) {
        $email->addContent("text/plain", $textoPlano);
    }
    $email->addContent("text/html", $html);

    $sendgrid = new \SendGrid($config['sendgrid_api_key']);
    
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