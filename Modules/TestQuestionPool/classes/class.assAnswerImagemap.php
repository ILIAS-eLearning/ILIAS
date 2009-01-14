<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. | 
   +----------------------------------------------------------------------------+
*/

include_once "./Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php";
include_once "./Modules/Test/classes/inc.AssessmentConstants.php";

/**
* Class for true/false or yes/no answers
* 
* ASS_AnswerImagemap is a class for true/false or yes/no answers used for example in multiple choice tests.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesTestQuestionPool
* @see ASS_AnswerSimple
* @see ASS_AnswerTrueFalse
*/
class ASS_AnswerImagemap extends ASS_AnswerBinaryState {
/**
* Coordinates of an area in image mape
*
* Coordinates of an area in image mape
*
* @var string
*/
  var $coords;

/**
* area of an imagemap
*
* area of an imagemap
*
* @var string
*/
  var $area;
  
/**
* ASS_AnswerImagemap constructor
* 
* The constructor takes possible arguments an creates an instance of the ASS_AnswerImagemap object.
*
* @param string $answertext A string defining the answer text
* @param double $points The number of points given for the selected answer
* @param boolean $correctness A boolean value indicating the correctness of the answer
* @param integer $order A nonnegative value representing a possible display or sort order
* @access public
*/
	function ASS_AnswerImagemap (
		$answertext = "",
		$points = 0.0,
		$order = 0,
		$coords = "",
		$area = "",
		$id = -1
	)
	{
		$this->ASS_AnswerBinaryState($answertext, $points, $order, 1, $id);
		$this->coords = $coords;
		$this->area = $area;
	}
  
  
/**
* Gets the coordinates of an image map
*
* @return string coords
* @access public
* @see $coords
*/
	function getCoords() {
		$this->coords = preg_replace("/\s/", "", $this->coords);
		return $this->coords;
	}


/**
* Sets the coordinates of an image map
*
* @param string $coords
* @access public
* @see $coords
*/
	function setCoords($coords="") {
		$coords = preg_replace("/\s/", "", $coords);
		$this->coords=$coords;
	}

/**
* Gets the area of an image map
*
* @return string area
* @access public
* @see $area
*/
	function getArea() {
		return $this->area;
	}


/**
* Sets the area of an image map
*
* @param string $area
* @access public
* @see $area
*/
	function setArea($area="") {
		$this->area=$area;
	}
}

?>