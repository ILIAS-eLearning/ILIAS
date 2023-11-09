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
 * Capsules all data which is neither part of a variable or category structure in the Scss file. This is needed
 * to write the Scss file back to it's initial form
 *
 * //== NameOfCategory
 * //
 * //## Comment
 */
class ilSystemStyleScssComment extends ilSystemStyleScssItem
{
    /**
     * Random content of the Scss file being neither part of a variable or category
     */
    protected string $comment = '';

    /**
     * ilSystemStyleScssComment constructor.
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
     * This function will be needed to write the comment back to the Scss file and restore it's initial structure
     * in Scss.
     */
    public function __toString(): string
    {
        return $this->getComment() . "\n";
    }
}
