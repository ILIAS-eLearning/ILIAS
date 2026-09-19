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

use ILIAS\Refinery\Encode\Group as EncodeGroup;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;

/**
* Unit tests
*
* @author Matheus Zych <mzych@databay.de>
*
* @ingroup components\ILIASTestQuestionPool
*/
class ilAssQuestionListTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private ilAssQuestionList $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new ilAssQuestionList(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(Refinery::class),
            $this->createMock(ilComponentRepository::class),
            $this->createMock(ilComponentFactory::class),
            null
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilAssQuestionList::class, $this->object);
    }

    public function testLoadUsesQuestionTypeAliasAndWritesTranslatedLabel(): void
    {
        $db = $this->createMock(ilDBInterface::class);
        $lng = $this->createMock(ilLanguage::class);
        $refinery = $this->createMock(Refinery::class);
        $component_repository = $this->createMock(ilComponentRepository::class);
        $component_factory = $this->createMock(ilComponentFactory::class);

        $encode_group = $this->createMock(EncodeGroup::class);
        $transformation = $this->createMock(Transformation::class);
        $transformation->method('transform')->willReturnArgument(0);
        $encode_group->method('htmlSpecialCharsAsEntities')->willReturn($transformation);
        $refinery->method('encode')->willReturn($encode_group);

        $lng->method('txt')->with('assSingleChoice')->willReturn('Single Choice');

        $statement = $this->createMock(ilDBStatement::class);
        $captured_query = '';
        $db->expects($this->once())
            ->method('query')
            ->willReturnCallback(static function (string $query) use (&$captured_query, $statement) {
                $captured_query = $query;
                return $statement;
            });
        $db->expects($this->exactly(2))
            ->method('fetchAssoc')
            ->with($statement)
            ->willReturnOnConsecutiveCalls(
                [
                    'question_id' => 42,
                    'obj_fi' => 7,
                    'title' => 'Question title',
                    'description' => 'Question description',
                    'author' => 'Author',
                    'lifecycle' => 'draft',
                    'points' => 1.0,
                    'complete' => 1,
                    'tstamp' => 1,
                    'created' => 1,
                    'question_type' => 'assSingleChoice',
                    'plugin' => false,
                    'plugin_name' => null,
                    'feedback' => 0,
                ],
                null
            );

        $list = new ilAssQuestionList(
            $db,
            $lng,
            $refinery,
            $component_repository,
            $component_factory,
            null
        );
        $list->setJoinObjectData(false);
        $list->load();

        $this->assertStringContainsString('qpl_qst_type.type_tag AS question_type', $captured_query);

        $row = $list->getDataArrayForQuestionId(42);
        $this->assertSame('Single Choice', $row['question_type']);
        $this->assertArrayNotHasKey('type_tag', $row);
    }
}
