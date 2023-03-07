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

require_once("./libs/composer/vendor/autoload.php");

use ILIAS\Data\DataSize;
use PHPUnit\Framework\TestCase;

/**
 * Testing the DataSize object
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class DataSizeTest extends TestCase
{
    public function provideDataSizes(): array
    {
        return [
            [1000, '1000 B'],
            [1001, '1 KB'],
            [1023, '1.02 KB'],
            [1024, '1.02 KB'],
            [1025, '1.03 KB'],
            [10000, '10 KB'],
            [11000, '11 KB'],
            [28_566_695, '28.57 MB'],
            [48_521_625, '48.52 MB'],
            [58_777_412_654, '58.78 GB'],
            [46_546_544_654_545, '46.55 TB'],
            [125_862_151_563_255_622, '125862.15 TB'],
        ];
    }

    /**
     * @dataProvider provideDataSizes
     */
    public function testDifferentDataSizes(int $bytes, string $expected_representation): void
    {
        $datasize = new DataSize($bytes, DataSize::Byte);

        $this->assertEquals($expected_representation, $datasize->__toString());
    }

    /**
     * @dataProvider tDataProvider
     */
    public function test_normal($a, $b, $expected, $expected_in_bytes): void
    {
        $ds = new DataSize($a, $b);
        $this->assertEquals($a / $b, $ds->getSize());
        $this->assertEquals($b, $ds->getUnit());
        $this->assertEquals($expected, $ds->__toString());
        if ($expected_in_bytes) {
            $this->assertEquals($expected_in_bytes, (int) $ds->inBytes());
        }
    }

    public function test_division_by_zero(): void
    {
        try {
            $ds = new DataSize(4533, 0);
            $this->assertFalse("This should not happen");
        } catch (Exception | DivisionByZeroError $e) {
            $this->assertTrue(true);
        }
    }

    public function tDataProvider(): array
    {
        return [
            [122, 1000, "122 B", 122],
            [-122, 1000, "-122 B", -122],
            [122, 1000000, "122 B", 122],
            [-122, 1000000, "-122 B", -122],
            [122, 1000000000, "122 B", 122],
            [-122, 1000000000, "-122 B", -122],
            [122, 1000000000000, "122 B", null], // There is a float rounding error here
            [-122, 1000000000000, "-122 B", null], // There is a float rounding error here
            [122, 1024, "122 B", 122],
            [-122, 1024, "-122 B", -122],
            [122, 1048576, "122 B", 122],
            [-122, 1048576, "-122 B", -122],
            [122, 1073741824, "122 B", 122],
            [-122, 1073741824, "-122 B", -122],
            [122, 1099511627776, "122 B", 122],
            [-122, 1099511627776, "-122 B", -122],
            [10 * DataSize::KiB, DataSize::KiB, "10.24 KB", 10 * DataSize::KiB],
        ];
    }
}
