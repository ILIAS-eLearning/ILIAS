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

require_once "class.assQuestion.php";
require_once "class.assAnswerImagemap.php";
require_once "class.assImagemapArea.php";

define("IMAGEMAP_UPLOAD", "0");
define("IMAGEMAP_CREATE", "1");

define("RECTANGLE", "0");
define("CIRCLE", "1");
define("POLYGON", "2");

/**
* Class for image map questions
*
* ASS_ImagemapQuestion is a class for imagemap question.
*
* @author		Muzaffar Altaf <maltaf@tzi.de>
* @version	$Id$
* @module   class.assImagemapQuestion.php
* @modulegroup   Assessment
*/
class ASS_ImagemapQuestion extends ASS_Question {

/**
* The imagemap_Question containing the question
*
* The imagemap_Question containing the question.
*
* @var string
*/
  var $question;

/**
* The possible answers of the imagemap question
*
* $answers is an array of the predefined answers of the imagemap question
*
* @var array
*/
  var $answers;

/**
* Points for solving the imagemap question
*
* Enter the number of points the user gets when he/she solves the imagemap
* question. This value overrides the point values of single answers when set different
* from zero.
*
* @var double
*/
  var $points;

/**
* The imagemap file containing the name of imagemap file
*
* The imagemap file containing the name of imagemap file
*
* @var string
*/
  var $imagemap_filename;

/**
* The image file containing the name of image file
*
* The image file containing the name of image file
*
* @var string
*/
  var $image_filename;

/**
* The variable containing contents of an imagemap file
*
* The variable containing contents of an imagemap file
*
* @var string
*/
  var $imagemap_contents;

/**
* The variable about information whether imagemap is created by imagemap editor
*
* The variable about information whether imagemap is created by imagemap editor
*
* @var boolean
*/
  var $imagemap_source;

/**
* The variable containing imagemap area type
*
* The variable containing imagemap area type
*
* @var integer
*/
  var $area_type;

/**
* The variable containing information about the number of times, image is clicked
*
* The variable containing information about the number of times, image is clicked
*
* @var integer
*/
  var $image_clicked_count;

/**
* The variable containing information about the coordinates when creating imagemap
*
* The variable containing information about the coordinates when creating imagemap
*
* @var string
*/
  var $image_coords;

/**
* The variable containing an instance of imagemap area
*
* The variable containing an instance of imagemap area
*
* @var object
*/
  var $imagemap_area;

/**
* image map work copy image
*
* image map work copy image
*
* @var object
*/
var $map_image;

/**
* map area line color 1
*
* map area line color 1
*
* @var object
*/
var $color1;

/**
* map area line color 2
*
* map area line color 2
*
* @var object
*/
var $color2;


/**
* ASS_ImagemapQuestion constructor
*
* The constructor takes possible arguments an creates an instance of the ASS_ImagemapQuestion object.
*
* @param string $title A title string to describe the question
* @param string $comment A comment string to describe the question
* @param string $author A string containing the name of the questions author
* @param integer $owner A numerical ID to identify the owner/creator
* @param string $imagemap_file The imagemap file name of the imagemap question
* @param string $image_file The image file name of the imagemap question
* @param string $question The question string of the imagemap question
* @param string $materials An uri to additional materials
* @access public
*/
  function ASS_ImagemapQuestion(
    $title = "",
    $comment = "",
    $author = "",
    $owner = -1,
    $question = "",
    $imagemap_filename = "",
    $image_filename = ""
  )
  {
    $this->ASS_Question($title, $comment, $author, $owner);
    $this->question = $question;
    $this->imagemap_filename = $imagemap_filename;
    $this->image_filename = $image_filename;
    $this->answers = array();
		$this->points = $points;

	$this->imagemap_source = IMAGEMAP_UPLOAD;
	$this->area_type = RECTANGLE;
	$this->image_clicked_count = 0;
	$this->image_coords = "";
	$this->imagemap_area =& new ASS_ImagemapArea();

  }

/**
* Returns true, if a imagemap question is complete for use
*
* Returns true, if a imagemap question is complete for use
*
* @return boolean True, if the imagemap question is complete for use, otherwise false
* @access public
*/
	function isComplete()
	{
		if (($this->title) and ($this->author) and ($this->question) and ($this->image_filename) and (count($this->answers)))
		{
			return true;
		}
			else
		{
			return false;
		}
	}

/**
* Saves a ASS_ImagemapQuestion object to a database
*
* Saves a ASS_ImagemapQuestion object to a database (experimental)
*
* @param object $db A pear DB object
* @access public
*/
  function save_to_db()
  {
    global $ilias;
		$complete = 0;
		if ($this->isComplete()) {
			$complete = 1;
		}

    $db = & $ilias->db->db;

    $estw_time = $this->get_estimated_working_time();
    $estw_time = sprintf("%02d:%02d:%02d", $estw_time['h'], $estw_time['m'], $estw_time['s']);

    if ($this->id == -1) {
      // Neuen Datensatz schreiben
      $now = getdate();
      $question_type = 6;
      $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
      $query = sprintf("INSERT INTO qpl_questions (question_id, question_type_fi, ref_fi, title, comment, author, owner, question_text, working_time, points, imagemap_file, image_file, imagemap_source, complete, created, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
        $db->quote($question_type),
        $db->quote($this->ref_id),
        $db->quote($this->title),
        $db->quote($this->comment),
        $db->quote($this->author),
        $db->quote($this->owner),
        $db->quote($this->question),
        $db->quote($estw_time),
        $db->quote($this->points),
        $db->quote($this->imagemap_filename),
        $db->quote($this->image_filename),
        $db->quote($this->imagemap_source),
				$db->quote("$complete"),
        $db->quote($created)
      );
      $result = $db->query($query);
      if ($result == DB_OK) {
        $this->id = $this->ilias->db->getLastInsertId();
        // Falls die Frage in einen Test eingefügt werden soll, auch diese Verbindung erstellen
        if ($this->get_test_id() > 0) {
          $this->insert_into_test($this->get_test_id());
        }
      }
    } else {
      // Vorhandenen Datensatz aktualisieren
      $query = sprintf("UPDATE qpl_questions SET title = %s, comment = %s, author = %s, question_text = %s, working_time = %s, points = %s, imagemap_file = %s, image_file = %s, imagemap_source = %s, complete = %s WHERE question_id = %s",
        $db->quote($this->title),
        $db->quote($this->comment),
        $db->quote($this->author),
        $db->quote($this->question),
        $db->quote($estw_time),
        $db->quote($this->points),
        $db->quote($this->imagemap_filename),
        $db->quote($this->image_filename),
        $db->quote($this->imagemap_source),
				$db->quote("$complete"),
        $db->quote($this->id)
      );
      $result = $db->query($query);
    }
    if ($result == DB_OK) {
      // saving material uris in the database
      $this->save_materials_to_db();
      // Antworten schreiben
      // alte Antworten löschen
      $query = sprintf("DELETE FROM qpl_answers WHERE question_fi = %s",
        $db->quote($this->id)
      );
      $result = $db->query($query);
      // Anworten wegschreiben
      foreach ($this->answers as $key => $value) {
        $answer_obj = $this->answers[$key];
        //print "id:".$this->id." answer tex:".$answer_obj->get_answertext()." answer_obj->get_order():".$answer_obj->get_order()." answer_obj->get_coords():".$answer_obj->get_coords()." answer_obj->get_area():".$answer_obj->get_area();
        $query = sprintf("INSERT INTO qpl_answers (answer_id, question_fi, answertext, points, aorder, correctness, coords, area, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, NULL)",
          $db->quote($this->id),
          $db->quote($answer_obj->get_answertext()),
          $db->quote($answer_obj->get_points()),
          $db->quote($answer_obj->get_order()),
          $db->quote($answer_obj->get_correctness()),
          $db->quote($answer_obj->get_coords()),
          $db->quote($answer_obj->get_area())
        );
        $answer_result = $db->query($query);
      }
    }
  }

/**
* Loads a ASS_ImagemapQuestion object from a database
*
* Loads a ASS_ImagemapQuestion object from a database (experimental)
*
* @param object $db A pear DB object
* @param integer $question_id A unique key which defines the multiple choice test in the database
* @access public
*/
  function load_from_db($question_id)
  {
    global $ilias;

    $db = & $ilias->db->db;
    $query = sprintf("SELECT * FROM qpl_questions WHERE question_id = %s",
      $db->quote($question_id)
    );
    $result = $db->query($query);
    if (strcmp(get_class($result), db_result) == 0) {
      if ($result->numRows() == 1) {
        $data = $result->fetchRow(DB_FETCHMODE_OBJECT);
        $this->id = $question_id;
				$this->ref_id = $data->ref_fi;
        $this->title = $data->title;
        $this->comment = $data->comment;
        $this->author = $data->author;
        $this->owner = $data->owner;
        $this->question = $data->question_text;
        $this->imagemap_filename = $data->imagemap_file;
        $this->image_filename = $data->image_file;
        $this->imagemap_source = $data->imagemap_source;
        $this->points = $data->points;
        $this->set_estimated_working_time(substr($data->working_time, 0, 2), substr($data->working_time, 3, 2), substr($data->working_time, 6, 2));
      }
      // loads materials uris from database
      $this->load_material_from_db($question_id);
      $query = sprintf("SELECT * FROM qpl_answers WHERE question_fi = %s ORDER BY aorder ASC",
        $db->quote($question_id)
      );
      $result = $db->query($query);
      if (strcmp(get_class($result), db_result) == 0) {
        while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
          array_push($this->answers, new ASS_AnswerImagemap($data->answertext, $data->points, $data->aorder, $data->correctness, $data->coords, $data->area));
        }
      }
    }
  }

