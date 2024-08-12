<?php

namespace App\Utilities;

use DOMXPath;
use Exception;
use DOMDocument;
use Embed\Embed;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

final class OEmbed
{
    public static function parse(string $html): string
    {
        $dom = new DOMDocument();

        @$dom->loadHTML(
            '<!DOCTYPE html>' . mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        $xpath = new DOMXPath($dom);
        $anchors = $xpath->query('//p/a[@href]');

        foreach ($anchors as $node) {
            /** @var \DOMElement $node */
            if (! $node->hasAttribute('href')) {
                continue;
            }

            $href = trim($node->getAttribute('href'));

            if (trim($node->textContent) !== $href) {
                continue;
            }

            if (trim($node->textContent) !== trim($node->parentNode->textContent)) {
                continue;
            }

            try {
                $oembed = Cache::remember("oembed_{$href}", now()->addDay(), function () use ($href) {
                    $result = (new Embed)->get($href);

                    return (object) [
                        'oembed' => $result->code ?? null,
                        'html' => (string) $result->getResponse()->getBody(),
                    ];
                });

                $embedCode = $oembed->oembed;

                if (! $embedCode) {
                    $embedCode = Cache::remember('opengraph_' . $href, now()->addDay(), function() use ($oembed) {
                        return static::getOpenGraphBlock($oembed->html);
                    });
                }

                $embedDom = new DOMDocument();
                @$embedDom->loadHTML(
                    mb_convert_encoding("<div class='oembed flex justify-center'>{$embedCode}</div>", 'HTML-ENTITIES', 'UTF-8'),
                    LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
                );

                $node->parentNode->replaceChild(
                    $dom->importNode($embedDom->documentElement, true),
                    $node
                );
            } catch (Exception $e) {
                // If the try clause failed for whatever reason, the original
                // node would never have been replaced, so we'll leave the
                // original text anchor in there to be displayed instead.
            }
        }

        return Str::after($dom->saveHTML(), '<!DOCTYPE html>');
    }

    protected static function getOpenGraphBlock(string $html): string
    {
        $openGraphTags = static::getOpenGraphTags($html);

        return (string) view('components.opengraph-link', [
            'url' => $openGraphTags['og:url'] ?? null,
            'imageUrl' => $openGraphTags['og:image'] ?? null,
            'siteName' => $openGraphTags['og:site_name'] ?? null,
            'title' => $openGraphTags['og:title'] ?? null,
            'description' => $openGraphTags['og:description'] ?? null,
            'domainName' => parse_url($openGraphTags['og:url'], PHP_URL_HOST) ?? null,
        ]);
    }

    protected static function getOpenGraphTags(string $html): array
    {
        $doc = new DomDocument();
        @$doc->loadHTML($html);
        $xpath = new DOMXPath($doc);
        $metaTags = $xpath->query('//*/meta[starts-with(@property, \'og:\')]');
        $openGraphTags = [];

        foreach ($metaTags as $metaTag) {
            $openGraphTags[$metaTag->getAttribute('property')] = $metaTag->getAttribute('content');
        }

        return $openGraphTags;
    }
}