<?php

declare(strict_types=1);

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

/**
 * Class MainFactoryTest
 */
class MainFactoryTest extends AbstractFactoryTest
{
    public array $kitchensink_info_settings = [
        "card" => ["context" => false],
        "deck" => ["context" => false],
        "image" => ["context" => false, "rules" => false],
        "legacy" => ["context" => false],
        "viewControl" => ["rules" => false],
        "input" => ["rules" => false],
        "table" => ["rules" => false],
        "layout" => ["rules" => false],
        "menu" => ["rules" => false],
        "symbol" => ["rules" => false]
    ];

    public string $factory_title = 'ILIAS\\UI\\Factory';

    /**
     * @doesNotPerformAssertions
     */
    public function test_proper_namespace(): void
    {
        // Nothing to test here.
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_proper_name(): void
    {
        // Nothing to test here.
    }

    protected function get_regex_factory_namespace(): string
    {
        return "\\\\ILIAS\\\\UI\\\\Component";
    }
}