/**
* Gets the imagemap question
*
* Gets the question string of the ASS_ImagemapQuestion object
*
* @return string The question string of the ASS_ImagemapQuestion object
* @access public
* @see $question
*/
  function get_question() {
    return $this->question;
  }

/**
* Sets the imagemap question
*
* Sets the question string of the ASS_ImagemapQuestion object
*
* @param string $question A string containing the imagemap question
* @access public
* @see $question
*/
  function set_question($question = "") {
    $this->question = $question;
  }

/**
* Gets the imagemap file name
*
* Gets the imagemap file name
*
* @return string The imagemap file of the ASS_ImagemapQuestion object
* @access public
* @see $imagemap_filename
*/
  function get_imagemap_filename() {
    return $this->imagemap_filename;
  }

/**
* Sets the imagemap file name
*
* Sets the imagemap file name
*
* @param string $imagemap_file.
* @access public
* @see $imagemap_filename
*/
  function set_imagemap_filename($imagemap_filename, $imagemap_tempfilename = "") {
    if (!empty($imagemap_filename)) {
      $this->imagemap_filename = $imagemap_filename;
    }
    if (!empty($imagemap_tempfilename)) {
 	    $fp = fopen($imagemap_tempfilename, "r");
 	    $contents = fread($fp, filesize($imagemap_tempfilename));
      fclose($fp);
			$this->flush_answers();
			if (preg_match_all("/<area(.+)>/siU", $contents, $matches)) {
		  	for ($i=0; $i< count($matches[1]); $i++) {
		    	preg_match("/alt\s*=\s*\"(.+)\"\s*/siU", $matches[1][$i], $alt);
		    	preg_match("/coords\s*=\s*\"(.+)\"\s*/siU", $matches[1][$i], $coords);
		    	preg_match("/shape\s*=\s*\"(.+)\"\s*/siU", $matches[1][$i], $shape);
					$this->add_answer($alt[1], 0.0, FALSE, $i, $coords[1], $shape[1]);
		  	}
			}
    }
	}

