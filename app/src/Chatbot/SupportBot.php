<?php

declare(strict_types=1);

namespace Chatbot;

class SupportBot
{
    public static function ask(string $question, array $history = []): array
    {
        $question = trim($question);
        if ($question === '') {
            return ['ok' => false, 'msg' => 'Please ask a question.'];
        }

        $context = self::buildContext();
        $answer = self::askOpenAI($question, $history, $context);

        if ($answer === null || $answer === '') {
            $answer = self::fallbackAnswer($question, $context);
        }

        return [
            'ok' => true,
            'answer' => $answer,
            'handoff' => self::needsHumanHandoff($question),
        ];
    }

    private static function askOpenAI(string $question, array $history, string $context): ?string
    {
        $apiKey = (string) env('OPENAI_API_KEY', '');
        if ($apiKey === '') {
            return null;
        }

        $model = (string) env('OPENAI_MODEL', 'gpt-4.1-mini');
        $historyText = '';
        foreach (array_slice($history, -6) as $msg) {
            $role = ($msg['role'] ?? '') === 'assistant' ? 'Assistant' : 'Visitor';
            $content = trim((string)($msg['content'] ?? ''));
            if ($content !== '') {
                $historyText .= "{$role}: {$content}\n";
            }
        }

        $systemPrompt = "You are the website support assistant for a print business.\n"
            . "Rules:\n"
            . "1) Answer using ONLY the provided knowledge context.\n"
            . "2) If information is missing, say that clearly and offer human support.\n"
            . "3) Keep answers concise and action-oriented.\n"
            . "4) Never invent prices, timelines, policies, or contact details.\n\n"
            . "Knowledge context:\n{$context}";

        $payload = [
            'model' => $model,
            'input' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => "Conversation so far:\n{$historyText}\nVisitor question: {$question}"],
            ],
            'max_output_tokens' => 280,
        ];

        $ch = curl_init('https://api.openai.com/v1/responses');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT => 18,
        ]);

        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code < 200 || $code >= 300 || !is_string($raw) || $raw === '') {
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return null;
        }

        $text = trim((string)($data['output_text'] ?? ''));
        return $text !== '' ? $text : null;
    }

    private static function buildContext(): string
    {
        $fromDb = trim((string) \Database::setting('chatbot_knowledge', ''));
        $fromFile = '';
        $file = APP_PATH . '/data/chatbot_knowledge.md';
        if (is_file($file)) {
            $fromFile = trim((string)file_get_contents($file));
        }

        $biz = [
            'Business Name: ' . (string) \Database::setting('biz_name', 'RCS Graphic'),
            'Phone: ' . (string) \Database::setting('biz_phone', ''),
            'WhatsApp: ' . (string) \Database::setting('biz_whatsapp', ''),
            'Email: ' . (string) \Database::setting('biz_email', ''),
            'Address: ' . (string) \Database::setting('biz_address', ''),
            'GST %: ' . (string) \Database::setting('gst_percent', ''),
        ];

        return trim(implode("\n", array_filter([
            implode("\n", $biz),
            $fromDb,
            $fromFile,
        ])));
    }

    private static function fallbackAnswer(string $question, string $context): string
    {
        $q = mb_strtolower($question);

        if (str_contains($q, 'price') || str_contains($q, 'cost') || str_contains($q, 'pricing')) {
            return 'You can check live pricing on each product page by selecting quantity and options. If you share your exact requirement, our team can confirm final pricing on WhatsApp.';
        }
        if (str_contains($q, 'delivery') || str_contains($q, 'shipping')) {
            return 'Shipping is calculated during checkout based on your order settings. Share your location and requirement for exact delivery timeline.';
        }
        if (str_contains($q, 'whatsapp') || str_contains($q, 'call') || str_contains($q, 'contact')) {
            $phone = (string) \Database::setting('biz_phone', '');
            $wa = (string) \Database::setting('biz_whatsapp', '');
            return "You can contact us directly. Phone: {$phone}. WhatsApp: {$wa}.";
        }

        if ($context === '') {
            return 'I can help with products, pricing, checkout, and order support. For detailed answers, please ask a specific question or connect with our team on WhatsApp.';
        }

        return 'I can help with product details, pricing flow, shipping, and checkout support. If you share your exact requirement, I will guide you step by step.';
    }

    private static function needsHumanHandoff(string $question): bool
    {
        $q = mb_strtolower($question);
        foreach (['urgent', 'complaint', 'refund', 'custom design', 'bulk order', 'issue', 'problem'] as $w) {
            if (str_contains($q, $w)) {
                return true;
            }
        }
        return false;
    }
}
