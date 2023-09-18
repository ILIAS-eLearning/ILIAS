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

namespace ILIAS\UI\Implementation\Component\Launcher;

use ILIAS\Data\Link;
use ILIAS\UI\Component\Launcher;
use ILIAS\UI\Component\Modal;

class Factory implements Launcher\Factory
{
    protected Modal\Factory $modal_factory;

    public function __construct(Modal\Factory $modal_factory)
    {
        $this->modal_factory = $modal_factory;
    }

    /**
     * @inheritdoc
     */
    public function inline(Link $target): Launcher\Inline
    {
        return new Inline($this->modal_factory, $target);
    }
}
