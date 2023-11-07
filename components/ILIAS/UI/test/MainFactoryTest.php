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

require_once 'components/ILIAS/UI/test/AbstractFactoryTest.php';

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
        "symbol" => ["rules" => false],
        "entity" => ["context" => true]
    ];

    public string $factory_title = 'ILIAS\\UI\\Factory';

    /**
     * @doesNotPerformAssertions
     */
    public function testProperNamespace(): void
    {
        // Nothing to test here.
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testProperName(): void
    {
        // Nothing to test here.
    }

    protected function getRegexFactoryNamespace(): string
    {
        return "\\\\ILIAS\\\\UI\\\\Component";
    }
}
