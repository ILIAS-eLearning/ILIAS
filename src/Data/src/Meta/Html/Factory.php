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

namespace ILIAS\Data\Meta\Html;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Factory
{
    public function userDefined(string $key, string $value): Tag
    {
        return new UserDefined($key, $value);
    }

    /**
     * @param Tag[] $tags
     */
    public function collection(array $tags): TagCollection
    {
        return new TagCollection(...$tags);
    }

    public function nullTag(): Tag
    {
        return new NullTag();
    }
}
