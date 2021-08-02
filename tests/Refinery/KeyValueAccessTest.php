<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Refinery;

use ILIAS\Refinery\KeyValueAccess;
use ILIAS\Refinery\Factory;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Class KeyValueAccessTest
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class KeyValueAccessTest extends PHPUnitTestCase
{
    protected Factory $refinery;

    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        $this->refinery = new Factory(new \ILIAS\Data\Factory(), $this->createMock(\ilLanguage::class));
    }

    public function testAccess() : void
    {
        $array = [
            'key_one' => '1',
            'key_two' => '2',
            'key_three' => '3',
        ];

        $kv = new KeyValueAccess($array, $this->refinery->kindlyTo()->int());

        $this->assertEquals(3, $kv->count());
        $this->assertEquals(1, $kv['key_one']);
        $this->assertEquals(2, $kv['key_two']);
        $this->assertEquals(3, $kv['key_three']);
        $this->assertEquals(null, $kv['key_four']);
    }

    public function testRecursion() : void
    {
        $array = [
            'key_one' => '1',
            'key_two' => [
                'sub_key_one' => '1',
                'sub_key_two' => [
                    'sub_sub_key_one' => '1',
                ],
            ]
        ];

        $kv = new KeyValueAccess($array, $this->refinery->kindlyTo()->int());
        $this->assertEquals(2, $kv->count());
        $this->assertEquals(1, $kv['key_two']['sub_key_one']);
        $this->assertEquals(1, $kv['key_two']['sub_key_two']['sub_sub_key_one']);
    }
}
