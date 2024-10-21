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

namespace ILIAS\Test\Tests\Toolbars;

use ILIAS\UI\Component\Link\Factory as LinkFactory;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Implementation\Component\Link\Standard as Link;
use ilTestBaseTestCase;
use ilTestResultsToolbarGUI;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

/**
 * Class ilTestResultsToolbarGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestResultsToolbarGUITest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $il_test_results_toolbar_gui = $this->createInstanceOf(ilTestResultsToolbarGUI::class);
        $this->assertInstanceOf(ilTestResultsToolbarGUI::class, $il_test_results_toolbar_gui);
    }

    /**
     * @dataProvider setAndGetCertificateLinkTargetDataProvider
     * @throws ReflectionException|Exception
     */
    public function testSetAndGetCertificateLinkTarget(string $IO): void
    {
        $il_test_results_toolbar_gui = $this->createInstanceOf(ilTestResultsToolbarGUI::class);
        $this->assertNull($il_test_results_toolbar_gui->getCertificateLinkTarget());
        $il_test_results_toolbar_gui->setCertificateLinkTarget($IO);
        $this->assertEquals($IO, $il_test_results_toolbar_gui->getCertificateLinkTarget());
    }

    public static function setAndGetCertificateLinkTargetDataProvider(): array
    {
        return [
            'empty' => [''],
            'string' => ['string'],
            'strING' => ['strING'],
            'STRING' => ['STRING']
        ];
    }

    /**
     * @dataProvider setAndGetShowBestSolutionsLinkTargetDataProvider
     * @throws ReflectionException|Exception
     */
    public function testSetAndGetShowBestSolutionsLinkTarget(string $IO): void
    {
        $il_test_results_toolbar_gui = $this->createInstanceOf(ilTestResultsToolbarGUI::class);
        $this->assertNull($il_test_results_toolbar_gui->getShowBestSolutionsLinkTarget());
        $il_test_results_toolbar_gui->setShowBestSolutionsLinkTarget($IO);
        $this->assertEquals($IO, $il_test_results_toolbar_gui->getShowBestSolutionsLinkTarget());
    }

    public static function setAndGetShowBestSolutionsLinkTargetDataProvider(): array
    {
        return [
            'empty' => [''],
            'string' => ['string'],
            'strING' => ['strING'],
            'STRING' => ['STRING']
        ];
    }

    /**
     * @dataProvider setAndGetHideBestSolutionsLinkTargetDataProvider
     * @throws ReflectionException|Exception
     */
    public function testSetAndGetHideBestSolutionsLinkTarget(string $IO): void
    {
        $il_test_results_toolbar_gui = $this->createInstanceOf(ilTestResultsToolbarGUI::class);
        $this->assertNull($il_test_results_toolbar_gui->getHideBestSolutionsLinkTarget());
        $il_test_results_toolbar_gui->setHideBestSolutionsLinkTarget($IO);
        $this->assertEquals($IO, $il_test_results_toolbar_gui->getHideBestSolutionsLinkTarget());
    }

    public static function setAndGetHideBestSolutionsLinkTargetDataProvider(): array
    {
        return [
            'empty' => [''],
            'string' => ['string'],
            'strING' => ['strING'],
            'STRING' => ['STRING']
        ];
    }

    /**
     * @dataProvider setAndGetParticipantSelectorOptionsDataProvider
     * @throws ReflectionException|Exception
     */
    public function testSetAndGetParticipantSelectorOptions(array $IO): void
    {
        $il_test_results_toolbar_gui = $this->createInstanceOf(ilTestResultsToolbarGUI::class);
        $this->assertEmpty($il_test_results_toolbar_gui->getParticipantSelectorOptions());
        $il_test_results_toolbar_gui->setParticipantSelectorOptions($IO);
        $this->assertEquals($IO, $il_test_results_toolbar_gui->getParticipantSelectorOptions());
    }

    public static function setAndGetParticipantSelectorOptionsDataProvider(): array
    {
        return [
            'empty' => [[]],
            'array_string' => [['string']],
            'array_strING' => [['strING']],
            'array_STRING' => [['STRING']]
        ];
    }

    /**
     * @dataProvider getParticipantSelectorLinksArrayDataProvider
     * @throws Exception|ReflectionException
     */
    public function testGetParticipantSelectorLinksArray(?array $input, array $output): void
    {
        $il_test_results_toolbar_gui = $this->createInstanceOf(ilTestResultsToolbarGUI::class);

        $this->adaptDICServiceMock(UIFactory::class, function (UIFactory|MockObject $mock) use ($input, $output) {
            $link_factory = $this->createMock(LinkFactory::class);
            $link_factory
                ->expects($this->exactly(count($input ?? [])))
                ->method('standard')
                ->withAnyParameters()
                ->willReturnOnConsecutiveCalls(...$output);

            $mock
                ->method('link')
                ->willReturn($link_factory);
        });

        if ($input !== null) {
            $il_test_results_toolbar_gui->setParticipantSelectorOptions($input);
        }

        $this->assertEquals($output, $il_test_results_toolbar_gui->getParticipantSelectorLinksArray());
    }

    public static function getParticipantSelectorLinksArrayDataProvider(): array
    {
        return [
            'default' => [null, []],
            'empty' => [[], []],
            'array_string' => [['label' => 'string'], [new Link('label', 'string')]],
            'array_strING' => [['label' => 'strING'], [new Link('label', 'strING')]],
            'array_STRING' => [['label' => 'STRING'], [new Link('label', 'STRING')]],
            'array_string_string' => [
                ['label_1' => 'string', 'label_2' => 'string'],
                [new Link('label', 'string'), new Link('label', 'string')]
            ],
            'array_strING_strING' => [
                ['label_1' => 'strING', 'label_2' => 'strING'],
                [new Link('label', 'strING'), new Link('label', 'strING')]
            ],
            'array_STRING_STRING' => [
                ['label_1' => 'STRING', 'label_2' => 'STRING'],
                [new Link('label', 'STRING'), new Link('label', 'STRING')]
            ],
            'array_string_strING' => [
                ['label_1' => 'string', 'label_2' => 'strING'],
                [new Link('label', 'string'), new Link('label', 'strING')]
            ],
            'array_string_STRING' => [
                ['label_1' => 'string', 'label_2' => 'STRING'],
                [new Link('label', 'string'), new Link('label', 'STRING')]
            ],
            'array_strING_string' => [
                ['label_1' => 'strING', 'label_2' => 'string'],
                [new Link('label', 'strING'), new Link('label', 'string')]
            ],
            'array_strING_STRING' => [
                ['label_1' => 'strING', 'label_2' => 'STRING'],
                [new Link('label', 'strING'), new Link('label', 'STRING')]
            ],
            'array_STRING_string' => [
                ['label_1' => 'STRING', 'label_2' => 'string'],
                [new Link('label', 'STRING'), new Link('label', 'string')]
            ],
            'array_STRING_strING' => [
                ['label_1' => 'STRING', 'label_2' => 'strING'],
                [new Link('label', 'STRING'), new Link('label', 'strING')]
            ]
        ];
    }
}
