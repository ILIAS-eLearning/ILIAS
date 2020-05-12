<?php
require_once("./Services/Style/System/classes/Less/class.ilSystemStyleLessItem.php");

/***
 * Capsules data of a less variable in the variables to less file. A less variable has the following structure:
 *
 * //** Comment to describe the variable
 * @variable:   value;
 *
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$
 *
 */
class ilSystemStyleLessVariable extends ilSystemStyleLessItem
{

    /**
     * Name of the variable
     *
     * @var string
     */
    protected $name = "";

    /**
     * Value of the variable as set in the less file
     *
     * @var string
     */
    protected $value = "";

    /**
     * Comment to the variable as in the less file
     *
     * @var string
     */
    protected $comment = "";

    /**
     * Less Category which encloses this variable
     * @var string
     */
    protected $category_name = "";

    /**
     * Set references to other variables that are used by this exact variable
     *
     * @var array
     */
    protected $references = array();

    /**
     * ilSystemStyleLessVariable constructor.
     * @param $name
     * @param $value
     * @param $comment
     * @param $category_name
     * @param $references
     */
    public function __construct($name, $value, $comment, $category_name, $references)
    {
        $this->setName($name);
        $this->setValue($value);
        $this->setCategoryName($category_name);
        $this->setComment($comment);
        $this->setReferences($references);
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
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        if ($this->getName() == "icon-font-path") {
            if ($value[0] != "\"") {
                $value = "\"" . $value;
                ;
            }
            if (substr($value, -1, 1) != "\"") {
                $value .= "\"";
            }

            if ($value == "\"../../libs/bower/bower_components/bootstrap/fonts/\"") {
                $value = "\"../../../../libs/bower/bower_components/bootstrap/fonts/\"";
            }
        }

        $value = str_replace(PHP_EOL, '', $value);
        $this->value = str_replace("\n", '', $value);
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
     * @return string
     */
    public function getCategoryName()
    {
        return $this->category_name;
    }

    /**
     * @param string $category_name
     */
    public function setCategoryName($category_name)
    {
        $this->category_name = $category_name;
    }

    /**
     * @return array
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * @param array $references
     */
    public function setReferences($references)
    {
        $this->references = $references;
    }


    /**
     * This function will be needed to write the variable back to the less file and restore it's initial structure
     * in less.
     *
     * @return string
     */
    public function __toString()
    {
        $content = "";
        if ($this->getComment()) {
            $content .= "//** " . $this->getComment() . "\n";
        }
        $content .= "@" . $this->getName() . ":\t\t" . $this->getValue() . ";\n";
        return $content;
    }
}
