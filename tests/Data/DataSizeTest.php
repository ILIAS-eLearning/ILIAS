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
            [122, 1000, "0.122 KB", 122],
            [-122, 1000, "-0.122 KB", -122],
            [122, 1000000, "0.000122 MB", 122],
            [-122, 1000000, "-0.000122 MB", -122],
            [122, 1000000000, "1.22E-7 GB", 122],
            [-122, 1000000000, "-1.22E-7 GB", -122],
            [122, 1000000000000, "1.22E-10 TB", null], // There is a float rounding error here
            [-122, 1000000000000, "-1.22E-10 TB", null], // There is a float rounding error here
            [122, 1000000000000000, "1.22E-13 PB", 122],
            [-122, 1000000000000000, "-1.22E-13 PB", -122],
            [122, 1000000000000000000, "1.22E-16 EB", 122],
            [-122, 1000000000000000000, "-1.22E-16 EB", -122],
            [122, 1024, "0.119140625 KiB", 122],
            [-122, 1024, "-0.119140625 KiB", -122],
            [122, 1048576, "0.00011634826660156 MiB", 122],
            [-122, 1048576, "-0.00011634826660156 MiB", -122],
            [122, 1073741824, "1.1362135410309E-7 GiB", 122],
            [-122, 1073741824, "-1.1362135410309E-7 GiB", -122],
            [122, 1099511627776, "1.109583536163E-10 TiB", 122],
            [-122, 1099511627776, "-1.109583536163E-10 TiB", -122],
            [122, 1125899906842624, "1.0835776720342E-13 PiB", 122],
            [-122, 1125899906842624, "-1.0835776720342E-13 PiB", -122],
            [122, 1152921504606846976, "1.0581813203459E-16 EiB", 122],
            [-122, 1152921504606846976, "-1.0581813203459E-16 EiB", -122],
            [10 * DataSize::KiB, DataSize::KiB, "10 KiB", 10 * DataSize::KiB]

            // This tests will fail because the second param of DataSize
            // needs an integer and this numbers are to big.
            // [122, 1000000000000000000000, "1.22E-19 ZB"],
            // [-122, 1000000000000000000000, "-1.22E-19 ZB"],
            // [122, 1000000000000000000000000, "1.22E-19 YB"],
            // [-122, 1000000000000000000000000, "-1.22E-19 YB"]
        ];
    }
}
