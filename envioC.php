<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/libreriasC/vendor/autoload.php';

function enviarCorreoPrueba($destinatario) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = '2001pruebac@gmail.com';
        $mail->Password   = 'yznp rtsa nrbn wayz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('2001pruebac@gmail.com', 'Prueba de Correo');
        $mail->addAddress($destinatario);


    $mail->addAttachment(__DIR__ . '/php/pdfG.pdf'); // Ruta al archivo

        $mail->isHTML(true);
        $mail->Subject = 'Prueba de PHPMailer - ¡Funciona!';
        $mail->Body    = '<h1>¡El correo funciona correctamente!</h1>
                          <p>Este es un correo de prueba enviado desde PHPMailer.</p>
                          <p><strong>Fecha:</strong> ' . date('d/m/Y H:i:s') . '</p>';
        $mail->AltBody = "¡El correo funciona correctamente!\nEste es un correo de prueba.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}