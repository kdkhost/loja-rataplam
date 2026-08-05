<?php

namespace App\Services\MercadoPago;

use App\Exceptions\MercadoPagoApiException;

class MercadoPagoPixResponseSanitizer
{
    private const QR_CODE_MAX_LENGTH = 2000;
    private const QR_CODE_BASE64_MAX_LENGTH = 100000;
    private const TICKET_URL_MAX_LENGTH = 2048;

    /**
     * Sanitiza e valida campos Pix da resposta da API
     *
     * @param array $paymentData Dados brutos da resposta da API
     * @return array Campos Pix sanitizados
     * @throws MercadoPagoApiException
     */
    public function sanitize(array $paymentData): array
    {
        $sanitized = [];

        if (!empty($paymentData['qr_code'])) {
            $sanitized['qr_code'] = $this->validateQrCode($paymentData['qr_code']);
        }

        if (!empty($paymentData['qr_code_base64'])) {
            $sanitized['qr_code_base64'] = $this->validateQrCodeBase64($paymentData['qr_code_base64']);
        }

        if (!empty($paymentData['ticket_url'])) {
            $sanitized['ticket_url'] = $this->validateTicketUrl($paymentData['ticket_url']);
        }

        if (!empty($paymentData['expiration_date'])) {
            $sanitized['expiration_date'] = $paymentData['expiration_date'];
        }

        return $sanitized;
    }

    /**
     * Valida QR Code copia-e-cola
     */
    private function validateQrCode(string $qrCode): string
    {
        if (strlen($qrCode) > self::QR_CODE_MAX_LENGTH) {
            throw new MercadoPagoApiException('QR Code excede limite de tamanho.');
        }

        if (preg_match('/^(javascript:|data:)/i', $qrCode)) {
            throw new MercadoPagoApiException('QR Code contém protocolo inválido.');
        }

        // Bloquear caracteres de controle
        if (preg_match('/[\x00-\x1F\x7F]/', $qrCode)) {
            throw new MercadoPagoApiException('QR Code contém caracteres inválidos.');
        }

        return $qrCode;
    }

    /**
     * Valida QR Code Base64
     */
    private function validateQrCodeBase64(string $qrCodeBase64): string
    {
        if (strlen($qrCodeBase64) > self::QR_CODE_BASE64_MAX_LENGTH) {
            throw new MercadoPagoApiException('QR Code Base64 excede limite de tamanho.');
        }

        return $qrCodeBase64;
    }

    /**
     * Valida ticket URL
     */
    private function validateTicketUrl(string $ticketUrl): string
    {
        if (!preg_match('/^https:\/\//i', $ticketUrl)) {
            throw new MercadoPagoApiException('Ticket URL deve usar HTTPS.');
        }

        if (strlen($ticketUrl) > self::TICKET_URL_MAX_LENGTH) {
            throw new MercadoPagoApiException('Ticket URL excede limite de tamanho.');
        }

        if (preg_match('/^(javascript:|data:)/i', $ticketUrl)) {
            throw new MercadoPagoApiException('Ticket URL contém protocolo inválido.');
        }

        return $ticketUrl;
    }
}
