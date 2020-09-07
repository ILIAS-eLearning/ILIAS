<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Class for matching question answers
*
* ASS_AnswerSimple is a class for matching question answers
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
*/
class ASS_AnswerMatching
{
    /**
    * Points for selected matching pair
    *
    * The number of points given for the selected matching pair
    *
    * @var double
    */
    public $points;

    /**
    * Picture or definition
    *
    * A picture (filename) or  definition which matches a term
    *
    * @var string
    */
    public $picture_or_definition;

    /**
    * Term index
    *
    * A nonnegative integer defining an unique id for the term
    *
    * @var integer
    */
    public $term_id;
  
    /**
    * Term index
    *
    * A nonnegative integer defining an unique id for the picture or definition
    *
    * @var integer
    */
    public $picture_or_definition_id;
  
    /**
    * ASS_AnswerMatching constructor
    *
    * The constructor takes possible arguments an creates an instance of the ASS_AnswerMatching object.
    *
    * @param string $answertext A string defining the answer text
    * @param double $points The number of points given for the selected answer
    * @param integer $order A nonnegative value representing a possible display or sort order
    * @param string $matchingtext A string defining the matching text for the answer text
    * @access public
    */
    public function __construct(
        $points = 0.0,
        $term_id = 0,
        $picture_or_definition = "",
        $picture_or_definition_id = 0
    ) {
        $this->term_id = $term_id;
        $this->picture_or_definition = $picture_or_definition;
        $this->picture_or_definition_id = $picture_or_definition_id;
        $this->points = $points;
    }

    /**
    * Gets the points
    *
    * Returns the points
    * @return double points
    * @access public
    * @see $points
    */
    public function getPoints()
    {
        return $this->points;
    }

    /**
    * Gets the term id
    *
    * Returns a nonnegative identifier for the term
    * @return integer order
    * @access public
    * @see $term_id
    */
    public function getTermId()
    {
        return $this->term_id;
    }

    /**
    * Gets the picture
    *
    * Returns the picture
    * @return string picture
    * @access public
    * @see $picture_or_definition
    */
    public function getPicture()
    {
        return $this->picture_or_definition;
    }

    /**
    * Gets the definition
    *
    * Returns the definition
    * @return string definition
    * @access public
    * @see $picture_or_definition
    */
    public function getDefinition()
    {
        return $this->picture_or_definition;
    }
  
    /**
    * Gets the picture identifier
    *
    * Returns the picture identifier
    * @return integer picture identifier
    * @access public
    * @see $picture_or_definition_id
    */
    public function getPictureId()
    {
        return $this->picture_or_definition_id;
    }

    /**
    * Gets the definition identifier
    *
    * Returns the definition identifier
    * @return integer definition identifier
    * @access public
    * @see $picture_or_definition_id
    */
    public function getDefinitionId()
    {
        return $this->picture_or_definition_id;
    }
  
    /**
    * Sets the term id
    *
    * Sets the nonnegative term identifier which can be used for sorting or displaying matching pairs
    *
    * @param integer $term_id A nonnegative integer
    * @access public
    * @see $term_id
    */
    public function setTermId($term_id = 0)
    {
        if ($term_id >= 0) {
            $this->term_id = $term_id;
        }
    }

    /**
    * Sets the picture id
    *
    * Sets the nonnegative picture identifier which can be used for sorting or displaying matching pairs
    *
    * @param integer $picture_id A nonnegative integer
    * @access public
    * @see $picture_or_definition_id
    */
    public function setPictureId($picture_id = 0)
    {
        if ($picture_id >= 0) {
            $this->picture_or_definition_id = $picture_id;
        }
    }

    /**
    * Sets the definition id
    *
    * Sets the nonnegative definition identifier which can be used for sorting or displaying matching pairs
    *
    * @param integer $definition_id A nonnegative integer
    * @access public
    * @see $picture_or_definition_id
    */
    public function setDefinitionId($definition_id = 0)
    {
        if ($definition_id >= 0) {
            $this->picture_or_definition_id = $definition_id;
        }
    }

    /**
    * Sets the picture
    *
    * Sets the picture
    *
    * @param string $picture Picture
    * @access public
    * @see $picture_or_definition
    */
    public function setPicture($picture = "")
    {
        $this->picture_or_definition = $picture;
    }

    /**
    * Sets the definition
    *
    * Sets the definition
    *
    * @param string $definition Definition
    * @access public
    * @see $picture_or_definition
    */
    public function setDefinition($definition = "")
    {
        $this->picture_or_definition = $definition;
    }


    /**
    * Sets the points
    *
    * Sets the points given for selecting the answer.
    *
    * @param double $points The points given for the answer
    * @access public
    * @see $points
    */
    public function setPoints($points = 0.0)
    {
        $this->points = $points;
    }
}
