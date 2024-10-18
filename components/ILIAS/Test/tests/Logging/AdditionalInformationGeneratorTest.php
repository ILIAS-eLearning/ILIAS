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

namespace ILIAS\Test\Tests\Logging;

use ILIAS\Test\Logging\AdditionalInformationGenerator;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionException;

class AdditionalInformationGeneratorTest extends ilTestBaseTestCase
{
    /**
     * @dataProvider getTrueFalseTagForBoolDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetTrueFalseTagForBool(bool $input, string $output): void
    {
        $additional_information_generator = $this->createInstanceOf(AdditionalInformationGenerator::class);
        $this->assertEquals($output, $additional_information_generator->getTrueFalseTagForBool($input));
    }

    public static function getTrueFalseTagForBoolDataProvider(): array
    {
        return [
            'true' => [true, '{{ true }}'],
            'false' => [false, '{{ false }}']
        ];
    }

    /**
     * @dataProvider getEnabledDisabledTagForBoolDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetEnabledDisabledTagForBool(bool $input, string $output): void
    {
        $additional_information_generator = $this->createInstanceOf(AdditionalInformationGenerator::class);
        $this->assertEquals($output, $additional_information_generator->getEnabledDisabledTagForBool($input));
    }

    public static function getEnabledDisabledTagForBoolDataProvider(): array
    {
        return [
            'true' => [true, '{{ enabled }}'],
            'false' => [false, '{{ disabled }}']
        ];
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetNoneTag(): void
    {
        $additional_information_generator = $this->createInstanceOf(AdditionalInformationGenerator::class);
        $this->assertEquals('{{ none }}', $additional_information_generator->getNoneTag());
    }

    /**
     * @dataProvider getTagForLangVarDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetTagForLangVar(string $input, string $output): void
    {
        $additional_information_generator = $this->createInstanceOf(AdditionalInformationGenerator::class);
        $this->assertEquals($output, $additional_information_generator->getTagForLangVar($input));
    }

    public static function getTagForLangVarDataProvider(): array
    {
        return [
            'empty' => ['', '{{  }}'],
            'string' => ['string', '{{ string }}'],
            'strING' => ['strING', '{{ strING }}'],
            'STRING' => ['STRING', '{{ STRING }}']
        ];
    }

    /**
     * @dataProvider getCheckedUncheckedTagForBoolDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetCheckedUncheckedTagForBool(bool $input, string $output): void
    {
        $additional_information_generator = $this->createInstanceOf(AdditionalInformationGenerator::class);
        $this->assertEquals($output, $additional_information_generator->getCheckedUncheckedTagForBool($input));
    }

    public static function getCheckedUncheckedTagForBoolDataProvider(): array
    {
        return [
            'true' => [true, '{{ checked }}'],
            'false' => [false, '{{ unchecked }}']
        ];
    }
}
