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

namespace ILIAS\Mail\Mime\Presentation\Asset;

/**
 * Designates one of a set of images as the mail's logo by file name convention,
 * falling back to the first image.
 */
final class LogoByFileName
{
    /** @var list<string> */
    private const array KNOWN_LOGO_NAMES = ['logo', 'headericon'];

    public function markIn(InlineImages $images): InlineImages
    {
        if ($images->hasLogo() || $images->isEmpty()) {
            return $images;
        }

        foreach ($images as $image) {
            if (\in_array($image->stem(), self::KNOWN_LOGO_NAMES, true)) {
                return $images->withLogo($image);
            }
        }

        $first = $images->first();

        return $first === null ? $images : $images->withLogo($first);
    }
}
