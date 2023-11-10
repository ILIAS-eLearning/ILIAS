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

namespace ILIAS\MetaData\Elements\Markers;

use PHPUnit\Framework\TestCase;

class MarkerFactoryTest extends TestCase
{
    public function testCreateMarker(): void
    {
        $factory = new MarkerFactory();
        $neutral_marker = $factory->marker(Action::NEUTRAL, 'value');
        $create_marker = $factory->marker(Action::CREATE_OR_UPDATE, 'value');

        $this->assertInstanceOf(MarkerInterface::class, $neutral_marker);
        $this->assertSame(Action::NEUTRAL, $neutral_marker->action());
        $this->assertSame('', $neutral_marker->dataValue());

        $this->assertInstanceOf(MarkerInterface::class, $create_marker);
        $this->assertSame(Action::CREATE_OR_UPDATE, $create_marker->action());
        $this->assertSame('value', $create_marker->dataValue());
    }
}
