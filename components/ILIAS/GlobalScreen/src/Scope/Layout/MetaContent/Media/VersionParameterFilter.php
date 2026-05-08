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
 * Decides whether the resource version parameter (see
 * {@see AbstractCollection::VERSION_PARAMETER}) must be appended to a given
 * URL/path. Implementations return false to exclude the URL from the version
 * parameter, e.g. for signed URLs whose query string is part of the signature.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
interface VersionParameterFilter
{
    public function shouldAppend(string $content): bool;
}
