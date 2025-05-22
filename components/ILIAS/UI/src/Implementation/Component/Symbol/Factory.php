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

namespace ILIAS\UI\Implementation\Component\Symbol;

use ILIAS\UI\Component;

class Factory implements Component\Symbol\Factory
{
    protected Icon\Factory $icon_factory;
    protected Glyph\Factory $glyph_factory;
    protected Avatar\Factory $avatar_factory;

    public function __construct(
        Icon\Factory $icon_factory,
        Glyph\Factory $glyph_factory,
        Avatar\Factory $avatar_factory
    ) {
        $this->icon_factory = $icon_factory;
        $this->glyph_factory = $glyph_factory;
        $this->avatar_factory = $avatar_factory;
    }

    public function icon(): Icon\Factory
    {
        return $this->icon_factory;
    }

    public function glyph(): Glyph\Factory
    {
        return $this->glyph_factory;
    }

    public function avatar(): Avatar\Factory
    {
        return $this->avatar_factory;
    }
}
