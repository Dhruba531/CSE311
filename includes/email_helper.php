<?php
// includes/email_helper.php

/**
 * Sends an email using raw SMTP (no external libraries required).
 * Requires SMTP credentials to be defined in config.php.
 */
function send_email($to, $subject, $body) {
    if (!defined('SMTP_HOST') || !defined('SMTP_USER') || SMTP_USER === 'your_email@gmail.com') {
        // Fallback to local log if not configured
        $logFile = __DIR__ . '/../email_log.txt';
        $logMessage = "[" . date('Y-m-d H:i:s') . "] (MOCK) To: $to | Subject: $subject | Body: $body" . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        return true;
    }

    $host = SMTP_HOST;
    $port = SMTP_PORT;
    $username = SMTP_USER;
    $password = SMTP_PASS;
    $from = SMTP_FROM;
    $fromName = SMTP_FROM_NAME;

    try {
        $socket = fsockopen($host, $port, $errno, $errstr, 15);
        if (!$socket) {
            throw new Exception("Could not connect to SMTP host: $errstr ($errno)");
        }

        server_parse($socket, "220");
        
        fputs($socket, "EHLO " . $host . "\r\n");
        server_parse($socket, "250");

        if ($port == 587) {
            fputs($socket, "STARTTLS\r\n");
            server_parse($socket, "220");
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            fputs($socket, "EHLO " . $host . "\r\n");
            server_parse($socket, "250");
        }

        fputs($socket, "AUTH LOGIN\r\n");
        server_parse($socket, "334");

        fputs($socket, base64_encode($username) . "\r\n");
        server_parse($socket, "334");

        fputs($socket, base64_encode($password) . "\r\n");
        server_parse($socket, "235");

        fputs($socket, "MAIL FROM: <$username>\r\n");
        server_parse($socket, "250");

        fputs($socket, "RCPT TO: <$to>\r\n");
        server_parse($socket, "250");

        fputs($socket, "DATA\r\n");
        server_parse($socket, "354");

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: $fromName <$username>" . "\r\n";
        $headers .= "Reply-To: $fromName <$username>" . "\r\n";
        $headers .= "To: $to" . "\r\n";
        $headers .= "Subject: $subject" . "\r\n";

        fputs($socket, "$headers\r\n\r\n$body\r\n.\r\n");
        server_parse($socket, "250");

        fputs($socket, "QUIT\r\n");
        fclose($socket);

        // Also log for debugging
        $logFile = __DIR__ . '/../email_log.txt';
        $logMessage = "[" . date('Y-m-d H:i:s') . "] (SMTP) Sent to $to: $subject\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);

        return true;

    } catch (Exception $e) {
        $logFile = __DIR__ . '/../email_log.txt';
        $logMessage = "[" . date('Y-m-d H:i:s') . "] (SMTP ERROR) " . $e->getMessage() . "\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
        return false;
    }
}

function server_parse($socket, $response) {
    $server_response = '';
    while (substr($server_response, 3, 1) != ' ') {
        if (!($server_response = fgets($socket, 256))) {
            throw new Exception("Error while fetching server response codes.");
        }
    }
    if (substr($server_response, 0, 3) != $response) {
        throw new Exception("Unable to send email. Server said: $server_response");
    }
}
?>
