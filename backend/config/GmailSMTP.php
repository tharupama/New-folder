<?php
/**
 * Simple Gmail SMTP Client
 * Sends emails via Gmail SMTP without PHPMailer
 */

class GmailSMTP {
    private $host = 'smtp.gmail.com';
    private $port = 587;
    private $username;
    private $password;
    private $socket = null;
    private $timeout = 10;
    
    public function __construct($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }
    
    /**
     * Send email via Gmail SMTP
     */
    public function sendEmail($to, $subject, $htmlBody, $fromEmail, $fromName) {
        try {
            // Connect to Gmail SMTP
            $this->socket = stream_socket_client(
                "tcp://{$this->host}:{$this->port}",
                $errno,
                $errstr,
                $this->timeout
            );
            
            if (!$this->socket) {
                throw new Exception("Failed to connect to SMTP: $errstr ($errno)");
            }
            
            stream_set_timeout($this->socket, $this->timeout);
            
            // Read welcome message
            $this->readResponse();
            
            // Send EHLO
            $this->sendCommand("EHLO localhost");
            
            // Start TLS
            $this->sendCommand("STARTTLS");
            
            // Enable crypto
            if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception("Failed to enable crypto");
            }
            
            // Send EHLO again after TLS
            $this->sendCommand("EHLO localhost");
            
            // Authenticate
            $this->sendCommand("AUTH LOGIN");
            $this->sendCommand(base64_encode($this->username));
            $this->sendCommand(base64_encode($this->password));
            
            // Build email headers
            $headers = "From: " . trim(addslashes($fromName)) . " <{$fromEmail}>\r\n";
            $headers .= "To: {$to}\r\n";
            $headers .= "Subject: " . $this->encodeSubject($subject) . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "Content-Transfer-Encoding: 8bit\r\n";
            
            $body = $headers . "\r\n" . $htmlBody;
            
            // Send email
            $this->sendCommand("MAIL FROM:<{$fromEmail}>");
            $this->sendCommand("RCPT TO:<{$to}>");
            $this->sendCommand("DATA");
            
            fwrite($this->socket, $body . "\r\n.\r\n");
            $this->readResponse();
            
            // Quit
            $this->sendCommand("QUIT");
            
            fclose($this->socket);
            return true;
            
        } catch (Exception $e) {
            error_log("Gmail SMTP Error: " . $e->getMessage());
            if ($this->socket) {
                fclose($this->socket);
            }
            return false;
        }
    }
    
    /**
     * Send SMTP command
     */
    private function sendCommand($command) {
        fwrite($this->socket, $command . "\r\n");
        return $this->readResponse();
    }
    
    /**
     * Read SMTP response
     */
    private function readResponse() {
        $response = '';
        while ($line = fgets($this->socket, 1024)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        
        $code = intval(substr($response, 0, 3));
        if ($code >= 400) {
            throw new Exception("SMTP Error [$code]: " . trim($response));
        }
        
        return $response;
    }
    
    /**
     * Encode subject for email header
     */
    private function encodeSubject($subject) {
        return "=?UTF-8?B?" . base64_encode($subject) . "?=";
    }
}
?>
