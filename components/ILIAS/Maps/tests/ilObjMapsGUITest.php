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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ilObjMapsGUITest extends TestCase
{
    #[DataProvider('mapTypeProvider')]
    public function testNormalizeMapType(?string $configured_type, array $available_types, string $expected): void
    {
        $method = new ReflectionMethod(ilObjMapsGUI::class, 'normalizeMapType');

        $this->assertSame($expected, $method->invoke(null, $configured_type, $available_types));
    }

    public static function mapTypeProvider(): \Iterator
    {
        yield 'supported type is kept' => [
            'openlayers',
            ['openlayers' => 'OpenLayers'],
            'openlayers'
        ];

        yield 'legacy google maps type falls back to first available option' => [
            'googlemaps',
            ['openlayers' => 'OpenLayers'],
            'openlayers'
        ];

        yield 'empty type falls back to first available option' => [
            '',
            ['openlayers' => 'OpenLayers'],
            'openlayers'
        ];

        yield 'missing type falls back to first available option' => [
            null,
            ['openlayers' => 'OpenLayers'],
            'openlayers'
        ];

        yield 'empty options still return openlayers default' => [
            'googlemaps',
            [],
            'openlayers'
        ];
    }
}
