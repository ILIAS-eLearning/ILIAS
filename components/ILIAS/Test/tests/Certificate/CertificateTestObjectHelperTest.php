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

namespace ILIAS\Test\Certificate;

use ilTestBaseTestCase;

class CertificateTestObjectHelperTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $this->assertInstanceOf(CertificateTestObjectHelper::class, new CertificateTestObjectHelper());
    }

    /**
     * @dataProvider getResultPassDataProvider
     */
    public function testGetResultPass(int $input, ?int $output): void
    {
        $this->assertEquals($output, (new CertificateTestObjectHelper())->getResultPass($input));
    }

    public static function getResultPassDataProvider(): array
    {
        return [
            'negative_one' => [-1, null],
            'zero' => [0, null],
            'one' => [1, null]
        ];
    }
}
