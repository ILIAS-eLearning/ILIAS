<?php
require_once("./Services/Style/System/classes/Less/class.ilSystemStyleLessItem.php");
/**
 * Capsules all data which is neither part of a variable or category structure in the less file. This is needed
 * to write the less file back to it's initial form
 *
 * //== NameOfCategory
 * //
 * //## Comment
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$
 *
 */
class ilSystemStyleLessComment extends ilSystemStyleLessItem
{

    /**
     * Random content of the less file being neither part of a variable or category
     *
     * @var string
     */
    protected $comment = "";

    /**
     * ilSystemStyleLessComment constructor.
     * @param string $comment
     */
    public function __construct($comment)
    {
        $this->setComment($comment);
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
        $this->comment  = str_replace("\n", '', $comment);
    }

    /**
     * This function will be needed to write the comment back to the less file and restore it's initial structure
     * in less.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getComment() . "\n";
    }
}
