<?php

namespace App\Models;

use DOMDocument;
use App\Utilities\OEmbed;
use App\ValueObjects\Tag;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Orbit\Concerns\Orbital;
use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Spatie\LaravelMarkdown\MarkdownRenderer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Article extends Model implements Feedable
{
    use HasFactory;
    use Orbital;

    public $timestamps = false;

    public static function schema(Blueprint $table): void
    {
        $table->string('type')->default('article');
        $table->string('title');
        $table->string('alternate_title')->nullable();
        $table->string('slug');
        $table->date('published_at')->nullable();
        $table->date('updated_at')->nullable();
        $table->json('tags');
        $table->string('strapline')->nullable();
        $table->string('synopsis')->nullable();
        $table->string('canonical')->nullable();
        $table->string('next_article')->nullable();
        $table->string('previous_article')->nullable();
        $table->string('opengraph_image')->nullable();
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime:Y-m-d',
            'updated_at' => 'datetime:Y-m-d',
            'tags' => 'array',
        ];
    }

    public function getKeyName()
    {
        return 'slug';
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getUrl(): string
    {
        return url("/{$this->slug}");
    }

    public function render(): string
    {
        $markdown = $this->content;
        // $markdown = str_replace('<?php', '\<?php', $markdown);
        $markdown = $this->wrapCodeBlocksWithVerbatim($markdown);
        $markdown = app(MarkdownRenderer::class)->toHtml($markdown);
        $markdown = Blade::render($markdown, [
            'article' => $this,
        ]);
        $markdown = str_replace("<p></p>", '', $markdown);
        $markdown = $this->augmentListsStartingWithEmoji($markdown);
        $markdown = $this->augmentListsStartingWithDateTime($markdown);
        $markdown = $this->processImgTags($markdown);
        $markdown = $this->processHeadingsWithId($markdown);
        $markdown = $this->removeTrailingLineFromCodeBlocks($markdown);
        $markdown = $this->addCopyToClipboardButtonToCodeBlocks($markdown);;
        $markdown = OEmbed::parse($markdown);

        return $markdown;
    }

    /** @return Tag[] */
    public function getTags(): array
    {
        return array_map(fn ($tag) => new Tag($tag), array_filter($this->tags ?? []));
    }

    public function getExcerpt(): string
    {
        if (!empty($this->synopsis)) {
            return $this->synopsis;
        }

        $dom = new DOMDocument();
        $html = mb_convert_encoding($this->render(), 'HTML-ENTITIES', 'UTF-8');
        @$dom->loadHTML('<!doctype html>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_SCHEMA_CREATE);
        $paragraphs = $dom->getElementsByTagName('p');

        if ($paragraphs->length > 0) {
            return $dom->saveHTML($paragraphs->item(0));
        }

        return '';
    }

    protected function wrapCodeBlocksWithVerbatim(string $markdown): string
    {
        // Use regex to find fenced code blocks
        $pattern = '/(```\s*[^`]*```)/s';

        // Callback function to wrap matched code blocks
        $callback = function ($matches) {
            return "@verbatim\n" . $matches[0] . "\n@endverbatim";
        };

        // Replace the code blocks with the wrapped version
        $wrappedMarkdown = preg_replace_callback($pattern, $callback, $markdown);

        return $wrappedMarkdown;
    }

    protected function augmentListsStartingWithEmoji(string $html): string
    {
        // Regular expression to match <li> tags and capture contents
        $liPattern = '/<li>(.*?)<\/li>/s';

        // Callback function to process each <li> tag
        $callback = function ($matches) {
            $content = $matches[1];

            if (preg_match('/^(\p{So}|\p{Cn}){1,}/u', $content, $emojiMatch)) {
                $emoji = $emojiMatch[0];
                $restOfText = substr($content, strlen($emoji));

                return "<li class=\"flex gap-x-4\"><span class=\"emoji\">{$emoji}</span><span>{$restOfText}</span></li>";
            } else {
                return $matches[0];
            }
        };

        // Use preg_replace_callback to apply the callback to each <li> tag
        return preg_replace_callback($liPattern, $callback, $html);
    }

    protected function augmentListsStartingWithDateTime(string $html): string
    {
        // Regular expression to match <li> tags and capture contents
        $liPattern = '/<li>(.*?)<\/li>/s';

        // Callback function to process each <li> tag
        $callback = function ($matches) {
            $content = $matches[1];

            // starts with date, time, or datetime
            if (preg_match('/^(\d{4}-\d{2}-\d{2}|\d{2}:\d{2}|\d{4}-\d{2}-\d{2} \d{2}:\d{2})/u', $content, $datetimeMatch)) {
                $datetime = $datetimeMatch[0];
                $restOfText = substr($content, strlen($datetime));

                if (str_starts_with($restOfText, ' : ') || str_starts_with($restOfText, ' - ')) {
                    $restOfText = substr($restOfText, 3);
                }

                return "<li class=\"timeline\"><span class=\"timeline-date\">{$datetime}</span><span class=\"timeline-content\">{$restOfText}</span></li>";
            } else {
                return $matches[0];
            }
        };

        // Use preg_replace_callback to apply the callback to each <li> tag
        return preg_replace_callback($liPattern, $callback, $html);
    }

    protected function processImgTags(string $html): string
    {
        $imgPattern = '/<img[^>]*>/i';

        $callback = function ($matches) {
            $imgTag = $matches[0];

            if (preg_match('/<(picture|figure)[^>]*>.*' . preg_quote($imgTag, '/') . '.*<\/\1>/is', $matches[0])) {
                return $imgTag;
            }

            if (preg_match('/data-nofigure/i', $imgTag)) {
                return $imgTag;
            }

            preg_match('/alt="([^"]*)"/i', $imgTag, $altMatches);
            $altText = $altMatches[1] ?? '';

            $figureTag = '<figure class="' . random_rotation() . ' will-change-transform flex flex-col items-center"><picture>' . $imgTag . '</picture>';
            if ($altText !== '') {
                $arrow = svg(Arr::random(['lines.turny-sputter', 'lines.turny-solid', 'lines.turny-fat', 'lines.turny-zaggy', 'lines.turny-dashed']));

                $arrowBefore = '';
                $arrowAfter = '';

                if (random_int(0, 1) === 1) {
                    $arrowBefore = '<span class="flex-none w-4 h-4 -mt-4 text-gray-400 ' . random_rotation() . '">' . $arrow . '</span>';
                } else {
                    $arrowAfter = '<span class="scale-x-[-1] -mt-4 h-4 w-4 flex-none text-gray-400 ' . random_rotation() . '">' . $arrow . '</span>';
                }

                $figureTag .= '<figcaption class="flex mt-4 text-sm leading-6 text-gray-500 gap-x-2" aria-hidden="true">' . $arrowBefore . htmlspecialchars($altText) . $arrowAfter . '</figcaption>';
            }
            $figureTag .= '</figure>';

            return $figureTag;
        };

        // Use preg_replace_callback to apply the callback to each <img> tag
        $html = preg_replace_callback($imgPattern, $callback, $html);

        return $html;
    }

    protected function processHeadingsWithId(string $html): string
    {
        $headingPattern = '/<(h[1-6])\s+([^>]*id="([^"]+)"[^>]*)>(.*?)<\/\1>/is';

        $callback = function ($matches) {
            $headingTag = $matches[1];
            $attributes = $matches[2];
            $id = $matches[3];
            $content = $matches[4];
            $level = max(1, substr($headingTag, 1) - 1);
            $offset = match($level) {
                1 => '-ml-[2ch]',
                2 => '-ml-[4ch]',
                3 => '-ml-[6ch]',
                4 => '-ml-[7ch]',
                5 => '-ml-[8ch]',
            };

            $newHeadingClasses = 'group -ml-8 pl-8';

            if (preg_match('/class="([^"]*)"/i', $attributes, $classMatches)) {
                $existingClasses = $classMatches[1];
                $newClasses = $existingClasses . ' ' . $newHeadingClasses;
                $attributes = preg_replace('/class="[^"]*"/i', 'class="' . htmlspecialchars($newClasses) . '"', $attributes);
            } else {
                $attributes .= ' class="' . htmlspecialchars($newHeadingClasses) . '"';
            }

            $anchorLink = '<a href="#' . htmlspecialchars($id) . '" class="absolute text-orange-500 no-underline ' . $offset . ' invisible group-hover:visible" aria-hidden="true">'
                . str_repeat('#', $level)
                . '</a>';

            return "<$headingTag $attributes>$anchorLink$content</$headingTag>";
        };

        $html = preg_replace_callback($headingPattern, $callback, $html);

        return $html;
    }

    protected function removeTrailingLineFromCodeBlocks(string $html): string
    {
        return str_replace('<span class="line"></span></code></pre>', '</code></pre>', $html);
    }

    protected function addCopyToClipboardButtonToCodeBlocks(string $html): string
    {
        $codeBlockPattern = '/<pre(.*?)><code(.*?)>(.*?)<\/code><\/pre>/s';

        $callback = function ($matches) {
            $codeBlock = $matches[3];

            $button = '<button class="absolute flex items-center justify-center px-2 py-1 text-xs font-semibold tracking-wide text-gray-400 uppercase bg-gray-700 top-2 right-2 rounded-xl size-8 hover:bg-gray-600" aria-label="Copy code to clipboard" title="Copy code to clipboard" x-on:click="$clipboard($root.textContent.trim())">' . svg('clipboard') . '</button>';

            return "<div class=\"relative\" x-data><pre{$matches[1]}><code{$matches[2]}>{$codeBlock}</code></pre>{$button}</div>";
        };

        return preg_replace_callback($codeBlockPattern, $callback, $html);
    }

    public function hasPreviousArticle(): bool
    {
        return !empty($this->previous_article);
    }

    public function getPreviousArticle(): ?Article
    {
        if (!$this->hasPreviousArticle()) {
            return null;
        }

        return Article::where('slug', $this->previous_article)->first();
    }

    public function hasNextArticle(): bool
    {
        return !empty($this->next_article);
    }

    public function getNextArticle(): ?Article
    {
        if (!$this->hasNextArticle()) {
            return null;
        }

        return Article::where('slug', $this->next_article)->first();
    }

    public function getAlternateTitle(): string
    {
        return $this->alternate_title ?? $this->title;
    }

    public function toFeedItem(): FeedItem
    {
        return FeedItem::create()
            ->id($this->slug)
            ->title($this->title)
            ->summary($this->synopsis ?? '')
            ->updated($this->updated_at ?? $this->published_at)
            ->link($this->getUrl())
            ->authorName('Liam Hammett')
            ->authorEmail('liam@liamhammett.com');
    }

    public static function getFeedItems()
    {
        return Article::query()->published()->orderByDesc('published_at')->get();
    }

    public function getOpengraphImageUrl(): string
    {
        if (!empty( $this->opengraph_image)) {
            if (Str::isUrl($this->opengraph_image)) {
                return $this->opengraph_image;
            }

            return url($this->opengraph_image);
        }

        return $this->getUrl() . '.png';
    }

    public function getOpengraphImageLocalPath(): string
    {
        return "images/opengraph/{$this->slug}.png";
    }

    public function hasOpengraphImage(): bool
    {
        return file_exists(public_path($this->getOpengraphImageLocalPath()));
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('published_at', '<=', now()->timestamp)->whereNotNull('published_at');
    }

    public function scopeUnpublished(Builder $query): void
    {
        $query->where(function (Builder $q) {
            return $q->where('published_at', '>', now()->timestamp)
                ->orWhereNull('published_at');
        });
    }
}
