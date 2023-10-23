<?php

declare(strict_types=1);

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

namespace ILIAS\MediaCast;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class MediaCastManager
{
    protected \ilSetting $settings;
    protected \ilObjMediaCast $media_cast;
    protected \ILIAS\MediaObjects\MediaType\MediaTypeManager $media_types;

    public function __construct(
        \ilObjMediaCast $media_cast
    ) {
        global $DIC;

        $this->media_cast = $media_cast;
        $this->settings = $DIC->settings();
        $this->media_types = $DIC->mediaObjects()
            ->internal()
            ->domain()
            ->mediaType();
    }

    public function getSuffixesForViewMode(string $view_mode): array
    {
        switch ($view_mode) {
            case \ilObjMediaCast::VIEW_VCAST:
                return iterator_to_array($this->media_types->getAllowedVideoSuffixes());
                break;
            case \ilObjMediaCast::VIEW_IMG_GALLERY:
                return iterator_to_array($this->media_types->getAllowedImageSuffixes());
                break;
            case \ilObjMediaCast::VIEW_PODCAST:
                return iterator_to_array($this->media_types->getAllowedAudioSuffixes());
                break;
        }
        return [];
    }

    public function commentsActive(): bool
    {
        if ($this->settings->get("disable_comments")) {
            return false;
        }
        if (!$this->media_cast->getComments()) {
            return false;
        }
        return true;
    }
}
