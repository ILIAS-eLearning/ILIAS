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

/**
 * Class ilTermsOfServiceCriterionConfigTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceCriterionConfigTest extends ilTermsOfServiceCriterionBaseTest
{
    public function testConfigCanBePassedAsArray(): void
    {
        $actualKey = 'phpunit';
        $actualValue = 'rulz';

        $data = [$actualKey => $actualValue];

        $config = new ilTermsOfServiceCriterionConfig($data);

        $expected = json_encode($data, JSON_THROW_ON_ERROR);

        $this->assertSame($expected, $config->toJson());
        $this->assertArrayHasKey($actualKey, $config);
        $this->assertSame($actualValue, $config[$actualKey]);
    }

    public function testConfigCanBePassedAsJson(): void
    {
        $actualKey = 'phpunit';
        $actualValue = 'rulz';

        $data = json_encode([$actualKey => $actualValue], JSON_THROW_ON_ERROR);

        $config = new ilTermsOfServiceCriterionConfig($data);

        $this->assertSame($data, $config->toJson());
        $this->assertArrayHasKey($actualKey, $config);
        $this->assertSame($actualValue, $config[$actualKey]);
    }

    public function testConfigCanBeImportedAsJson(): void
    {
        $actualKey = 'phpunit';
        $actualValue = 'rulz';

        $data = json_encode([$actualKey => $actualValue], JSON_THROW_ON_ERROR);

        $config = new ilTermsOfServiceCriterionConfig();
        $config->fromJson($data);

        $this->assertSame($data, $config->toJson());
        $this->assertArrayHasKey($actualKey, $config);
        $this->assertSame($actualValue, $config[$actualKey]);
    }
}
