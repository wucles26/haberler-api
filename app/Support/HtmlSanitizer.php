<?php

namespace App\Support;

class HtmlSanitizer
{
    /**
     * @var list<string>
     */
    private const ALLOWED_TAGS = [
        'p',
        'br',
        'strong',
        'b',
        'em',
        'i',
        'u',
        'ul',
        'ol',
        'li',
        'a',
        'h2',
        'h3',
        'blockquote',
    ];

    public function sanitize(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $allowed = '<'.implode('><', self::ALLOWED_TAGS).'>';
        $cleaned = strip_tags($html, $allowed);

        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"><div>'.$cleaned.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $wrapper = $document->getElementsByTagName('div')->item(0);

        if ($wrapper === null) {
            return '';
        }

        foreach (iterator_to_array($wrapper->getElementsByTagName('*')) as $element) {
            if (! $element instanceof \DOMElement) {
                continue;
            }

            if ($element->tagName === 'a') {
                $href = $element->getAttribute('href');

                if ($href === '' || ! preg_match('/^(https?:\/\/|\/|#|mailto:)/i', $href)) {
                    $element->removeAttribute('href');
                } else {
                    $element->setAttribute('href', $href);
                    $element->setAttribute('rel', 'noopener noreferrer');
                }

                foreach (iterator_to_array($element->attributes) as $attribute) {
                    if (! in_array($attribute->name, ['href', 'rel', 'title'], true)) {
                        $element->removeAttribute($attribute->name);
                    }
                }

                continue;
            }

            while ($element->attributes->length > 0) {
                $element->removeAttribute($element->attributes->item(0)->name);
            }
        }

        $result = '';

        foreach ($wrapper->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return $result;
    }
}
