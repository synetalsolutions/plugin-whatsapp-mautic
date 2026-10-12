<?php

declare(strict_types=1);

namespace MauticPlugin\MauticWhatsAppBundle\Service;

use MauticPlugin\MauticWhatsAppBundle\Entity\WhatsAppTemplate;

/**
 * Turns a Mautic template, written with {contactfield=firstname} tokens, into the
 * component payload Meta expects, with {{1}} placeholders and review examples.
 */
final class TemplatePayloadFactory
{
    /**
     * @return array{name: string, language: string, category: string, parameter_format: string, components: list<array<string, mixed>>, tokens: list<string>}
     */
    public function build(WhatsAppTemplate $template): array
    {
        $tokens     = [];
        $components = [];

        $header = trim((string) $template->getHeaderText());
        if ('' !== $header) {
            $converted = $this->convertTokens($header, $tokens);
            $component = [
                'type'   => 'HEADER',
                'format' => 'TEXT',
                'text'   => $converted['text'],
            ];
            if ($converted['added'] > 0) {
                $component['example'] = [
                    'header_text' => $this->examplesFor($template, $converted['added'], count($tokens) - $converted['added']),
                ];
            }
            $components[] = $component;
        }

        $body      = $this->convertTokens((string) $template->getBody(), $tokens);
        $component = [
            'type' => 'BODY',
            'text' => $body['text'],
        ];
        if ($body['added'] > 0) {
            $component['example'] = [
                'body_text' => [
                    $this->examplesFor($template, $body['added'], count($tokens) - $body['added']),
                ],
            ];
        }
        $components[] = $component;

        $footer = trim((string) $template->getFooter());
        if ('' !== $footer) {
            $components[] = [
                'type' => 'FOOTER',
                'text' => $footer,
            ];
        }

        $buttonText = trim((string) $template->getButtonText());
        $buttonUrl  = trim((string) $template->getButtonUrl());
        if ('' !== $buttonText && '' !== $buttonUrl) {
            $components[] = [
                'type'    => 'BUTTONS',
                'buttons' => [[
                    'type' => 'URL',
                    'text' => $buttonText,
                    'url'  => $buttonUrl,
                ]],
            ];
        }

        return [
            'name'             => (string) $template->getTemplateName(),
            'language'         => $template->getLanguage(),
            'category'         => $template->getCategory(),
            'parameter_format' => 'POSITIONAL',
            'components'       => $components,
            'tokens'           => $tokens,
        ];
    }

    /**
     * @param list<string> $parameters values already resolved for this recipient, in token order
     *
     * @return array<string, mixed>
     */
    public function sendPayload(WhatsAppTemplate $template, string $to, array $parameters): array
    {
        $built      = $this->build($template);
        $components = [];
        $offset     = 0;

        foreach ($built['components'] as $component) {
            if ('HEADER' === $component['type'] && isset($component['example']['header_text'])) {
                $count        = count($component['example']['header_text']);
                $components[] = [
                    'type'       => 'header',
                    'parameters' => $this->textParameters(array_slice($parameters, $offset, $count)),
                ];
                $offset += $count;
            }
            if ('BODY' === $component['type'] && isset($component['example']['body_text'][0])) {
                $count        = count($component['example']['body_text'][0]);
                $components[] = [
                    'type'       => 'body',
                    'parameters' => $this->textParameters(array_slice($parameters, $offset, $count)),
                ];
                $offset += $count;
            }
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $to,
            'type'              => 'template',
            'template'          => [
                'name'     => $template->getTemplateName(),
                'language' => ['code' => $template->getLanguage()],
            ],
        ];

        if ([] !== $components) {
            $payload['template']['components'] = $components;
        }

        return $payload;
    }

    /**
     * @param list<string> $tokens
     *
     * @return array{text: string, added: int}
     */
    private function convertTokens(string $text, array &$tokens): array
    {
        $added = 0;
        $text  = preg_replace_callback(
            '/\{([^{}]+)\}/',
            static function (array $match) use (&$tokens, &$added): string {
                $tokens[] = $match[1];
                ++$added;

                return '{{'.count($tokens).'}}';
            },
            $text
        ) ?? $text;

        return ['text' => $text, 'added' => $added];
    }

    /**
     * @return list<string>
     */
    private function examplesFor(WhatsAppTemplate $template, int $count, int $offset): array
    {
        $lines = preg_split('/\r\n|\r|\n/', (string) $template->getExamples()) ?: [];
        $lines = array_values(array_map('trim', $lines));
        $out   = [];
        for ($i = 0; $i < $count; ++$i) {
            $out[] = '' !== ($lines[$offset + $i] ?? '') ? $lines[$offset + $i] : 'sample';
        }

        return $out;
    }

    /**
     * @param list<string> $values
     *
     * @return list<array{type: string, text: string}>
     */
    private function textParameters(array $values): array
    {
        return array_map(
            static fn (string $value): array => ['type' => 'text', 'text' => $value],
            $values
        );
    }
}
