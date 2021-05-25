<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ilHtmlPurifierCompositeTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilHtmlPurifierCompositeTest extends TestCase
{
    private function getFakePurifier() : ilHtmlPurifierInterface
    {
        return new class implements ilHtmlPurifierInterface {
            public function purify(string $html) : string
            {
                return $html . '.';
            }

            public function purifyArray(array $htmlCollection) : array
            {
                foreach ($htmlCollection as $key => &$html) {
                    $html .= '.';
                }

                return $htmlCollection;
            }
        };
    }

    public function testPurifierNodesAreCalledIfStringGetsPurified() : void
    {
        $purifier = new ilHtmlPurifierComposite();

        $p1 = $this->getFakePurifier();
        $p2 = clone $p1;
        $p3 = clone $p1;

        $purifier->addPurifier($p1);
        $purifier->addPurifier($p1);
        $purifier->addPurifier($p2);
        $purifier->addPurifier($p3);

        $this->assertEquals('phpunit...', $purifier->purify('phpunit'));

        $purifier->removePurifier($p2);

        $this->assertEquals('phpunit..', $purifier->purify('phpunit'));
    }

    public function testPurifierNodesAreCalledIfArrayOfStringGetssPurified() : void
    {
        $purifier = new ilHtmlPurifierComposite();

        $p1 = $this->getFakePurifier();
        $p2 = clone $p1;
        $p3 = clone $p1;

        $purifier->addPurifier($p1);
        $purifier->addPurifier($p1);
        $purifier->addPurifier($p2);
        $purifier->addPurifier($p3);

        $toPurify = [
            'phpunit1',
            'phpunit2',
            'phpunit3',
        ];

        $this->assertEquals(array_map(static function (string $html) : string {
            return $html . '...';
        }, $toPurify), $purifier->purifyArray($toPurify));

        $purifier->removePurifier($p2);

        $this->assertEquals(array_map(static function (string $html) : string {
            return $html . '..';
        }, $toPurify), $purifier->purifyArray($toPurify));
    }

    /**
     * @return array
     */
    public function invalidHtmlDataTypeProvider() : array
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
    public function testExceptionIsRaisedIfNonStringElementsArePassedForHtmlBatchProcessing($element) : void
    {
        $this->expectException(InvalidArgumentException::class);

        $purifier = new ilHtmlPurifierComposite();
        $purifier->purifyArray([$element]);
    }
}
