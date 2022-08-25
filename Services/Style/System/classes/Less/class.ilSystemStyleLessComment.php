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

/**
 * Capsules all data which is neither part of a variable or category structure in the less file. This is needed
 * to write the less file back to it's initial form
 *
 * //== NameOfCategory
 * //
 * //## Comment
 */
class ilSystemStyleLessComment extends ilSystemStyleLessItem
{
    /**
     * Random content of the less file being neither part of a variable or category
     */
    protected string $comment = '';

    /**
     * ilSystemStyleLessComment constructor.
     */
    public function __construct(string $comment)
    {
        $this->setComment($comment);
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $comment = str_replace(PHP_EOL, '', $comment);
        $this->comment = str_replace("\n", '', $comment);
    }

    /**
     * This function will be needed to write the comment back to the less file and restore it's initial structure
     * in less.
     */
    public function __toString(): string
    {
        return $this->getComment() . "\n";
    }
}
