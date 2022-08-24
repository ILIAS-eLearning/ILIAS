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

require_once('tests/UI/Base.php');

use ILIAS\UI\Implementation\Component\Listing\CharacteristicValue\Factory as CharacteristicValueFactory;

class CharacteristicValueTest extends ILIAS_UI_TestBase
{
    public function test_interfaces(): void
    {
        $f = $this->getCharacteristicValueFactory();

        $this->assertInstanceOf(
            'ILIAS\\UI\\Component\\Listing\\CharacteristicValue\\Factory',
            $f
        );

        $this->assertInstanceOf(
            'ILIAS\\UI\\Component\\Listing\\CharacteristicValue\\Text',
            $f->text($this->getTextItemsMock())
        );
    }

    protected function getCharacteristicValueFactory(): CharacteristicValueFactory
    {
        return new CharacteristicValueFactory();
    }

    protected function getTextItemsMock(): array
    {
        return [
            'label1' => 'item1',
            'label2' => 'item2',
            'label3' => 'item3'
        ];
    }

    protected function getInvalidTextItemsMocks(): array
    {
        return [
            ['' => 'item'],
            ['label' => ''],
            []
        ];
    }
}
