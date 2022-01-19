<?php

declare(strict_types=1);

/**
 * Capsules data of a less category in the variables to less file. A less category has the following structure:
 *
 * //== NameOfCategory
 * //
 * //## Comment
 */
class ilSystemStyleLessCategory extends ilSystemStyleLessItem
{
    /**
     * Name of the category
     */
    protected string $name = '';

    /**
     * Comment to describe what this category is about
     */
    protected string $comment = '';

    public function __construct(string $name, string $comment = '')
    {
        $this->setName($name);
        $this->setComment($comment);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $name = str_replace(PHP_EOL, '', $name);
        $this->name = str_replace('\n', '', $name);
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $comment = str_replace(PHP_EOL, '', $comment);
        $this->comment = str_replace('\n', '', $comment);
    }

    /**
     * This function will be needed to write the category back to the less file and restore it's initial structure
     * in less.
     */
    public function __toString(): string
    {
        if ($this->getComment()) {
            return '//== ' . $this->getName() . '\n//\n//## ' . $this->getComment() . '\n';
        } else {
            return '//== ' . $this->getName() . '\n//\n//##\n';
        }
    }
}
