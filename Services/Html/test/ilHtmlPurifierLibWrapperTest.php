<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ilHtmlPurifierLibWrapperTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilHtmlPurifierLibWrapperTest extends TestCase
{
    private function getPurifier() : ilHtmlPurifierAbstractLibWrapper
    {
        return new class extends ilHtmlPurifierAbstractLibWrapper {
            protected function getPurifierConfigInstance() : HTMLPurifier_Config
            {
                return HTMLPurifier_Config::createDefault();
            }
        };
    }

    public function testPurifierIsCalledIfStringsArePurified() : void
    {
        $purifier = $this->getPurifier();

        $this->assertEquals('phpunit', $purifier->purify('phpunit'));

        $toPurify = [
            'phpunit1',
            'phpunit2',
            'phpunit3',
        ];
        $this->assertEquals($toPurify, $purifier->purifyArray($toPurify));
    }

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

        $purifier = $this->getPurifier();
        $purifier->purifyArray([$element]);
    }
}
