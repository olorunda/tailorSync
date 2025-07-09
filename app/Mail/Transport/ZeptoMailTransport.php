<?php

namespace App\Mail\Transport;

use Illuminate\Mail\Transport\Transport;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;

class ZeptoMailTransport extends Transport
{
    /**
     * The ZeptoMail API endpoint.
     *
     * @var string
     */
    protected $endpoint = 'https://api.zeptomail.com/v1.1/email';

    /**
     * The ZeptoMail API key.
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Create a new ZeptoMail transport instance.
     *
     * @param  string  $apiKey
     * @return void
     */
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritdoc}
     */
    public function send(SentMessage $message, array $failedRecipients = []): ?SentMessage
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $payload = $this->buildPayload($email);

        $response = Http::withHeaders([
            'Authorization' => 'Zoho-enczapikey ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->endpoint, $payload);

        if (!$response->successful()) {
            throw new \Exception('ZeptoMail API error: ' . $response->body());
        }

        $this->sendPerformed($message);

        return $message;
    }

    /**
     * Build the API payload from the message.
     *
     * @param  \Symfony\Component\Mime\Email  $email
     * @return array
     */
    protected function buildPayload(Email $email): array
    {
        $payload = [
            'from' => $this->formatAddress($email->getFrom()[0]),
            'to' => $this->formatAddresses($email->getTo()),
            'subject' => $email->getSubject(),
        ];

        // Add CC recipients if present
        if ($email->getCc()) {
            $payload['cc'] = $this->formatAddresses($email->getCc());
        }

        // Add BCC recipients if present
        if ($email->getBcc()) {
            $payload['bcc'] = $this->formatAddresses($email->getBcc());
        }

        // Add reply-to if present
        if ($email->getReplyTo()) {
            $payload['reply_to'] = $this->formatAddress($email->getReplyTo()[0]);
        }

        // Add HTML content if present
        if ($htmlBody = $email->getHtmlBody()) {
            $payload['htmlbody'] = $htmlBody;
        }

        // Add text content if present
        if ($textBody = $email->getTextBody()) {
            $payload['textbody'] = $textBody;
        }

        // Add attachments if present
        if ($attachments = $email->getAttachments()) {
            $payload['attachments'] = $this->formatAttachments($attachments);
        }

        return $payload;
    }

    /**
     * Format an address for the API payload.
     *
     * @param  \Symfony\Component\Mime\Address  $address
     * @return array
     */
    protected function formatAddress(Address $address): array
    {
        return [
            'address' => $address->getAddress(),
            'name' => $address->getName(),
        ];
    }

    /**
     * Format an array of addresses for the API payload.
     *
     * @param  array  $addresses
     * @return array
     */
    protected function formatAddresses(array $addresses): array
    {
        return array_map(function ($address) {
            return $this->formatAddress($address);
        }, $addresses);
    }

    /**
     * Format attachments for the API payload.
     *
     * @param  array  $attachments
     * @return array
     */
    protected function formatAttachments(array $attachments): array
    {
        return array_map(function ($attachment) {
            return [
                'name' => $attachment->getFilename(),
                'content' => base64_encode($attachment->getBody()),
                'mime_type' => $attachment->getContentType(),
            ];
        }, $attachments);
    }
}
