<?php

declare(strict_types=1);

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
 */

use PHPUnit\Framework\TestCase;
use ILIAS\TA\Questions\assQuestionSuggestedSolutionsDatabaseRepository;
use ILIAS\TA\Questions\assQuestionSuggestedSolution;
use ILIAS\TA\Questions\assSuggestedSolutionText;
use ILIAS\TA\Questions\assSuggestedSolutionFile;
use ILIAS\TA\Questions\assSuggestedSolutionLink;

/**
 * test the suggested solution immutable(s)
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
*/
class assQuestionSuggestedSolutionRepoMock extends assQuestionSuggestedSolutionsDatabaseRepository
{
    public function __construct()
    {
    }
    public function getSolution(
        int $id,
        int $question_id,
        string $internal_link,
        string $import_id,
        int $subquestion_index,
        string $type,
        string $value,
        \DateTimeImmutable $last_update
    ): assQuestionSuggestedSolution {
        return $this->buildSuggestedSolution(
            $id,
            $question_id,
            $internal_link,
            $import_id,
            $subquestion_index,
            $type,
            $value,
            $last_update
        );
    }
}

class assQuestionSuggestedSolutionTest extends TestCase
{
    protected function setUp(): void
    {
        $this->repo = new assQuestionSuggestedSolutionRepoMock();
    }

    public function testSuggestedSolutionText(): assSuggestedSolutionText
    {
        $id = 123;
        $question_id = 321;
        $internal_link = '';
        $import_id = 'imported_xy';
        $subquestion_index = 0;
        $type = assQuestionSuggestedSolution::TYPE_TEXT;
        $value = 'some value' ;
        $last_update = new \DateTimeImmutable();

        $sugsol = $this->repo->getSolution(
            $id,
            $question_id,
            $internal_link,
            $import_id,
            $subquestion_index,
            $type,
            $value,
            $last_update
        );
        $this->assertInstanceOf(assQuestionSuggestedSolution::class, $sugsol);
        $this->assertInstanceOf(assSuggestedSolutionText::class, $sugsol);

        $this->assertEquals($id, $sugsol->getId());
        $this->assertEquals($question_id, $sugsol->getQuestionId());
        $this->assertEquals($import_id, $sugsol->getImportId());
        $this->assertEquals($subquestion_index, $sugsol->getSubquestionIndex());
        $this->assertEquals($last_update, $sugsol->getLastUpdate());
        $this->assertEquals($type, $sugsol->getType());
        $this->assertEquals($value, $sugsol->getValue());
        $this->assertEquals($value, $sugsol->getStorableValue());

        $this->assertTrue($sugsol->isOfTypeText());
        $this->assertFalse($sugsol->isOfTypeFile());
        $this->assertFalse($sugsol->isOfTypeLink());

        return $sugsol;
    }


    /**
     * @depends testSuggestedSolutionText
     */
    public function testSuggestedSolutionMutators(assQuestionSuggestedSolution $sugsol): void
    {
        $id = 654;
        $question_id = 456;
        $internal_link = './another/internal/link/';
        $import_id = '_my_import_id';
        $subquestion_index = 2;

        $value = 'some other value' ;
        $last_update = new \DateTimeImmutable();

        $sugsol = $sugsol
            ->withId($id)
            ->withQuestionId($question_id)
            ->withImportId($import_id)
            ->withSubquestionIndex($subquestion_index)
            ->withValue($value);

        $this->assertEquals($id, $sugsol->getId());
        $this->assertEquals($question_id, $sugsol->getQuestionId());
        $this->assertEquals($import_id, $sugsol->getImportId());
        $this->assertEquals($subquestion_index, $sugsol->getSubquestionIndex());
        $this->assertEquals($value, $sugsol->getValue());
        $this->assertNotEquals($last_update, $sugsol->getLastUpdate());
    }

    public function testSuggestedSolutionFile(): assSuggestedSolutionFile
    {
        $id = 123;
        $question_id = 321;
        $internal_link = '';
        $import_id = 'imported_xy';
        $subquestion_index = 0;
        $type = assQuestionSuggestedSolution::TYPE_FILE;

        $values = [
            'name' => 'something.jpg',
            'type' => 'image/jpeg',
            'size' => 120,
            'filename' => 'actually title of file'
        ];

        $last_update = new \DateTimeImmutable();

        $sugsol = $this->repo->getSolution(
            $id,
            $question_id,
            $internal_link,
            $import_id,
            $subquestion_index,
            $type,
            serialize($values),
            $last_update
        );
        $this->assertInstanceOf(assQuestionSuggestedSolution::class, $sugsol);
        $this->assertInstanceOf(assSuggestedSolutionFile::class, $sugsol);

        $this->assertEquals($values[$sugsol::ARRAY_KEY_TITLE], $sugsol->getTitle());
        $this->assertEquals($values[$sugsol::ARRAY_KEY_MIME], $sugsol->getMime());
        $this->assertEquals($values[$sugsol::ARRAY_KEY_SIZE], $sugsol->getSize());
        $this->assertEquals($values[$sugsol::ARRAY_KEY_FILENAME], $sugsol->getFilename());
        $this->assertEquals(serialize($values), $sugsol->getStorableValue());
        $this->assertTrue($sugsol->isOfTypeFile());
        $this->assertFalse($sugsol->isOfTypeText());
        $this->assertFalse($sugsol->isOfTypeLink());

        return $sugsol;
    }


    /**
     * @depends testSuggestedSolutionFile
     */
    public function testSuggestedSolutionMutatorsFile(assSuggestedSolutionFile $sugsol): void
    {
        $values = [
            'name' => 'somethingelse.ico',
            'type' => 'image/x-icon',
            'size' => 11,
            'filename' => ''
        ];

        $sugsol = $sugsol
            ->withTitle($values['filename'])
            ->withMime($values['type'])
            ->withSize($values['size'])
            ->withFilename($values['name']);

        $this->assertEquals($values['name'], $sugsol->getTitle());
        $this->assertEquals($values['name'], $sugsol->getFileName());
        $this->assertEquals($values['type'], $sugsol->getMime());
        $this->assertEquals($values['size'], $sugsol->getSize());

        $nu_title = 'another title';
        $this->assertEquals($nu_title, $sugsol->withTitle($nu_title)->getTitle());
    }
}
