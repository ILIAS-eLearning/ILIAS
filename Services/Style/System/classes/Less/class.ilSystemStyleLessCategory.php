<?php
require_once("./Services/Style/System/classes/Less/class.ilSystemStyleLessItem.php");

/**
 * Capsules data of a less category in the variables to less file. A less category has the following structure:
 *
 * //== NameOfCategory
 * //
 * //## Comment
 *
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$
 *
 */
class ilSystemStyleLessCategory extends ilSystemStyleLessItem
{
    /**
     * Name of the category
     *
     * @var string
     */
    protected $name = "";

    /**
     * Comment to describe what this category is about
     *
     * @var string
     */
    protected $comment = "";

    /**
     * ilSystemStyleLessCategory constructor.
     * @param string $name
     * @param string $comment
     */
    public function __construct($name, $comment = "")
    {
        $this->setName($name);
        $this->setComment($comment);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $name = str_replace(PHP_EOL, '', $name);
        $this->name = str_replace("\n", '', $name);
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $comment = str_replace(PHP_EOL, '', $comment);
        $this->comment = str_replace("\n", '', $comment);
    }

    /**
     * This function will be needed to write the category back to the less file and restore it's initial structure
     * in less.
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->getComment()) {
            return "//== " . $this->getName() . "\n//\n//## " . $this->getComment() . "\n";
        } else {
            return "//== " . $this->getName() . "\n//\n//##\n";
        }
    }
}
