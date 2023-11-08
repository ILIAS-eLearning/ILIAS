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

namespace ILIAS\Tests\UI\Component\Modal;

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Implementation\Component\Modal\LightboxCardPage;
use ILIAS\UI\Component\Card\Card;

class LightboxCardPageTest extends TestCase
{
    public function testGetTitlesReturnsCardTitle(): void
    {
        $title = 'Foobar';
        $card = $this->getMockBuilder(Card::class)->disableOriginalConstructor()->getMock();
        $card->expects(self::once())->method('getTitle')->willReturn($title);
        $this->assertEquals($title, (new LightboxCardPage($card))->getTitle());
    }

    public function testGetComponentReturnsCard(): void
    {
        $card = $this->getMockBuilder(Card::class)->disableOriginalConstructor()->getMock();
        $this->assertEquals($card, (new LightboxCardPage($card))->getComponent());
    }
}
