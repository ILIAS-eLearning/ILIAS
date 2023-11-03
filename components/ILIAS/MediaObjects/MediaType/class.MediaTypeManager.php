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

namespace ILIAS\MediaObjects\MediaType;

class MediaTypeManager
{
    protected const TYPE_VIDEO = "video";
    protected const TYPE_AUDIO = "audio";
    protected const TYPE_IMAGE = "image";
    protected const TYPE_OTHER = "other";

    protected const TYPES = [
        "video/vimeo" => [
            "type" => self::TYPE_VIDEO,
            "suffixes" => []
        ],
        "video/youtube" => [
            "type" => self::TYPE_VIDEO,
            "suffixes" => []
        ],
        "video/mp4" => [
            "type" => self::TYPE_VIDEO,
            "suffixes" => ["mp4"]
        ],
        "video/webm" => [
            "type" => self::TYPE_VIDEO,
            "suffixes" => ["webm"]
        ],
        "audio/mpeg" => [
            "type" => self::TYPE_AUDIO,
            "suffixes" => ["mp3"]
        ],
        "image/png" => [
            "type" => self::TYPE_IMAGE,
            "suffixes" => ["png"]
        ],
        "image/jpeg" => [
            "type" => self::TYPE_IMAGE,
            "suffixes" => ["jpg", "jpeg"]
        ],
        "image/gif" => [
            "type" => self::TYPE_IMAGE,
            "suffixes" => ["gif"]
        ],
        "image/webp" => [
            "type" => self::TYPE_IMAGE,
            "suffixes" => ["webp"]
        ],
        "image/svg+xml" => [
            "type" => self::TYPE_IMAGE,
            "suffixes" => ["svg"]
        ],
        "text/html" => [
            "type" => self::TYPE_OTHER,
            "suffixes" => ["html", "htm"]
        ],
        "application/pdf" => [
            "type" => self::TYPE_OTHER,
            "suffixes" => ["pdf"]
        ]
    ];
    protected ?array $mime_blacklist;

    public function __construct(?array $mime_blacklist = null)
    {
        if (is_null($mime_blacklist)) {
            $mime_blacklist = [];
            $mset = new \ilSetting("mobs");
            foreach (explode(",", $mset->get("black_list_file_types")) as $suffix) {
                $mime_blacklist[] = strtolower(trim($suffix));
            }
        }
        $this->mime_blacklist = $mime_blacklist;
    }

    /**
     * This has been introduced for applets long time ago and been available for
     * all mime times for several years.
     */
    public function usesParameterProperty(string $mime): bool
    {
        return false;
    }

    /**
     * Check whether only autostart parameter should be supported (instead
     * of parameters input field)
     *
     * This should be the same behaviour as mp3/flv in page.xsl
     */
    public function usesAutoStartParameterOnly(
        string $location,
        string $mime
    ): bool {
        $lpath = pathinfo($location);
        $ext = $lpath["extension"] ?? "";

        if ($this->isVideo($mime) || $this->isAudio($mime)) {
            return true;
        }
        return false;
    }

    protected function isType(string $mime, string $type): bool
    {
        return in_array($mime, iterator_to_array($this->getMimeTypesOfType($type)), true);
    }

    public function isImage(string $mime): bool
    {
        return $this->isType($mime, self::TYPE_IMAGE);
    }

    public function isAudio(string $mime): bool
    {
        return $this->isType($mime, self::TYPE_AUDIO);
    }

    public function isVideo(string $mime): bool
    {
        return $this->isType($mime, self::TYPE_VIDEO);
    }

    public function usesAltTextProperty(string $mime): bool
    {
        return $this->isImage($mime);
    }

    protected function mergeSuffixes(array ...$arr): array
    {
        $suffixes = [];
        foreach ($arr as $type) {
            foreach ($type as $item) {
                $suffixes = array_merge($suffixes, array_values($item));
            }
        }
        return $suffixes;
    }

    protected function getMimeTypesOfType(string $type): \Iterator
    {
        foreach (self::TYPES as $mime => $def) {
            if ($def["type"] === $type) {
                yield $mime;
            }
        }
    }

