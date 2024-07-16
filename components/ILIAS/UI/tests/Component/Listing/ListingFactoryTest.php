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

require_once 'components/ILIAS/UI/tests/AbstractFactoryTestCase.php';

class ListingFactoryTest extends AbstractFactoryTestCase
{
    public static array $kitchensink_info_settings = [
        "ordered" => [
                "context" => false,
                "rules" => false
        ],
        "unordered" => [
                "context" => false,
                "rules" => false
        ],
        "descriptive" => [
                "context" => false,
                "rules" => false
        ],
        "workflow" => [
                "context" => false,
                "rules" => false
        ],
        "characteristicValue" => [
            "context" => false,
            "rules" => false
        ],
        "entity" => [
            "context" => false,
            "rules" => false
        ],
        "property" => [
            "rules" => false
        ],

    ];


    public static string $factory_title = 'ILIAS\\UI\\Component\\Listing\\Factory';
}
