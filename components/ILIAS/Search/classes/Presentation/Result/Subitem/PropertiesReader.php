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

namespace ILIAS\Search\Presentation\Result\Subitem;

use ILIAS\Data\URI;
use ILIAS\DI\Container;
use Generator;

interface PropertiesReader
{
    /**
     * Type of the parent object.
     *
     * Should do nothing but return a string, is called during setup.
     */
    public static function type(): string;

    public function init(Container $dic): void;

    /**
     * Order of the output should respect the order of the
     * subitem_ids.
     * Subitems that should not be shown in the search
     * results should not be included in the output
     * (read access on the parent object is already
     * checked).
     *
     * @return Properties[]
     */
    public function getSubitemProperties(
        PropertiesFactory $factory,
        int $parent_ref_id,
        ID ...$subitem_ids
    ): Generator;
}
