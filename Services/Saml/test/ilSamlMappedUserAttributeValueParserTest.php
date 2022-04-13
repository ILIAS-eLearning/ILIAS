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
        $this->assertSame($expectedValue, $parser->parse());
    }

    public function testValueGivenAsArrayCanBeRetrievedForExternalAttribute() : void
    {
        $expectedValue = 'ILIAS';

        $attributeKey = 'firstname';
        $attributeValue = [$expectedValue];

        $userData = [$attributeKey => $attributeValue];

        $parser = new ilSamlMappedUserAttributeValueParser($this->getMappingRuleMock($attributeKey), $userData);
        $this->assertSame($expectedValue, $parser->parse());
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
        $this->assertSame($expectedValue, $parser->parse());
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