/**
* Gets the image file name
*
* Gets the image file name
*
* @return string The image file name of the ASS_ImagemapQuestion object
* @access public
* @see $image_filename
*/
  function get_image_filename() {
    return $this->image_filename;
  }

/**
* Sets the image file name
*
* Sets the image file name
*
* @param string $image_file name.
* @access public
* @see $image_filename
*/
  function set_image_filename($image_filename, $image_tempfilename = "") {

    if (!empty($image_filename)) {
      $this->image_filename = $image_filename;
    }
		if (!empty($image_tempfilename)) {
			$imagepath = $this->get_image_path();
			if (!file_exists($imagepath)) {
				ilUtil::makeDirParents($imagepath);
			}
			if (!move_uploaded_file($image_tempfilename, $imagepath . $image_filename)) {
				print "image not uploaded!!!! ";
			} else {
				// create thumbnail file
				$size = 100;
				$thumbpath = $imagepath . $image_filename . "." . "thumb.jpg";
				$convert_cmd = ilUtil::getConvertCmd() . " $imagepath$image_filename -resize $sizex$size $thumbpath";
				system($convert_cmd);
			}
		}
  }

/**
* Gets the imagemap file contents
*
* Gets the imagemap file contents
*
* @return string The imagemap file contents of the ASS_ImagemapQuestion object
* @access public
* @see $imagemap_contents
*/
  function get_imagemap_contents($href = "#") {
		$imagemap_contents = "<map name=\"".$this->title."\"> ";
		for ($i = 0; $i < count($this->answers); $i++) {
	 		$imagemap_contents .= "<area alt=\"".$this->answers[$i]->get_answertext()."\" ";
	 		$imagemap_contents .= "shape=\"".$this->answers[$i]->get_area()."\" ";
	 		$imagemap_contents .= "coords=\"".$this->answers[$i]->get_coords()."\" ";
	 		$imagemap_contents .= "href=\"$href&selimage=" . $this->answers[$i]->get_order() . "\" /> ";
		}
		$imagemap_contents .= "</map>";
    return $imagemap_contents;
  }

