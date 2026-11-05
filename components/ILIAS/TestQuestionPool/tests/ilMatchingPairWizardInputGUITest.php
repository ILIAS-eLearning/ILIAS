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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Language\Language;

/**
* Unit tests
*
* @author Matheus Zych <mzych@databay.de>
*
* @ingroup components\ILIASTestQuestionPool
*
* This test was automatically generated.
*/
class ilMatchingPairWizardInputGUITest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private ilMatchingPairWizardInputGUI $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_uiFactory();
        $this->addGlobal_uiRenderer();

        $lng_mock = $this->getMockBuilder(Language::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->setGlobalVariable('refinery', new Refinery(new DataFactory(), $lng_mock));

        $this->object = new ilMatchingPairWizardInputGUI('', 'pairs');
        $this->object->setRequired(true);
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilMatchingPairWizardInputGUI::class, $this->object);
    }

    /**
     * @dataProvider checkInputDataProvider
     */
    public function testCheckInput(array $data, bool $expected): void
    {
        $this->object->setRequestParam('pairs', $data);

        $this->assertSame($expected, $this->object->checkInput());
    }

    public static function checkInputDataProvider(): array
    {
        return [
            'all rows valid' => [
                [
                    'points' => ['0' => '1'],
                    'term' => ['0' => '75904'],
                    'definition' => ['0' => '15104'],
                ],
                true,
            ],
            'minimum valid identifier value one' => [
                [
                    'points' => ['0' => '1'],
                    'term' => ['0' => '1'],
                    'definition' => ['0' => '1'],
                ],
                true,
            ],
            'term please select' => [
                [
                    'points' => ['0' => '1'],
                    'term' => ['0' => '0'],
                    'definition' => ['0' => '15104'],
                ],
                false,
            ],
            'definition please select' => [
                [
                    'points' => ['0' => '1'],
                    'term' => ['0' => '75904'],
                    'definition' => ['0' => '0'],
                ],
                false,
            ],
            'mantis reported case with extra empty pair row' => [
                [
                    'points' => ['0' => '1', '1' => '1'],
                    'term' => ['0' => '75904', '1' => '0'],
                    'definition' => ['0' => '15104', '1' => '0'],
                ],
                false,
            ],
            'missing term key for row' => [
                [
                    'points' => ['0' => '1', '1' => '1'],
                    'term' => ['0' => '75904'],
                    'definition' => ['0' => '15104', '1' => '28131'],
                ],
                false,
            ],
            'non-integer term value' => [
                [
                    'points' => ['0' => '1'],
                    'term' => ['0' => 'not-an-id'],
                    'definition' => ['0' => '15104'],
                ],
                false,
            ],
        ];
    }
}
