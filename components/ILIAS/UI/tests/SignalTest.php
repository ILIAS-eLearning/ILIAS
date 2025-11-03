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

namespace ILIAS\UI;

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Implementation\Component\Signal;

class SignalTest extends TestCase
{
    public function testSignalProperties(): void
    {
        $id = 'some_id';
        $signal = new Signal($id);
        $this->assertEquals($id, $signal->getId());
        $this->assertEquals([], $signal->getOptions());
    }

    public function testSignalOptions(): void
    {
        $signal = new Signal('sig');
        $this->assertEquals(['o1' => 3], $signal->withOption('o1', 3)->getOptions());
        $signal->addOption('o2', 4);
        $this->assertEquals(['o2' => 4], $signal->getOptions());
    }

}