/**
* Gets the points
*
* Gets the points for solving the question of the ASS_ImagemapQuestion object
*
* @return double The points for solving the imagemap question
* @access public
* @see $points
*/
  function get_points() {
    return $this->points;
  }

/**
* Sets the points
*
* Sets the points for solving the question of the ASS_ImagemapQuestion object
*
* @param points double The points for solving the imagemap question
* @access public
* @see $points
*/
  function set_points($points = 0.0) {
    $this->points = $points;
  }
/**
* Gets the information whether imagemap is created or uploaded
*
* Gets the information whether imagemap is created or uploaded
*
* @return boolean
* @access public
* @see $question
*/
  function get_imagemap_source() {
    return $this->imagemap_source;
  }

/**
* Sets the information whether imagemap is created or uploaded
*
* Sets the information whether imagemap is created or uploaded
*
* @param boolean
* @access public
* @see $question
*/
  function set_imagemap_source($imagemap_source = IMAGEMAP_UPLOAD) {
    $this->imagemap_source = $imagemap_source;
  }

/**
* Gets the imagemap area type
*
* Gets the imagemap area type
*
* @return integer
* @access public
* @see $question
*/
  function get_area_type() {
    return $this->area_type;
  }

/**
* Sets the imagemap area type
*
* Sets the imagemap area type
*
* @param integer
* @access public
* @see $question
*/
  function set_area_type($area_type = RECTANGLE) {
    $this->area_type = $area_type;
  }

/**
* Gets the area name
*
* Sets the area name
*
* @param string
* @access public
* @see $question
*/
  function get_area_name() {
	switch($this->area_type)
	{
		case RECTANGLE:
		return "RECT";
		break;

		case CIRCLE:
		return "CIRCLE";
		break;

		case POLYGON:
		return "POLY";
		break;
	}
 }

/**
* Gets the image clicked count
*
* Gets the image clicked count
*
* @return integer
* @access public
* @see $question
*/
  function get_image_clicked_count() {
    return $this->image_clicked_count;
  }

/**
* Sets the image clicked count
*
* Sets the image clicked count
*
* @param integer
* @access public
* @see $question
*/
  function set_image_clicked_count($image_clicked_count) {
    $this->image_clicked_count = $image_clicked_count;
  }
/**
* Gets the image coordinates
*
* Gets the image coordinates
*
* @return string
* @access public
* @see $question
*/
  function get_image_coords() {
    return $this->image_coords;
  }

/**
* Sets the image coordinates
*
* Sets the image coordinates
*
* @param string
* @access public
* @see $question
*/
  function set_image_coords($image_coords) {
    $this->image_coords = $this->image_coords.$image_coords;
  }
