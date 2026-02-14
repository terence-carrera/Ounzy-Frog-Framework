<?php

namespace Frog\Infrastructure\Mail;

use RuntimeException;

class MailManager
{
    public function __construct(private array $config) {}

    public function send(string $to, string $subject, string $body, array $headers = []): bool
    {
        return $this->sendMessage($to, $subject, $body, null, [], $headers);
    }

    public function sendHtml(string $to, string $subject, string $html, string $text = '', array $headers = []): bool
    {
        $text = $text !== '' ? $text : strip_tags($html);
        return $this->sendMessage($to, $subject, $text, $html, [], $headers);
    }

    public function sendMessage(
        string $to,
        string $subject,
        string $textBody,
        ?string $htmlBody = null,
        array $attachments = [],
        array $headers = []
    ): bool {
        $driver = $this->config['default'] ?? 'mail';
        $drivers = $this->config['drivers'] ?? [];
        $cfg = $drivers[$driver] ?? null;
        if (!$cfg) {
            throw new RuntimeException("Mail driver '{$driver}' not configured");
        }

        $from = $this->config['from'] ?? [];
        $headers = $this->normalizeHeaders($headers, $from);
        [$headers, $body] = $this->buildPayload($to, $subject, $textBody, $htmlBody, $attachments, $headers);

        return match ($driver) {
            'mail' => $this->sendWithPhpMail($to, $subject, $body, $headers),
            'smtp' => $this->sendWithSmtp($to, $subject, $body, $headers, $cfg),
            'log' => $this->sendToLog($to, $subject, $body, $cfg['path'] ?? ''),
            default => throw new RuntimeException("Unsupported mail driver '{$driver}'"),
        };
    }

    private function sendWithPhpMail(string $to, string $subject, string $body, array $headers): bool
    {
        $headerLines = $this->buildHeaderLines($headers);
        return mail($to, $subject, $body, $headerLines);
    }

