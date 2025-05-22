<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media;

use Iterator;
use ILIAS\Data\URI;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class AbstractCollection
{
    /**
     * @var Js[]|Css[]|InlineCss[]|OnLoadCode[]
     */
    protected array $items = [];

    public function __construct(
        protected string $resource_version,
        protected bool $append_resource_version = false,
        protected bool $strip_queries = true,
        protected bool $allow_external = false,
        protected bool $allow_non_existing = false,
    ) {
    }

    public function clear(): void
    {
        $this->items = [];
    }

    protected function isURI(string $content): bool
    {
        if (realpath($content) !== false) {
            return false;
        }

        try {
            new URI($content);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function isExternalURI(string $content): bool
    {
        if (!$this->isURI($content)) {
            return false;
        }

        try {
            if ((new URI($url))->getHost() !== (new URI(ILIAS_HTTP_PATH))->getHost()) {
                return true;
            }
        } catch (\Throwable) {
            return false;
        }

        return false;
    }

    /**
     * @return Iterator <Css[]|InlineCss[]|Js[]|OnLoadCode[]>
     */
    public function getItems(): Iterator
    {
        foreach ($this->items as $path => $item) {
            yield $path => $this->handleParameters($item);
        }
    }

    private function handleParameters(AbstractMedia $media): AbstractMedia
    {
        if (!$media instanceof AbstractMediaWithPath) {
            return $media;
        }
        if (!$this->append_resource_version && !$this->strip_queries) {
            return $media;
        }
        $content = $media->getContent();
        if ($this->isContentDataUri($content)) {
            return $media;
        }

        $content = $media->getContent();

        $content_array = explode('?', $content);
        if ($this->strip_queries) {
            $content = $content_array[0] ?? $content;
        }
        if ($this->append_resource_version) {
            if ($this->hasContentParameters($content)) {
                $content = rtrim($content, "&") . "&version=" . $this->resource_version;
            } else {
                $content = rtrim($content, "?") . "?version=" . $this->resource_version;
            }
        }

        return $media->withContent($content);
    }

    protected function isContentDataUri(string $content): bool
    {
        // regex pattern matches if a string follows the data uri syntax.
        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/Data_URIs#syntax

        return (bool) preg_match('/^(data:)([a-z\/]*)((;base64)?)(,?)([A-z0-9=\/\+]*)$/', $content);
    }

    protected function hasContentParameters(string $content): bool
    {
        return (str_contains($content, "?"));
    }

    /**
     * @return Js[]|Css[]|InlineCss[]|OnLoadCode[]
     */
    public function getItemsInOrderOfDelivery(): array
    {
        return iterator_to_array($this->getItems());
    }

    /**
     * @param string $path
     * @return string
     */
    protected function stripPath(string $path): string
    {
        if (str_contains($path, '?')) {
            return parse_url($path, PHP_URL_PATH);
        }

        return $path;
    }
}
