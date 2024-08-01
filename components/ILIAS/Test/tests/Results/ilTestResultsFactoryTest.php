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

namespace Results;

use ilObjTest;
use ilTestBaseTestCase;
use ilTestPassResult;
use ilTestResultsFactory;
use ilTestShuffler;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use PHPUnit\Framework\MockObject\Exception;

class ilTestResultsFactoryTest extends ilTestBaseTestCase
{
    private ilObjTest $ilObjTest;

    private ilTestResultsFactory $ilTestResultsFactory;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $test_shuffler = $this->createMock(ilTestShuffler::class);
        $this->ilObjTest = $this->createMock(ilObjTest::class);

        global $DIC;
        $this->ilTestResultsFactory = new ilTestResultsFactory(
            $test_shuffler,
            $DIC['ui.factory'],
            $DIC['ui.renderer']
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilTestResultsFactory::class, $this->ilTestResultsFactory);
    }

    /**
     * @dataProvider getPassResultsForDataProvider
     */
    public function testGetPassResultsFor(array $IO): void
    {
        if (is_null($IO['is_user_output'])) {
            $ilTestPassResult = $this->ilTestResultsFactory->getPassResultsFor(
                $this->ilObjTest,
                $IO['active_id'],
                $IO['pass_id'],
            );
        } else {
            $ilTestPassResult = $this->ilTestResultsFactory->getPassResultsFor(
                $this->ilObjTest,
                $IO['active_id'],
                $IO['pass_id'],
                $IO['is_user_output']
            );
        }

        $this->assertInstanceOf(ilTestPassResult::class, $ilTestPassResult);
        $this->assertEquals($IO['active_id'], $ilTestPassResult->getActiveId());
        $this->assertEquals($IO['pass_id'], $ilTestPassResult->getPass());
    }

    public static function getPassResultsForDataProvider(): array
    {
        return [
            'zero_zero_default' => [['active_id' => 0, 'pass_id' => 0, 'is_user_output' => null]],
            'zero_zero_true' => [['active_id' => 0, 'pass_id' => 0, 'is_user_output' => true]],
            'zero_zero_false' => [['active_id' => 0, 'pass_id' => 0, 'is_user_output' => false]],
            'one_zero_default' => [['active_id' => 1, 'pass_id' => 0, 'is_user_output' => null]],
            'one_zero_true' => [['active_id' => 1, 'pass_id' => 0, 'is_user_output' => true]],
            'one_zero_false' => [['active_id' => 1, 'pass_id' => 0, 'is_user_output' => false]],
            'zero_one_default' => [['active_id' => 0, 'pass_id' => 1, 'is_user_output' => null]],
            'zero_one_true' => [['active_id' => 0, 'pass_id' => 1, 'is_user_output' => true]],
            'zero_one_false' => [['active_id' => 0, 'pass_id' => 1, 'is_user_output' => false]],
            'one_one_default' => [['active_id' => 1, 'pass_id' => 1, 'is_user_output' => null]],
            'one_one_true' => [['active_id' => 1, 'pass_id' => 1, 'is_user_output' => true]],
            'one_one_false' => [['active_id' => 1, 'pass_id' => 1, 'is_user_output' => false]]
        ];
    }
}