    protected function getSuffixesOfType(string $type): \Iterator
    {
        foreach (self::TYPES as $mime => $def) {
            foreach ($def["suffixes"] as $suffix) {
                if ($def["type"] === $type) {
                    yield $suffix;
                }
            }
        }
    }

    protected function getSuffixes(): \Iterator
    {
        foreach (self::TYPES as $mime => $def) {
            foreach ($def["suffixes"] as $suffix) {
                yield $suffix;
            }
        }
    }

    public function getAllowedSuffixes(): \Iterator
    {
        foreach (self::TYPES as $mime => $def) {
            if (!in_array($mime, $this->mime_blacklist, true)) {
                foreach ($def["suffixes"] as $suffix) {
                    yield $suffix;
                }
            }
        }
    }

    protected function getAllowedSuffixesOfType($type): \Iterator
    {
        foreach (self::TYPES as $mime => $def) {
            if ($def["type"] === $type && !in_array($mime, $this->mime_blacklist, true)) {
                foreach ($def["suffixes"] as $suffix) {
                    yield $suffix;
                }
            }
        }
    }

    protected function getAllowedSubset(\Iterator $types): \Iterator
    {
        foreach ($types as $type) {
            if (!in_array($type, $this->mime_blacklist, true)) {
                yield $type;
            }
        }
    }

    public function getMimeTypes(): \Iterator
    {
        foreach (self::TYPES as $mime => $def) {
            yield $mime;
        }
    }

    public function getAllowedMimeTypes(): \Iterator
    {
        return $this->getAllowedSubset($this->getMimeTypes());
    }

    public function getVideoMimeTypes(bool $local_only = false): \Iterator
    {
        foreach ($this->getMimeTypesOfType(self::TYPE_VIDEO) as $mime) {
            if (!$local_only || !in_array($mime, ["video/vimeo", "video/youtube"])) {
                yield $mime;
            }
        }
    }

    public function getAllowedVideoMimeTypes(bool $local_only = false): \Iterator
    {
        return $this->getAllowedSubset($this->getVideoMimeTypes($local_only));
    }

    public function getVideoSuffixes(): \Iterator
    {
        return $this->getSuffixesOfType(self::TYPE_VIDEO);
    }

    public function getAllowedVideoSuffixes(): \Iterator
    {
        return $this->getAllowedSuffixesOfType(self::TYPE_VIDEO);
    }

    public function getAudioMimeTypes(): \Iterator
    {
        return $this->getMimeTypesOfType(self::TYPE_AUDIO);
    }

    public function getAudioSuffixes(): \Iterator
    {
        return $this->getSuffixesOfType(self::TYPE_AUDIO);
    }

    public function getAllowedAudioMimeTypes(): \Iterator
    {
        return $this->getAllowedSubset($this->getAudioMimeTypes());
    }

    public function getAllowedAudioSuffixes(): \Iterator
    {
        return $this->getAllowedSuffixesOfType(self::TYPE_AUDIO);
    }

    public function getImageMimeTypes(): \Iterator
    {
        return $this->getMimeTypesOfType(self::TYPE_IMAGE);
    }

    public function getImageSuffixes(): \Iterator
    {
        return $this->getSuffixesOfType(self::TYPE_IMAGE);
    }

    public function getAllowedImageMimeTypes(): \Iterator
    {
        return $this->getAllowedSubset($this->getImageMimeTypes());
    }

    public function getAllowedImageSuffixes(): \Iterator
    {
        return $this->getAllowedSuffixesOfType(self::TYPE_IMAGE);
    }

    public function getOtherMimeTypes(): \Iterator
    {
        return $this->getMimeTypesOfType(self::TYPE_OTHER);
    }

    public function getOtherSuffixes(): \Iterator
    {
        return $this->getSuffixesOfType(self::TYPE_OTHER);
    }

    public function getAllowedOtherMimeTypes(): \Iterator
    {
        return $this->getAllowedSubset($this->getOtherMimeTypes());
    }

    public function isHtmlAllowed(): bool
    {
        return in_array("text/html", iterator_to_array($this->getAllowedOtherMimeTypes()), true);
    }

}
