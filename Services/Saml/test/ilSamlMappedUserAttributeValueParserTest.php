<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilSamlMappedUserAttributeValueParserTest
 */
class ilSamlMappedUserAttributeValueParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $exception_class
     */
    protected function assertException($exception_class)
    {
        if (version_compare(PHPUnit_Runner_Version::id(), '5.0', '>=')) {
            $this->setExpectedException($exception_class);
        }
    }

    /**
     * @param $externalAttributeReference
     * @return \ilExternalAuthUserAttributeMappingRule
     */
    protected function getMappingRuleMock($externalAttributeReference)
    {
        $rule = $this->getMockBuilder(ilExternalAuthUserAttributeMappingRule::class)->disableOriginalConstructor()->getMock();
        $rule->expects($this->any())->method('getExternalAttribute')->will($this->returnValue($externalAttributeReference));
        $rule->expects($this->any())->method('getAttribute')->will($this->returnValue($externalAttributeReference));

        return $rule;
    }

    public function testValueGivenAsStringCanBeRetrievedForExternalAttribute()
    {
        $expectedValue = 'ILIAS';

        $attributeKey   = 'firstname';
        $attributeValue = $expectedValue;

        $userData = [$attributeKey => $attributeValue];

        $parser = new ilSamlMappedUserAttributeValueParser($this->getMappingRuleMock($attributeKey), $userData);
        $this->assertEquals($expectedValue, $parser->parse());
    }

    public function testValueGivenAsArrayCanBeRetrievedForExternalAttribute()
    {
        $expectedValue = 'ILIAS';

        $attributeKey   = 'firstname';
        $attributeValue = [$expectedValue];

        $userData = [$attributeKey => $attributeValue];

        $parser = new ilSamlMappedUserAttributeValueParser($this->getMappingRuleMock($attributeKey), $userData);
        $this->assertEquals($expectedValue, $parser->parse());
    }

    public function testValueGivenAsArrayCanBeRetrievedForExternalAttributeWithSpecificIndex()
    {
        $expectedValue      = 'ILIAS';
        $expectedValueIndex = 5;

        $attributeKey   = 'firstname';
        $attributeValue = [$expectedValueIndex => $expectedValue];

        $userData = [$attributeKey => $attributeValue];

        $parser = new ilSamlMappedUserAttributeValueParser(
            $this->getMappingRuleMock($attributeKey . '|' . $expectedValueIndex),
            $userData
        );
        $this->assertEquals($expectedValue, $parser->parse());
    }

    public function testExceptionIsRaisedIfAnExpectedAttributeIsMissing()
    {
        $this->assertException(ilSamlException::class);

        $attributeKey   = 'firstname';
        $userData       = [];

        $parser = new ilSamlMappedUserAttributeValueParser($this->getMappingRuleMock($attributeKey), $userData);
        $parser->parse();
    }

    public function testExceptionIsRaisedIfAnExpectedValueCouldNotBeFoundForAnExpectedValueIndex()
    {
        $this->assertException(ilSamlException::class);

        $expectedValue      = 'ILIAS';
        $expectedValueIndex = 5;

        $attributeKey   = 'firstname';
        $attributeValue = [($expectedValueIndex + 1) => $expectedValue];

        $userData       = [$attributeKey => $attributeValue];

        $parser = new ilSamlMappedUserAttributeValueParser(
            $this->getMappingRuleMock($attributeKey . '|' . $expectedValueIndex),
            $userData
        );
        $parser->parse();
    }
    
    public function testExceptionIsRaisedForNonScalarValues()
    {
        $this->assertException(ilSamlException::class);

        $expectedValue      = array('ILIAS');
        $expectedValueIndex = 5;

        $attributeKey   = 'firstname';
        $attributeValue = [$expectedValueIndex => $expectedValue];

        $userData = [$attributeKey => $attributeValue];

        $parser = new ilSamlMappedUserAttributeValueParser(
            $this->getMappingRuleMock($attributeKey . '|' . $expectedValueIndex),
            $userData
        );
        $parser->parse();
    }
}
