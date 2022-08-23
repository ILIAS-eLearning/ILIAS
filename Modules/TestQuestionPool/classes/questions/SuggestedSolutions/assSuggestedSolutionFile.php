<?php declare(strict_types=1);

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

namespace ILIAS\TA\Questions;

/**
 * a suggested solution for file-contents
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class assSuggestedSolutionFile extends assQuestionSuggestedSolution
{
    const ARRAY_KEY_FILENAME = 'name';
    const ARRAY_KEY_TITLE = 'filename';
    const ARRAY_KEY_MIME = 'type';
    const ARRAY_KEY_SIZE = 'size';

    protected string $filename;
    protected string $mime;
    protected int $size = 0;
    protected string $title;

    public function __construct(
        int $id,
        int $question_id,
        int $subquestion_index,
        string $import_id,
        \DateTimeImmutable $last_update,
        string $type,
        string $value
    ) {
        parent::__construct($id, $question_id, $subquestion_index, $import_id, $last_update);
        $v = unserialize($value, []);

        $this->title = $v[self::ARRAY_KEY_TITLE] ?? '';
        $this->filename = $v[self::ARRAY_KEY_FILENAME] ?? '';
        $this->size = $v[self::ARRAY_KEY_SIZE] ?? 0;
        $this->mime = $v[self::ARRAY_KEY_MIME] ?? '';
    }

    public function getType() : string
    {
        return parent::TYPE_FILE;
    }

    public function getStorableValue() : string 
    {
        return serialize([
            self::ARRAY_KEY_FILENAME => $this->getFilename(),
            self::ARRAY_KEY_MIME => $this->getMime(),
            self::ARRAY_KEY_SIZE => $this->getSize(),
            self::ARRAY_KEY_TITLE => $this->getTitle()
        ]);
    }

    public function getTitle() : string
    {
        if($this->title) {
            return $this->title;
        }
        return $this->filename;
    }
    public function withTitle(string $title) : static
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function getMime() : string
    {
        return $this->mime;
    }
    public function withMime(string $mime) : static
    {
        $clone = clone $this;
        $clone->mime = $mime;
        return $clone;
    }

    public function getSize() : int
    {
        return $this->size;
    }
    public function withSize(int $size) : static
    {
        $clone = clone $this;
        $clone->size = $size;
        return $clone;
    }

    public function getFilename() : string
    {
        return $this->filename;
    }
    public function withFilename(string $filename) : static
    {
        $clone = clone $this;
        $clone->filename = $filename;
        return $clone;
    }

}