/**
* Sets the image coordinates
*
* Sets the image coordinates
*
* @param string
* @access public
* @see $question
*/
  function add_answer_from_image_editor() {
    $this->add_answer("area_".$this->get_answer_count(), 0.0, FALSE, $this->get_answer_count(), $this->get_image_coords(),$this->get_area_name());
    $this->imagemap_area->setCoords($this->image_coords);
    $this->imagemap_area->setShape($this->get_area_name());
    $this->imagemap_area->setTitle("area_".$this->get_answer_count());

    $this->buildMapWorkImage();
    $this->imagemap_area->draw($this->getMapWorkImage(), $this->color1, $this->color2);
    $this->saveMapWorkImage();

    $this->set_image_clicked_count(3);
  }

/**
* get image type of image map work copy
*/
function getMapWorkCopyType()
{
	$ext = explode(".", $this->get_image_filename());
	return ilUtil::getGDSupportedImageType($ext[count($ext) - 1]);
}

/**
* build image map work image
*/
function buildMapWorkImage()
{
	$im_type = strtolower($this->getMapWorkCopyType());
	$imgcopy = $this->get_image_path().$this->get_image_filename();

	switch ($im_type)
	{
		case "gif":
			$this->map_image = ImageCreateFromGIF($imgcopy);
			break;

		case "jpg":
			$this->map_image = ImageCreateFromJPEG($imgcopy);
			break;

		case "png":
			$this->map_image = ImageCreateFromPNG($imgcopy);
			break;
	}

	// try to allocate black and white as color. if this is not possible, get the closest colors
	if (imagecolorstotal($this->map_image) > 250)
	{
		$this->color1 = imagecolorclosest($this->map_image, 0, 0, 0);
		$this->color2 = imagecolorclosest($this->map_image, 255, 255, 255);
	}
	else
	{
		$this->color1 = imagecolorallocate($this->map_image, 0, 0, 0);
		$this->color2 = imagecolorallocate($this->map_image, 255, 255, 255);
	}
}

/**
* save image map work image
*/
function saveMapWorkImage()
{
	$im_type = strtolower($this->getMapWorkCopyType());
	// save image work-copy and free memory
	switch ($im_type)
	{
		case "gif":
			ImageGIF($this->map_image, $this->get_image_path().$this->get_image_filename());
			break;

		case "jpg":
			ImageJPEG($this->map_image, $this->get_image_path().$this->get_image_filename());
			break;

		case "png":
			ImagePNG($this->map_image, $this->get_image_path().$this->get_image_filename());
			break;
	}

	ImageDestroy($this->map_image);
}

/**
* get image map work image
*/
function &getMapWorkImage()
{
	return $this->map_image;
}


/**
* Adds a possible answer for a imagemap question
*
* Adds a possible answer for a imagemap question. A ASS_AnswerImagemap object will be
* created and assigned to the array $this->answers.
*
* @param string $answertext The answer text
* @param double $points The points for selecting the answer (even negative points can be used)
* @param boolean $correctness Defines the answer as correct (TRUE) or incorrect (FALSE)
* @param integer $order A possible display order of the answer
* @access public
* @see $answers
* @see ASS_AnswerImagemap
*/
  function add_answer(
    $answertext = "",
    $points = 0.0,
    $correctness = FALSE,
    $order = 0,
    $coords="",
    $area=""
  )
  {
    if (array_key_exists($order, $this->answers)) {
      // Antwort einfügen
      $answer = new ASS_AnswerImagemap($answertext, $points, $found, $correctness, $coords, $area);
			for ($i = count($this->answers) - 1; $i >= $order; $i--) {
				$this->answers[$i+1] = $this->answers[$i];
				$this->answers[$i+1]->set_order($i+1);
			}
			$this->answers[$order] = $answer;
    } else {
      // Anwort anhängen
      $answer = new ASS_AnswerImagemap($answertext, $points, count($this->answers), $correctness, $coords, $area);
      array_push($this->answers, $answer);
    }
  }

/**
* Returns the number of answers
*
* Returns the number of answers
*
* @return integer The number of answers of the multiple choice question
* @access public
* @see $answers
*/
  function get_answer_count() {
    return count($this->answers);
  }

/**
* Returns an answer
*
* Returns an answer with a given index. The index of the first
* answer is 0, the index of the second answer is 1 and so on.
*
* @param integer $index A nonnegative index of the n-th answer
* @return object ASS_AnswerImagemap-Object containing the answer
* @access public
* @see $answers
*/
  function get_answer($index = 0) {
    if ($index < 0) return NULL;
    if (count($this->answers) < 1) return NULL;
    if ($index >= count($this->answers)) return NULL;
    return $this->answers[$index];
  }

