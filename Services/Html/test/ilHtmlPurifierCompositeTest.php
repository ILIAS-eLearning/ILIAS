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

use PHPUnit\Framework\TestCase;

/**
 * Class ilHtmlPurifierCompositeTest
 * @author Michael Jansen <mjansen@databay.de>
 */
final class ilHtmlPurifierCompositeTest extends TestCase
{
    private const TO_PURIFY = [
        'phpunit1',
        'phpunit2',
        'phpunit3',
    ];

    private function getFakePurifier(): ilHtmlPurifierInterface
    {
        return new class () implements ilHtmlPurifierInterface {
            public function purify(string $html): string
            {
                return $html . '.';
            }

            public function purifyArray(array $htmlCollection): array
            {
                foreach ($htmlCollection as &$html) {
                    $html .= '.';
                }

                return $htmlCollection;
            }
        };
    }

    public function testPurifierNodesAreCalledIfStringGetsPurified(): void
    {
        $purifier = new ilHtmlPurifierComposite();

        $p1 = $this->getFakePurifier();
        $p2 = clone $p1;
        $p3 = clone $p1;

        $purifier->addPurifier($p1);
        $purifier->addPurifier($p1);
        $purifier->addPurifier($p2);
        $purifier->addPurifier($p3);

        $this->assertSame('phpunit...', $purifier->purify('phpunit'));

        $purifier->removePurifier($p2);

        $this->assertSame('phpunit..', $purifier->purify('phpunit'));
    }

    public function testPurifierNodesAreCalledIfArrayOfStringGetssPurified(): void
    {
        $purifier = new ilHtmlPurifierComposite();

        $p1 = $this->getFakePurifier();
        $p2 = clone $p1;
        $p3 = clone $p1;

        $purifier->addPurifier($p1);
        $purifier->addPurifier($p1);
        $purifier->addPurifier($p2);
        $purifier->addPurifier($p3);

        $this->assertSame(array_map(static function (string $html): string {
            return $html . '...';
        }, self::TO_PURIFY), $purifier->purifyArray(self::TO_PURIFY));

        $purifier->removePurifier($p2);

        $this->assertSame(array_map(static function (string $html): string {
            return $html . '..';
        }, self::TO_PURIFY), $purifier->purifyArray(self::TO_PURIFY));
    }

    /**
     * @return array{integer: int[], float: float[], null: null[], array: never[][], object: \stdClass[], bool: false[], resource: resource[]|false[]}
     */
    public function invalidHtmlDataTypeProvider(): array
    {
        return [
            'integer' => [5],
            'float' => [0.1],
            'null' => [null],
            'array' => [[]],
            'object' => [new stdClass()],
            'bool' => [false],
            'resource' => [fopen('php://memory', 'rb')],
        ];
    }

    /**
     * @dataProvider invalidHtmlDataTypeProvider
     */
    public function testExceptionIsRaisedIfNonStringElementsArePassedForHtmlBatchProcessing($element): void
    {
        $this->expectException(InvalidArgumentException::class);

        $purifier = new ilHtmlPurifierComposite();
        $purifier->purifyArray([$element]);
    }
}
