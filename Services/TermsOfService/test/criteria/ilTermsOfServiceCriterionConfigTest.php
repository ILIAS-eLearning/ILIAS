<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceCriterionConfigTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceCriterionConfigTest extends \ilTermsOfServiceCriterionBaseTest
{
    /**
     *
     */
    public function testConfigCanBePassedAsArray()
    {
        $actualKey = 'phpunit';
        $actualValue = 'rulz';

        $data = [$actualKey => $actualValue];

        $config = new \ilTermsOfServiceCriterionConfig($data);

        $expected = json_encode($data);

        $this->assertEquals($expected, $config->toJson());
        $this->assertArrayHasKey($actualKey, $config);
        $this->assertEquals($actualValue, $config[$actualKey]);
    }

    /**
     *
     */
    public function testConfigCanBePassedAsJson()
    {
        $actualKey = 'phpunit';
        $actualValue = 'rulz';

        $data = json_encode([$actualKey => $actualValue]);

        $config = new \ilTermsOfServiceCriterionConfig($data);

        $this->assertEquals($data, $config->toJson());
        $this->assertArrayHasKey($actualKey, $config);
        $this->assertEquals($actualValue, $config[$actualKey]);
    }

    /**
     *
     */
    public function testConfigCanBeImportedAsJson()
    {
        $actualKey = 'phpunit';
        $actualValue = 'rulz';

        $data = json_encode([$actualKey => $actualValue]);

        $config = new \ilTermsOfServiceCriterionConfig();
        $config->fromJson($data);

        $this->assertEquals($data, $config->toJson());
        $this->assertArrayHasKey($actualKey, $config);
        $this->assertEquals($actualValue, $config[$actualKey]);
    }
}
