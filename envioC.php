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
$mail->Subject = 'Universidad de La Guajira - Facultad de Ingeniería de Sistemas';
$mail->Body    = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h1 style="color: #2c3e50; text-align: center;">Software de Gestión Académica</h1>
        
        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-top: 20px;">
            <h2 style="color:rgb(238, 156, 4);">Manual de Usuario</h2>
            <p>Estimado usuario,</p>
            <p>Adjunto encontrará la guía completa para el uso del sistema de gestión académica.</p>
            
            <div style="margin-top: 20px; padding: 10px; background-color: #e9ecef; border-left: 4px solid #3498db;">
                <p><strong>Fecha de envío:</strong> ' . date('d/m/Y H:i:s') . '</p>
                <p><strong>Departamento:</strong> Ingeniería de Sistemas</p>
            </div>
        </div>
        
        <p style="margin-top: 30px; text-align: center; color: #7f8c8d; font-size: 12px;">
            © ' . date('Y') . ' Universidad de La Guajira - Todos los derechos reservados
        </p>
    </div>
';

$mail->AltBody = "Software de Gestión Académica\n\n"
               . "Manual de Usuario\n"
               . "Estimado usuario,\n\n"
               . "Adjunto encontrará la guía completa para el uso del sistema de gestión académica.\n\n"
               . "Fecha de envío: " . date('d/m/Y H:i:s') . "\n"
               . "Departamento: Ingeniería de Sistemas\n\n"
               . "© " . date('Y') . " Universidad de La Guajira - Todos los derechos reservados";

$mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}