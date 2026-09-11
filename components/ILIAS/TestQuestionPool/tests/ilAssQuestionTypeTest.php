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

/**
* Unit tests
*
* @author Matheus Zych <mzych@databay.de>
*
* @ingroup components\ILIASTestQuestionPool
*/
class ilAssQuestionTypeTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private ilAssQuestionType $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new ilAssQuestionType();
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilAssQuestionType::class, $this->object);
    }

    public function testCompleteMissingPluginNameFillsEmptyPluginNameFromQuestionType(): void
    {
        $result = ilAssQuestionType::completeMissingPluginName([
            'plugin' => true,
            'plugin_name' => '',
            'question_type' => 'assExamplePluginQuestion',
        ]);

        $this->assertSame('assExamplePluginQuestion', $result['plugin_name']);
    }

    public function testCompleteMissingPluginNameFillsNullPluginNameFromQuestionType(): void
    {
        $result = ilAssQuestionType::completeMissingPluginName([
            'plugin' => true,
            'plugin_name' => null,
            'question_type' => 'assExamplePluginQuestion',
        ]);

        $this->assertSame('assExamplePluginQuestion', $result['plugin_name']);
    }

    public function testCompleteMissingPluginNameKeepsExistingPluginName(): void
    {
        $result = ilAssQuestionType::completeMissingPluginName([
            'plugin' => true,
            'plugin_name' => 'ExampleQuestionPlugin',
            'question_type' => 'assExamplePluginQuestion',
        ]);

        $this->assertSame('ExampleQuestionPlugin', $result['plugin_name']);
    }

    public function testCompleteMissingPluginNameDoesNotTouchNonPluginData(): void
    {
        $result = ilAssQuestionType::completeMissingPluginName([
            'plugin' => false,
            'plugin_name' => null,
            'question_type' => 'assSingleChoice',
        ]);

        $this->assertNull($result['plugin_name']);
        $this->assertSame('assSingleChoice', $result['question_type']);
    }
}
