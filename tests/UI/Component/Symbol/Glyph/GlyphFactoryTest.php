<?php declare(strict_types=1);

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
 
require_once 'tests/UI/AbstractFactoryTest.php';

class GlyphFactoryTest extends AbstractFactoryTest
{
    public array $kitchensink_info_settings = [
        "collapse" => ["context" => false],
        "expand" => ["context" => false],
        "user" => ["context" => false],
        "mail" => ["context" => false],
        "notification" => ["context" => false],
        "tag" => ["context" => false],
        "note" => ["context" => false],
        "comment" => ["context" => false],
        "sortAscending" => ["context" => false],
        "sortDescending" => ["context" => false],
        "briefcase" => ["context" => false],
        "reset" => ["context" => false],
        "apply" => ["context" => false],
        "close" => ["context" => false],
        "settings" => ["context" => false],
    ];

    public string $factory_title = 'ILIAS\\UI\\Component\\Symbol\\Glyph\\Factory';
}