/**
* Deletes an answer
*
* Deletes an answer with a given index. The index of the first
* answer is 0, the index of the second answer is 1 and so on.
*
* @param integer $index A nonnegative index of the n-th answer
* @access public
* @see $answers
*/
  function delete_answer($index = 0) {
    if ($index < 0) return;
    if (count($this->answers) < 1) return;
    if ($index >= count($this->answers)) return;
    unset($this->answers[$index]);
    $this->answers = array_values($this->answers);
    for ($i = 0; $i < count($this->answers); $i++) {
      if ($this->answers[$i]->get_order() > $index) {
        $this->answers[$i]->set_order($i);
      }
    }
  }

/**
* Deletes all answers
*
* Deletes all answers
*
* @access public
* @see $answers
*/
  function flush_answers() {
    $this->answers = array();
  }

/**
* Returns the maximum points, a learner can reach answering the question
*
* Returns the maximum points, a learner can reach answering the question
*
* @access public
* @see $points
*/
  function get_maximum_points() {
		$points = 0;
		foreach ($this->answers as $key => $value) {
			if ($value->is_true()) {
				$points += $value->get_points();
			}
		}
		return $points;
  }

/**
* Returns the points, a learner has reached answering the question
*
* Returns the points, a learner has reached answering the question
*
* @param integer $user_id The database ID of the learner
* @param integer $test_id The database Id of the test containing the question
* @access public
*/
  function get_reached_points($user_id, $test_id) {
    $found_values = array();
    $query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
      $this->ilias->db->db->quote($user_id),
      $this->ilias->db->db->quote($test_id),
      $this->ilias->db->db->quote($this->get_id())
    );
    $result = $this->ilias->db->query($query);
    while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
      array_push($found_values, $data->value1);
    }
    $points = 0;
    foreach ($found_values as $key => $value) {
      if (strlen($value) > 0) {
        if ($this->answers[$value]->is_true()) {
          $points += $this->answers[$value]->get_points();
        }
      }
    }
    return $points;
  }

/**
* Returns the evaluation data, a learner has entered to answer the question
*
* Returns the evaluation data, a learner has entered to answer the question
*
* @param integer $user_id The database ID of the learner
* @param integer $test_id The database Id of the test containing the question
* @access public
*/
  function get_reached_information($user_id, $test_id) {
    $found_values = array();
    $query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
      $this->ilias->db->db->quote($user_id),
      $this->ilias->db->db->quote($test_id),
      $this->ilias->db->db->quote($this->get_id())
    );
    $result = $this->ilias->db->query($query);
    while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
      array_push($found_values, $data->value1);
    }
    $counter = 1;
		$user_result = array();
    foreach ($found_values as $key => $value) {
			$solution = array(
				"order" => "$counter",
				"points" => 0,
				"true" => 0,
				"value" => ""
			);
      if (strlen($value) > 0) {
				$solution["value"] = $this->answers[$value]->get_answertext();
        if ($this->answers[$value]->is_true()) {
					$solution["points"] = $this->answers[$value]->get_points();
					$solution["true"] = 1;
        }
      }
			$counter++;
			array_push($user_result, $solution);
    }
    return $user_result;
  }

/**
* Saves the learners input of the question to the database
*
* Saves the learners input of the question to the database
*
* @param integer $test_id The database id of the test containing this question
* @access public
* @see $answers
*/
  function save_working_data($test_id, $limit_to = LIMIT_NO_LIMIT) {
    global $ilDB;
		global $ilUser;
    $db =& $ilDB->db;

    $query = sprintf("DELETE FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
      $db->quote($ilUser->id),
      $db->quote($test_id),
      $db->quote($this->get_id())
    );
    $result = $db->query($query);

		$query = sprintf("INSERT INTO tst_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL, NULL)",
			$db->quote($ilUser->id),
			$db->quote($test_id),
			$db->quote($this->get_id()),
			$db->quote($_GET["selimage"])
		);
		$result = $db->query($query);
//    parent::save_working_data($limit_to);
  }
}

?>