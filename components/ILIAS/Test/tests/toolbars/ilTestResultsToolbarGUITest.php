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
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTestResultsToolbarGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestResultsToolbarGUITest extends ilTestBaseTestCase
{
    private ilTestResultsToolbarGUI $ilTestResultsToolbarGUI;

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->ilTestResultsToolbarGUI = $this->createInstanceOf(ilTestResultsToolbarGUI::class);
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilTestResultsToolbarGUI::class, $this->ilTestResultsToolbarGUI);
    }

    /**
     * @dataProvider setAndGetCertificateLinkTargetDataProvider
     */
    public function testSetAndGetCertificateLinkTarget(string $IO): void
    {
        $this->assertNull($this->ilTestResultsToolbarGUI->getCertificateLinkTarget());
        $this->ilTestResultsToolbarGUI->setCertificateLinkTarget($IO);
        $this->assertEquals($IO, $this->ilTestResultsToolbarGUI->getCertificateLinkTarget());
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
     */
    public function testSetAndGetShowBestSolutionsLinkTarget(string $IO): void
    {
        $this->assertNull($this->ilTestResultsToolbarGUI->getShowBestSolutionsLinkTarget());
        $this->ilTestResultsToolbarGUI->setShowBestSolutionsLinkTarget($IO);
        $this->assertEquals($IO, $this->ilTestResultsToolbarGUI->getShowBestSolutionsLinkTarget());
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
     */
    public function testSetAndGetHideBestSolutionsLinkTarget(string $IO): void
    {
        $this->assertNull($this->ilTestResultsToolbarGUI->getHideBestSolutionsLinkTarget());
        $this->ilTestResultsToolbarGUI->setHideBestSolutionsLinkTarget($IO);
        $this->assertEquals($IO, $this->ilTestResultsToolbarGUI->getHideBestSolutionsLinkTarget());
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
     */
    public function testSetAndGetParticipantSelectorOptions(array $IO): void
    {
        $this->assertEmpty($this->ilTestResultsToolbarGUI->getParticipantSelectorOptions());
        $this->ilTestResultsToolbarGUI->setParticipantSelectorOptions($IO);
        $this->assertEquals($IO, $this->ilTestResultsToolbarGUI->getParticipantSelectorOptions());
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
     * @throws \Exception|Exception
     */
    public function testGetParticipantSelectorLinksArray(?array $input, array $output): void
    {
        $this->adaptDICServiceMock(\ILIAS\UI\Factory::class, function (\ILIAS\UI\Factory|MockObject $mock) use ($input, $output) {
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

        if (!is_null($input)) {
            $this->ilTestResultsToolbarGUI->setParticipantSelectorOptions($input);
        }

        $this->assertEquals($output, $this->ilTestResultsToolbarGUI->getParticipantSelectorLinksArray());
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
