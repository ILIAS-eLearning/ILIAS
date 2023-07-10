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

/***
 * Capsules data of a less variable in the variables to less file. A less variable has the following structure:
 * //** Comment to describe the variable
 * @variable:   value;
 */
class ilSystemStyleLessVariable extends ilSystemStyleLessItem
{
    /**
     * Name of the variable
     */
    protected string $name = '';

    /**
     * Value of the variable as set in the less file
     */
    protected string $value = '';

    /**
     * Comment to the variable as in the less file
     */
    protected string $comment = '';

    /**
     * Less Category which encloses this variable
     */
    protected string $category_name = '';

    /**
     * Set references to other variables that are used by this exact variable
     */
    protected array $references = [];

    public function __construct(
        string $name,
        string $value,
        string $comment,
        string $category_name,
        array $references = []
    ) {
        $this->setName($name);
        $this->setValue($value);
        $this->setCategoryName($category_name);
        $this->setComment($comment);
        $this->setReferences($references);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        if ($this->getName() == 'il-icon-font-path') {
            if ($value[0] != "\"") {
                $value = "\"" . $value;
            }
            if (substr($value, -1, 1) != "\"") {
                $value .= "\"";
            }

            if ($value == "\"../../node_modules/bootstrap/fonts/\"") {
                $value = "\"../../../../node_modules/bootstrap/fonts/\"";
            }
        }

        $value = str_replace(PHP_EOL, '', $value);
        $this->value = str_replace("\n", '', $value);
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

    public function getCategoryName(): string
    {
        return $this->category_name;
    }

    public function setCategoryName(string $category_name): void
    {
        $this->category_name = $category_name;
    }

    public function getReferences(): array
    {
        return $this->references;
    }

    public function setReferences(array $references): void
    {
        $this->references = $references;
    }

    /**
     * This function will be needed to write the variable back to the less file and restore it's initial structure
     * in less.
     */
    public function __toString(): string
    {
        $content = '';
        if ($this->getComment()) {
            $content .= '//** ' . $this->getComment() . "\n";
        }
        $content .= '@' . $this->getName() . ":\t\t" . $this->getValue() . ";\n";
        return $content;
    }
}
