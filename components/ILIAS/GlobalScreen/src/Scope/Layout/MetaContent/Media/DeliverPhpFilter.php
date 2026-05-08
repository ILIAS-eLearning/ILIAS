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

/**
 * Excludes URLs delivered by `deliver.php` from the resource version parameter.
 * Such URLs already encode the resource version in their signed path segment,
 * appending another query parameter would invalidate the signature.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class DeliverPhpFilter implements VersionParameterFilter
{
    private const NEEDLE = '/deliver.php/';

    public function shouldAppend(string $content): bool
    {
        return !str_contains($content, self::NEEDLE);
    }
}
