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

use ILIAS\UI\Component\Link\Factory as LinkFactory;
use ILIAS\UI\Implementation\Component\Link\Standard as Link;
use PHPUnit\Framework\MockObject\Exception;
use ILIAS\UI\Factory as UIFactory;

/**
 * Class ilTestResultsToolbarGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestResultsToolbarGUITest extends ilTestBaseTestCase
{
    /**
     * @throws Exception
     */
    public function testConstruct(): void
    {
        $il_test_results_toolbar_gui = new ilTestResultsToolbarGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilGlobalPageTemplate::class),
            $this->createMock(ilLanguage::class)
        );

        $this->assertInstanceOf(ilTestResultsToolbarGUI::class, $il_test_results_toolbar_gui);
    }

    /**
     * @dataProvider setAndGetCertificateLinkTargetDataProvider
     * @throws Exception
     */
    public function testSetAndGetCertificateLinkTarget(string $IO): void
    {
        $il_test_results_toolbar_gui = new ilTestResultsToolbarGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilGlobalPageTemplate::class),
            $this->createMock(ilLanguage::class)
        );

        $this->assertNull($il_test_results_toolbar_gui->getCertificateLinkTarget());
        $il_test_results_toolbar_gui->setCertificateLinkTarget($IO);
        $this->assertEquals($IO, $il_test_results_toolbar_gui->getCertificateLinkTarget());
    }

    public static function setAndGetCertificateLinkTargetDataProvider(): array
    {
        return [
            'empty' => [''],
            'string' => ['string'],
            'strING' => ['strING']
        ];
    }

    /**
     * @dataProvider setAndGetShowBestSolutionsLinkTargetDataProvider
     * @throws Exception
     */
    public function testSetAndGetShowBestSolutionsLinkTarget(string $IO): void
    {
        $il_test_results_toolbar_gui = new ilTestResultsToolbarGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilGlobalPageTemplate::class),
            $this->createMock(ilLanguage::class)
        );

        $this->assertNull($il_test_results_toolbar_gui->getShowBestSolutionsLinkTarget());
        $il_test_results_toolbar_gui->setShowBestSolutionsLinkTarget($IO);
        $this->assertEquals($IO, $il_test_results_toolbar_gui->getShowBestSolutionsLinkTarget());
    }

    public static function setAndGetShowBestSolutionsLinkTargetDataProvider(): array
    {
        return [
            'empty' => [''],
            'string' => ['string'],
            'strING' => ['strING']
        ];
    }

    /**
     * @dataProvider setAndGetHideBestSolutionsLinkTargetDataProvider
     * @throws Exception
     */
    public function testSetAndGetHideBestSolutionsLinkTarget(string $IO): void
    {
        $il_test_results_toolbar_gui = new ilTestResultsToolbarGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilGlobalPageTemplate::class),
            $this->createMock(ilLanguage::class)
        );

        $this->assertNull($il_test_results_toolbar_gui->getHideBestSolutionsLinkTarget());
        $il_test_results_toolbar_gui->setHideBestSolutionsLinkTarget($IO);
        $this->assertEquals($IO, $il_test_results_toolbar_gui->getHideBestSolutionsLinkTarget());
    }

    public static function setAndGetHideBestSolutionsLinkTargetDataProvider(): array
    {
        return [
            'empty' => [''],
            'string' => ['string'],
            'strING' => ['strING']
        ];
    }

    /**
     * @dataProvider setAndGetParticipantSelectorOptionsDataProvider
     * @throws Exception
     */
    public function testSetAndGetParticipantSelectorOptions(array $IO): void
    {
        $il_test_results_toolbar_gui = new ilTestResultsToolbarGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilGlobalPageTemplate::class),
            $this->createMock(ilLanguage::class)
        );

        $this->assertEmpty($il_test_results_toolbar_gui->getParticipantSelectorOptions());
        $il_test_results_toolbar_gui->setParticipantSelectorOptions($IO);
        $this->assertEquals($IO, $il_test_results_toolbar_gui->getParticipantSelectorOptions());
    }

    public static function setAndGetParticipantSelectorOptionsDataProvider(): array
    {
        return [
            'empty' => [[]],
            'array_string' => [['string']],
            'array_strING' => [['strING']]
        ];
    }

    /**
     * @dataProvider getParticipantSelectorLinksArrayDataProvider
     * @throws Exception
     */
    public function testGetParticipantSelectorLinksArray(?array $input, array $output): void
    {
        $link_factory = $this->createMock(LinkFactory::class);
        $link_factory
            ->expects($this->exactly(count($input ?? [])))
            ->method('standard')
            ->withAnyParameters()
            ->willReturnOnConsecutiveCalls(...$output);

        $this->setGlobalVariable('ui.factory', $this->createConfiguredMock(UIFactory::class, [
            'link' => $link_factory
        ]));

        $il_test_results_toolbar_gui = new ilTestResultsToolbarGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilGlobalPageTemplate::class),
            $this->createMock(ilLanguage::class)
        );

        if (!is_null($input)) {
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
            'array_string_string' => [
                ['label_1' => 'string', 'label_2' => 'string'],
                [new Link('label', 'string'), new Link('label', 'string')]
            ],
            'array_strING_strING' => [
                ['label_1' => 'strING', 'label_2' => 'strING'],
                [new Link('label', 'strING'), new Link('label', 'strING')]
            ],
            'array_string_strING' => [
                ['label_1' => 'string', 'label_2' => 'strING'],
                [new Link('label', 'string'), new Link('label', 'strING')]
            ],
            'array_strING_string' => [
                ['label_1' => 'strING', 'label_2' => 'string'],
                [new Link('label', 'strING'), new Link('label', 'string')]
            ]
        ];
    }
}
