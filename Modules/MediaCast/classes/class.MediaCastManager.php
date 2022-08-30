<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

namespace ILIAS\MediaCast;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class MediaCastManager
{
    protected \ILIAS\MediaObjects\MediaType\MediaTypeManager $media_types;

    public function __construct()
    {
        global $DIC;

        $this->media_types = $DIC->mediaObjects()
            ->internal()
            ->domain()
            ->mediaType();
    }

    public function getSuffixesForViewMode(string $view_mode) : array
    {
        switch ($view_mode) {
            case \ilObjMediaCast::VIEW_VCAST:
                return $this->media_types->getVideoSuffixes();
                break;
            case \ilObjMediaCast::VIEW_IMG_GALLERY:
                return $this->media_types->getImageSuffixes();
                break;
            case \ilObjMediaCast::VIEW_PODCAST:
                return $this->media_types->getAudioSuffixes();
                break;
        }
        return [];
    }

}