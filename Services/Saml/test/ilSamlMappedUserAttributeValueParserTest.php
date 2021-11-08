<?php declare(strict_types=1);

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ilSamlMappedUserAttributeValueParserTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilSamlMappedUserAttributeValueParserTest extends TestCase
{
    protected function getMappingRuleMock(string $externalAttributeReference) : ilExternalAuthUserAttributeMappingRule
    {
        $rule = $this->getMockBuilder(ilExternalAuthUserAttributeMappingRule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rule
            ->method('getExternalAttribute')
            ->willReturn($externalAttributeReference);
        $rule
            ->method('getAttribute')
            ->willReturn($externalAttributeReference);

        return $rule;
    }

    public function testValueGivenAsStringCanBeRetrievedForExternalAttribute() : void
    {
        $expectedValue = 'ILIAS';

        $attributeKey = 'firstname';
        $attributeValue = $expectedValue;

        $userData = [$attributeKey => $attributeValue];

        $parser = new ilSamlMappedUserAttributeValueParser($this->getMappingRuleMock($attributeKey), $userData);
        $this->assertEquals($expectedValue, $parser->parse());
    }

    public function testValueGivenAsArrayCanBeRetrievedForExternalAttribute() : void
    {
        $expectedValue = 'ILIAS';

        $attributeKey = 'firstname';
        $attributeValue = [$expectedValue];

        $userData = [$attributeKey => $attributeValue];

        $parser = new ilSamlMappedUserAttributeValueParser($this->getMappingRuleMock($attributeKey), $userData);
        $this->assertEquals($expectedValue, $parser->parse());
    }

    public function testValueGivenAsArrayCanBeRetrievedForExternalAttributeWithSpecificIndex() : void
    {
        $expectedValue = 'ILIAS';
        $expectedValueIndex = 5;

        $attributeKey = 'firstname';
        $attributeValue = [$expectedValueIndex => $expectedValue];

        $userData = [$attributeKey => $attributeValue];

        $parser = new ilSamlMappedUserAttributeValueParser(
            $this->getMappingRuleMock($attributeKey . '|' . $expectedValueIndex),
            $userData
        );
        $this->assertEquals($expectedValue, $parser->parse());
    }

    public function testExceptionIsRaisedIfAnExpectedAttributeIsMissing() : void
    {
        $this->expectException(ilSamlException::class);

        $attributeKey = 'firstname';
        $userData = [];

        $parser = new ilSamlMappedUserAttributeValueParser($this->getMappingRuleMock($attributeKey), $userData);
        $parser->parse();
    }

    public function testExceptionIsRaisedIfAnExpectedValueCouldNotBeFoundForAnExpectedValueIndex() : void
    {
        $this->expectException(ilSamlException::class);

        $expectedValue = 'ILIAS';
        $expectedValueIndex = 5;

        $attributeKey = 'firstname';
        $attributeValue = [($expectedValueIndex + 1) => $expectedValue];

        $userData = [$attributeKey => $attributeValue];

        $parser = new ilSamlMappedUserAttributeValueParser(
            $this->getMappingRuleMock($attributeKey . '|' . $expectedValueIndex),
            $userData
        );
        $parser->parse();
    }

    public function testExceptionIsRaisedForNonScalarValues() : void
    {
        $this->expectException(ilSamlException::class);

        $expectedValue = ['ILIAS'];
        $expectedValueIndex = 5;

        $attributeKey = 'firstname';
        $attributeValue = [$expectedValueIndex => $expectedValue];

        $userData = [$attributeKey => $attributeValue];

        $parser = new ilSamlMappedUserAttributeValueParser(
            $this->getMappingRuleMock($attributeKey . '|' . $expectedValueIndex),
            $userData
        );
        $parser->parse();
    }
}
