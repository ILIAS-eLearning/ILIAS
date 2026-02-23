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

class PropertiesFactoryImpl implements PropertiesFactory
{
    public function get(
        ID $id,
        string $title,
        ?URI $link_to_subitem,
        bool $open_link_in_new_viewport,
        string $presentable_subitem_type
    ): Properties {
        return new PropertiesImpl(
            $id,
            $title,
            $link_to_subitem,
            $open_link_in_new_viewport,
            $presentable_subitem_type
        );
    }

    public function getID(
        string $id,
        string $type
    ): ID {
        return new IDImpl($id, $type);
    }
}
