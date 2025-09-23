<?php
function send_smtp_mail($to, $subject, $body, $from = SMTP_FROM, $debug = false) {
    $smtp_host = SMTP_HOST;
    $smtp_port = SMTP_PORT;
    $smtp_user = SMTP_USER;
    $smtp_pass = SMTP_PASS;

    $headers = "From: $from\r\n";
    $headers .= "To: $to\r\n";
    $headers .= "Subject: $subject\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    $message = $headers . "\r\n" . $body;

    $socket = fsockopen($smtp_host, $smtp_port, $errno, $errstr, 10);
    if (!$socket) {
        return "❌ Connection failed: $errstr ($errno)";
    }

    if ($debug) {
        $greeting = fgets($socket, 512);
        echo "<pre>← $greeting</pre>";
    } else {
        fgets($socket, 512); // discard greeting silently
    }

    $log = function($cmd) use ($socket, $debug) {
        fwrite($socket, $cmd . "\r\n");
        $response = fgets($socket, 512);
        if ($debug) {
            echo "<pre>→ $cmd\n← $response</pre>";
        }
        return $response;
    };

    fwrite($socket, "EHLO deploysmart.dev.mspot.se\r\n");
    do {
        $response = fgets($socket, 512);
        if ($debug) echo "<pre>← $response</pre>";
    } while (strpos($response, '250 ') !== 0);

    $response = $log("STARTTLS");
    if (strpos($response, '220') !== 0) {
        return "❌ Server did not accept STARTTLS: $response";
    }

    $crypto = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
    if (!$crypto) {
        return "❌ Failed to start TLS encryption.";
    }

    fwrite($socket, "EHLO deploysmart.dev.mspot.se\r\n");
    do {
        $response = fgets($socket, 512);
        if ($debug) echo "<pre>← $response</pre>";
    } while (strpos($response, '250 ') !== 0);

    $log("AUTH LOGIN");
    $log(base64_encode($smtp_user));
    $log(base64_encode($smtp_pass));
    $log("MAIL FROM:<$from>");
    $log("RCPT TO:<$to>");
    $log("DATA");
    $log($message . "\r\n.");
    $log("QUIT");

    fclose($socket);
    return "✅ Email sent to $to";
}
?>