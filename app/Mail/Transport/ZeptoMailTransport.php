<?php

namespace App\Mail\Transport;

use GuzzleHttp\ClientInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\RawMessage;

class ZeptoMailTransport extends AbstractTransport
{
    /**
     * Guzzle client instance.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * The ZeptoMail API key.
     *
     * @var string
     */
    protected $key;

    /**
     * The ZeptoMail API endpoint.
     *
     * @var string
     */
    protected $endpoint;

    /**
     * Create a new ZeptoMail transport instance.
     *
     * @param  \GuzzleHttp\ClientInterface  $client
     * @param  string  $key
     * @param  string  $endpoint
     * @return void
     */
    public function __construct(ClientInterface $client, $key, $endpoint = 'https://api.zeptomail.com/v1.1/email')
    {
        parent::__construct();

        $this->key = $key;
        $this->client = $client;
        $this->endpoint = $endpoint;
    }

    /**
     * {@inheritdoc}
     */
    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        if ($message instanceof Email) {
            $email = $message;
        } elseif ($message instanceof Message) {
            $email = MessageConverter::toEmail($message);
        } else {
            // If it's just a RawMessage and not a Message or Email, we can't convert it
            throw new \Exception('Cannot convert RawMessage to Email. Message must be an instance of Message or Email.');
        }

        $payload = $this->getPayload($email);

        $response = $this->client->request('POST', $this->endpoint, [
            'headers' => [
                'Authorization' => 'Zoho-enczapikey ' . $this->key,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 201) {
            throw new \Exception('ZeptoMail API error: ' . $response->getBody());
        }

        $sentMessage = new SentMessage($message, $envelope ?? Envelope::create($message));

        return $sentMessage;
    }

    /**
     * {@inheritdoc}
     */
    protected function doSend(SentMessage $message): void
    {
        // This method is required by AbstractTransport but we're overriding send() directly
        // so this method won't be called
    }

    /**
     * Get the string representation of the transport.
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'zeptomail';
    }

    /**
     * Get the HTTP payload for sending the ZeptoMail message.
     *
     * @param  \Symfony\Component\Mime\Email  $email
     * @return array
     */
    protected function getPayload(Email $email)
    {
        $payload = [
            'from' => $this->formatAddress($email->getFrom()[0]),
            'to' => $this->formatAddresses($email->getTo()),
            'subject' => $email->getSubject(),
        ];

        // Add CC recipients if present
        if ($email->getCc() && count($email->getCc()) > 0) {
            $payload['cc'] = $this->formatAddresses($email->getCc());
        }

        // Add BCC recipients if present
        if ($email->getBcc() && count($email->getBcc()) > 0) {
            $payload['bcc'] = $this->formatAddresses($email->getBcc());
        }

        // Add reply-to if present
        if ($email->getReplyTo() && count($email->getReplyTo()) > 0) {
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
        $attachments = [];
        foreach ($email->getAttachments() as $attachment) {
            $attachments[] = [
                'name' => $attachment->getFilename(),
                'content' => base64_encode($attachment->getBody()),
                'mime_type' => $attachment->getMediaType() . '/' . $attachment->getMediaSubtype(),
            ];
        }

        if (!empty($attachments)) {
            $payload['attachments'] = $attachments;
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
            'name' => $address->getName() ?: '',
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
        $result = [];

        foreach ($addresses as $address) {
            $result[] = [
                'email_address' => [
                    'address' => $address->getAddress(),
                    'name' => $address->getName() ?: '',
                ],
            ];
        }

        return $result;
    }
}
