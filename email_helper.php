<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';

function sendEmail(
    string $recipient,
    string $subject,
    string $body
): bool {
    $config = require __DIR__ . '/email_config.php';

    $mail = new PHPMailer(true);

    try{
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->Port = (int)$config['port'];

        $mail->SMTPAuth = false;
        $mail->SMTPSecure = false;
        $mail->SMTPAutoTLS = false;

        $mail->setFrom(
            $config['from_email'],
            $config['from_name']
        );

        $mail->addAddress($recipient);

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->AltBody = strip_tags(
            str_replace(
                ['<br>', '<br/>', '<br />', '</p>', '</h1>', '</h2>'],
                "\n",
                $body
            )
        );

        $mail->send();

        return true;
    } catch (Exception $e){
        error_log(
            'BrightSmile email error: ' . $mail->ErrorInfo
        );
        
        return false;
    }
}
?>