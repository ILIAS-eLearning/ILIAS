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

include_once "./assessment/classes/class.assAnswerMultipleResponse.php";
include_once "./assessment/classes/inc.AssessmentConstants.php";

/**
* ASS_AnswerBinaryStateImage is a class for answers with a binary state 
* indicator (checked/unchecked, set/unset) and an image file
* 
* ASS_AnswerBinaryStateImage is a class for answers with a binary state 
* indicator (checked/unchecked, set/unset) and an image file
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @module   class.assAnswerBinaryState.php
* @modulegroup   Assessment
* @see ASS_AnswerSimple
*/
class ASS_AnswerMultipleResponseImage extends ASS_AnswerMultipleResponse {
/**
* Image filename
* 
* Image filename
*
* @var string
*/
  var $image;

/**
* ASS_AnswerMultipleResponse constructor
*
* The constructor takes possible arguments an creates an instance of the ASS_AnswerMultipleResponse object.
*
* @param string $answertext A string defining the answer text
* @param double $points The number of points given for the selected answer
* @param integer $order A nonnegative value representing a possible display or sort order
* @param double $points_unchecked The points when the answer is not checked
* @param string $a_image The image filename
* @access public
*/
  function ASS_AnswerMultipleResponseImage (
    $answertext = "",
    $points_checked = 0.0,
    $order = 0,
    $points_unchecked = 0,
		$a_image = "",
		$id = -1
  )
  {
    $this->ASS_AnswerMultipleResponse($answertext, $points_checked, $order, $points_unchecked, $id);
    $this->image = $a_image;
  }


/**
* Gets the image filename
*
* Returns the image filename

* @return string The image filename
* @access public
* @see $image
*/
  function getImage() 
	{
    return $this->image;
  }

/**
* Sets the image filename
*
* Sets the image filename
*
* @param string $a_image The image filename
* @access public
* @see $image
*/
  function setImage($a_image = 0)
  {
    $this->image = $a_image;
  }
}

?>
