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

namespace ILIAS\Tests\Refinery;

use ILIAS\Refinery\KeyValueAccess;
use ILIAS\Refinery\Factory as Refinery;
use ilLanguage;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use ILIAS\Data\Factory as DataFactory;

class KeyValueAccessTest extends PHPUnitTestCase
{
    private Refinery $refinery;

    protected function setUp() : void
    {
        $this->refinery = new Refinery(new DataFactory, $this->createMock(ilLanguage::class));
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
