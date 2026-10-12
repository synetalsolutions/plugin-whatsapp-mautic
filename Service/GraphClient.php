<?php

declare(strict_types=1);

namespace MauticPlugin\MauticWhatsAppBundle\Service;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use MauticPlugin\MauticWhatsAppBundle\Entity\WhatsAppTemplate;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GraphClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CoreParametersHelper $coreParametersHelper,
        private readonly TemplatePayloadFactory $payloadFactory,
    ) {
    }

    public function isConfigured(): bool
    {
        return '' !== $this->token() && '' !== $this->wabaId() && '' !== $this->phoneNumberId();
    }

    /**
     * @return array<string, mixed>
     */
    public function submitTemplate(WhatsAppTemplate $template): array
    {
        $this->assertConfigured();
        $payload = $this->payloadFactory->build($template);
        unset($payload['tokens']);

        if (null !== $template->getMetaId() && '' !== $template->getMetaId()) {
            return $this->request('POST', '/'.$template->getMetaId(), [
                'components' => $payload['components'],
            ]);
        }

        return $this->request('POST', '/'.$this->wabaId().'/message_templates', $payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchTemplate(string $metaId): array
    {
        $this->assertConfigured();

        return $this->request('GET', '/'.$metaId.'?fields=status,rejected_reason,name');
    }

    /**
     * @param list<string> $parameters
     *
     * @return array<string, mixed>
     */
    public function sendTemplate(WhatsAppTemplate $template, string $to, array $parameters): array
    {
        $this->assertConfigured();

        return $this->request(
            'POST',
            '/'.$this->phoneNumberId().'/messages',
            $this->payloadFactory->sendPayload($template, $to, $parameters)
        );
    }

    /**
     * @param array<string, mixed>|null $body
     *
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, ?array $body = null): array
    {
        $options = [
            'headers' => [
                'Authorization' => 'Bearer '.$this->token(),
                'Content-Type'  => 'application/json',
            ],
        ];
        if (null !== $body) {
            $options['json'] = $body;
        }

        $response = $this->httpClient->request($method, $this->baseUrl().$path, $options);
        $data     = $response->toArray(false);
        if ($response->getStatusCode() >= 400) {
            $message = $data['error']['error_user_msg'] ?? $data['error']['message'] ?? 'WhatsApp API request failed.';

            throw new \RuntimeException((string) $message);
        }

        return $data;
    }

    private function assertConfigured(): void
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('WhatsApp is not configured. Set the access token, WABA ID, and phone number ID in Configuration.');
        }
    }

    private function baseUrl(): string
    {
        $version = trim((string) $this->coreParametersHelper->get('whatsapp_api_version', 'v23.0'));
        $version = '' === $version ? 'v23.0' : $version;

        return 'https://graph.facebook.com/'.ltrim($version, '/');
    }

    private function token(): string
    {
        return trim((string) $this->coreParametersHelper->get('whatsapp_access_token', ''));
    }

    private function wabaId(): string
    {
        return trim((string) $this->coreParametersHelper->get('whatsapp_waba_id', ''));
    }

    private function phoneNumberId(): string
    {
        return trim((string) $this->coreParametersHelper->get('whatsapp_phone_number_id', ''));
    }
}
