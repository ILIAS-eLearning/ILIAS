<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceCriterionConfigTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceCriterionConfigTest extends ilTermsOfServiceCriterionBaseTest
{
    public function testConfigCanBePassedAsArray() : void
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

    public function testConfigCanBePassedAsJson() : void
    {
        $actualKey = 'phpunit';
        $actualValue = 'rulz';

        $data = json_encode([$actualKey => $actualValue], JSON_THROW_ON_ERROR);

        $config = new ilTermsOfServiceCriterionConfig($data);

        $this->assertSame($data, $config->toJson());
        $this->assertArrayHasKey($actualKey, $config);
        $this->assertSame($actualValue, $config[$actualKey]);
    }

    public function testConfigCanBeImportedAsJson() : void
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
