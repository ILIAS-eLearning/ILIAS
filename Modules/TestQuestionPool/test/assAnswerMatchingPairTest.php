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
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assAnswerMatchingPairTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        chdir(dirname(__FILE__));
        chdir('../../../');
    }

    public function testAnswerMatchingPairInstantiation(): void
    {
        $term = new assAnswerMatchingTerm('test');
        $definition = new assAnswerMatchingDefinition('testing');
        $instance = new assAnswerMatchingPair($term, $definition, 0.0);
        $this->assertInstanceOf('assAnswerMatchingPair', $instance);
    }

    public function testAnswerMatchingPairMutation(): void
    {
        $term = new assAnswerMatchingTerm('Term');
        $definition = new assAnswerMatchingDefinition('Definition');
        $instance = new assAnswerMatchingPair($term, $definition, 2.1);

        $this->assertEquals('Term', $instance->getTerm()->getText());
        $this->assertEquals('Definition', $instance->getDefinition()->getText());
        $this->assertEquals(2.1, $instance->getPoints());

        $term = new assAnswerMatchingTerm('another Term');
        $definition = new assAnswerMatchingDefinition('another Definition');
        $instance = $instance
            ->withTerm($term)
            ->withDefinition($definition)
            ->withPoints(3.4);

        $this->assertEquals('another Term', $instance->getTerm()->getText());
        $this->assertEquals('another Definition', $instance->getDefinition()->getText());
        $this->assertEquals(3.4, $instance->getPoints());
    }
}