    private function sendWithSmtp(string $to, string $subject, string $body, array $headers, array $cfg): bool
    {
        $host = $cfg['host'] ?? '127.0.0.1';
        $port = (int)($cfg['port'] ?? 587);
        $encryption = $cfg['encryption'] ?? 'tls';
        $timeout = (int)($cfg['timeout'] ?? 10);
        $username = $cfg['username'] ?? '';
        $password = $cfg['password'] ?? '';

        $transport = $encryption === 'ssl' ? 'ssl://' : '';
        $socket = @stream_socket_client(
            $transport . $host . ':' . $port,
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT
        );

        if (!$socket) {
            throw new RuntimeException('SMTP connection failed: ' . $errstr);
        }

        stream_set_timeout($socket, $timeout);
        $this->expectCode($socket, [220]);

        $this->writeLine($socket, 'EHLO frog');
        $this->expectCode($socket, [250]);

        if ($encryption === 'tls') {
            $this->writeLine($socket, 'STARTTLS');
            $this->expectCode($socket, [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('STARTTLS failed');
            }
            $this->writeLine($socket, 'EHLO frog');
            $this->expectCode($socket, [250]);
        }

        if ($username !== '') {
            $this->writeLine($socket, 'AUTH LOGIN');
            $this->expectCode($socket, [334]);
            $this->writeLine($socket, base64_encode($username));
            $this->expectCode($socket, [334]);
            $this->writeLine($socket, base64_encode($password));
            $this->expectCode($socket, [235]);
        }

        $from = $this->extractFromAddress($headers);
        $this->writeLine($socket, 'MAIL FROM:<' . $from . '>');
        $this->expectCode($socket, [250]);

        foreach ($this->splitRecipients($to) as $recipient) {
            $this->writeLine($socket, 'RCPT TO:<' . $recipient . '>');
            $this->expectCode($socket, [250, 251]);
        }

        $this->writeLine($socket, 'DATA');
        $this->expectCode($socket, [354]);

        $message = $this->buildMessage($headers, $body);
        $this->writeLine($socket, $message . "\r\n.");
        $this->expectCode($socket, [250]);

        $this->writeLine($socket, 'QUIT');
        @fclose($socket);
        return true;
    }

    private function sendToLog(string $to, string $subject, string $body, string $path): bool
    {
        if ($path === '') {
            throw new RuntimeException('Mail log path is not configured');
        }
        $entry = "To: {$to}\nSubject: {$subject}\n\n{$body}\n\n";
        return (bool)@file_put_contents($path, $entry, FILE_APPEND);
    }

    private function normalizeHeaders(array $headers, array $from): array
    {
        if (!isset($headers['From'])) {
            $address = $from['address'] ?? '';
            $name = $from['name'] ?? '';
            if ($address !== '') {
                $headers['From'] = $name ? "{$name} <{$address}>" : $address;
            }
        }
        return $headers;
    }

    private function buildPayload(
        string $to,
        string $subject,
        string $textBody,
        ?string $htmlBody,
        array $attachments,
        array $headers
    ): array {
        $headers['To'] = $headers['To'] ?? $to;
        $headers['Subject'] = $headers['Subject'] ?? $subject;
        $headers['MIME-Version'] = $headers['MIME-Version'] ?? '1.0';

        $textBody = $this->normalizeBody($textBody);
        $htmlBody = $htmlBody !== null ? $this->normalizeBody($htmlBody) : null;

        $attachments = $this->normalizeAttachments($attachments);
        $hasAttachments = count($attachments) > 0;

        if (!$hasAttachments && $htmlBody === null) {
            $headers['Content-Type'] = $headers['Content-Type'] ?? 'text/plain; charset=utf-8';
            return [$headers, $textBody];
        }

        if (!$hasAttachments && $htmlBody !== null) {
            $boundary = 'alt_' . bin2hex(random_bytes(8));
            $headers['Content-Type'] = 'multipart/alternative; boundary="' . $boundary . '"';
            $body = $this->buildAlternativeBody($boundary, $textBody, $htmlBody);
            return [$headers, $body];
        }

        $mixed = 'mix_' . bin2hex(random_bytes(8));
        $headers['Content-Type'] = 'multipart/mixed; boundary="' . $mixed . '"';

        $parts = [];
        if ($htmlBody !== null) {
            $alt = 'alt_' . bin2hex(random_bytes(8));
            $parts[] = $this->wrapPart(
                $mixed,
                'multipart/alternative; boundary="' . $alt . '"',
                $this->buildAlternativeBody($alt, $textBody, $htmlBody)
            );
        } else {
            $parts[] = $this->wrapPart(
                $mixed,
                'text/plain; charset=utf-8',
                $this->encodeQuotedPrintable($textBody)
            );
        }

        foreach ($attachments as $attachment) {
            $parts[] = $this->buildAttachmentPart($mixed, $attachment);
        }

        $body = implode("\r\n", $parts) . "\r\n--{$mixed}--\r\n";
        return [$headers, $body];
    }

    private function buildHeaderLines(array $headers): string
    {
        $lines = [];
        foreach ($headers as $key => $value) {
            $lines[] = $key . ': ' . $value;
        }
        return implode("\r\n", $lines);
    }

    private function buildMessage(array $headers, string $body): string
    {
        $headerLines = $this->buildHeaderLines($headers);
        $body = $this->normalizeBody($body);
        return $headerLines . "\r\n\r\n" . $body;
    }

    private function buildAlternativeBody(string $boundary, string $text, string $html): string
    {
        $parts = [];
        $parts[] = "--{$boundary}\r\nContent-Type: text/plain; charset=utf-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n" . $this->encodeQuotedPrintable($text);
        $parts[] = "--{$boundary}\r\nContent-Type: text/html; charset=utf-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n" . $this->encodeQuotedPrintable($html);
        return implode("\r\n", $parts) . "\r\n--{$boundary}--\r\n";
    }

    private function wrapPart(string $boundary, string $contentType, string $content): string
    {
        return "--{$boundary}\r\nContent-Type: {$contentType}\r\n\r\n" . $content;
    }

    private function buildAttachmentPart(string $boundary, array $attachment): string
    {
        $content = chunk_split(base64_encode($attachment['content']));
        $name = $attachment['name'];
        $type = $attachment['mime'];
        return "--{$boundary}\r\n" .
            "Content-Type: {$type}; name=\"{$name}\"\r\n" .
            "Content-Transfer-Encoding: base64\r\n" .
            "Content-Disposition: attachment; filename=\"{$name}\"\r\n\r\n" .
            $content;
    }

    private function normalizeAttachments(array $attachments): array
    {
        $out = [];
        foreach ($attachments as $attachment) {
            if (is_string($attachment)) {
                $attachment = ['path' => $attachment];
            }
            if (!is_array($attachment) || empty($attachment['path'])) {
                throw new RuntimeException('Invalid attachment format');
            }
            $path = $attachment['path'];
            if (!is_file($path)) {
                throw new RuntimeException('Attachment not found: ' . $path);
            }
            $name = $attachment['name'] ?? basename($path);
            $mime = $attachment['mime'] ?? $this->detectMime($path);
            $content = (string)file_get_contents($path);
            $out[] = ['name' => $name, 'mime' => $mime, 'content' => $content];
        }
        return $out;
    }

    private function detectMime(string $path): string
    {
        if (class_exists('finfo')) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($path);
            if (is_string($mime)) return $mime;
        }
        return 'application/octet-stream';
    }

    private function normalizeBody(string $body): string
    {
        $body = str_replace(["\r\n", "\r"], "\n", $body);
        return str_replace("\n", "\r\n", $body);
    }

    private function encodeQuotedPrintable(string $text): string
    {
        return quoted_printable_encode($text);
    }

    private function extractFromAddress(array $headers): string
    {
        $from = $headers['From'] ?? '';
        if (preg_match('/<([^>]+)>/', $from, $m)) {
            return $m[1];
        }
        return $from !== '' ? $from : 'noreply@example.com';
    }

    private function splitRecipients(string $to): array
    {
        $parts = array_map('trim', explode(',', $to));
        return array_filter($parts, fn($p) => $p !== '');
    }

    private function writeLine($socket, string $line): void
    {
        fwrite($socket, $line . "\r\n");
    }

    private function expectCode($socket, array $codes): void
    {
        $response = $this->readResponse($socket);
        $code = (int)substr($response, 0, 3);
        if (!in_array($code, $codes, true)) {
            throw new RuntimeException('SMTP error: ' . trim($response));
        }
    }

    private function readResponse($socket): string
    {
        $response = '';
        while (!feof($socket)) {
            $line = fgets($socket, 515);
            if ($line === false) break;
            $response .= $line;
            if (preg_match('/^\d{3} /', $line)) {
                break;
            }
        }
        return $response;
    }
}
