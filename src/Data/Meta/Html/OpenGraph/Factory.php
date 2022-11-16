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
 */

declare(strict_types=1);

namespace ILIAS\Data\Meta\Html\OpenGraph;

use ILIAS\Data\URI;
use ILIAS\Data\Meta\Html\OpenGraph\Resource;
use ILIAS\Data\Meta\Html\NullTag;
use ILIAS\Data\Meta\Html\Tag as HTMLTag;
use DateTimeImmutable;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Factory
{
    public function audio(URI $audio_url, string $mime_type): Audio
    {
        return new Audio($audio_url, $mime_type);
    }

    public function image(
        URI $image_url,
        string $mime_type,
        ?string $aria_label = null,
        ?int $width = null,
        ?int $height = null,
    ): Image {
        return new Image($image_url, $mime_type, $aria_label, $width, $height);
    }

    public function video(
        URI $video_url,
        string $mime_type,
        ?int $width = null,
        ?int $height = null,
    ): Video {
        return new Video($video_url, $mime_type, $width, $height);
    }

    /**
     * @param string|null $default_locale defaults to en_US
     * @param string[]    $alternative_locales
     * @param Resource[]  $additional_resources
     */
    public function website(
        URI $canonical_url,
        Image $image,
        string $object_title,
        ?string $website_name = null,
        ?string $description = null,
        ?string $default_locale = null,
        array $alternative_locales = [],
        array $additional_resources = [],
    ): TagCollection {
        return new TagCollection(
            new Text('og:type', 'website'),
            new Text('og:title', $object_title),
            new Link('og:url', $canonical_url),
            $image,
            (null !== $website_name) ? new Text('og:site_title', $website_name) : new NullTag(),
            (null !== $description) ? new Text('og:description', $description) : new NullTag(),
            (null !== $default_locale) ? new Text('og:locale', $default_locale) : new NullTag(),
            $this->getAlternativeLocalesTag($alternative_locales),
            ($this->checkAdditionalResources($additional_resources)) ? new TagCollection(
                ...$additional_resources
            ) : new NullTag(),
        );
    }

    /**
     * @param string[] $locales
     */
    protected function getAlternativeLocalesTag(array $locales): HTMLTag
    {
        if (empty($locales)) {
            return new NullTag();
        }

        $alternative_language_tags = [];
        foreach ($locales as $locale) {
            $alternative_language_tags[] = new Text('og:locale:alternative', $locale);
        }

        return new TagCollection(...$alternative_language_tags);
    }

    protected function checkAdditionalResources(array $resources): bool
    {
        foreach ($resources as $resource) {
            if (!$resource instanceof Resource) {
                throw new \LogicException(
                    sprintf(
                        "Expected array of %s but received %s.",
                        Resource::class,
                        get_class($resource)
                    )
                );
            }
        }

        return true;
    }
}
