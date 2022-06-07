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

/**
 * Tests on factory implementation for Slates
 *
 * @author Nils Haagen <nhaagen@concepts-and-training.de>
 */
class SlateFactoryTest extends AbstractFactoryTest
{
    public array $kitchensink_info_settings = [
        "legacy" => ["context" => false],
        "combined" => ["rules" => false]
    ];

    public string $factory_title = 'ILIAS\\UI\\Component\\MainControls\\Slate\\Factory';
}
