<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

require_once("./Services/Xml/classes/class.ilSaxParser.php");

/**
* Old survey question import/export identifiers
*/
define("METRIC_QUESTION_IDENTIFIER", "Metric Question");
define("NOMINAL_QUESTION_IDENTIFIER", "Nominal Question");
define("ORDINAL_QUESTION_IDENTIFIER", "Ordinal Question");
define("TEXT_QUESTION_IDENTIFIER", "Text Question");

/**
* Survey Question Import Parser
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @extends ilSaxParser
* @ingroup ServicesSurvey
*/
class SurveyImportParserPre38 extends ilSaxParser
{
	var $path;
	var $depth;
	var $activequestion;
	var $spl;
  var $error_code;
  var $error_line;
  var $error_col;
  var $error_msg;
	var $has_error;
  var $size;
  var $elements;
  var $attributes;
  var $texts;
  var $text_size;
	var $characterbuffer;
	var $activetag;
	var $material;
	var $metadata;
	var $responses;
	var $response_id;
	var $matrix;
	var $is_matrix;
	var $adjectives;
	var $spl_exists;
	var $in_survey;
	var $survey;
	var $anonymisation;
	var $surveyaccess;
	var $questions;
	var $original_question_id;
	var $constraints;
	var $textblock;
	var $textblocks;
	var $in_questionblock;
	var $questionblock;
	var $questionblocks;
	var $questionblocktitle;
	
	var $question_title;
	var $in_question;
	var $in_reponse;
	var $question_description;

	/**
	* Constructor
	*
	* @param	string		$a_xml_file		xml file
	*
	* @access	public
	*/
	function SurveyImportParserPre38($a_spl_id, $a_xml_file = '', $spl_exists = FALSE)
	{
		parent::ilSaxParser($a_xml_file);
		$this->spl_id = $a_spl_id;
		$this->has_error = FALSE;
		$this->characterbuffer = "";
		$this->activetag = "";
		$this->material = array();
		$this->depth = array();
		$this->path = array();
		$this->metadata = array();
		$this->responses = array();
		$this->response_id = "";
		$this->matrix = array();
		$this->is_matrix = FALSE;
		$this->adjectives = array();
		$this->spl_exists = $spl_exists;
		$this->survey = NULL;
		$this->in_survey = FALSE;
		$this->anonymisation = 0;
		$this->surveyaccess = "restricted";
		$this->questions = array();
		$this->original_question_id = "";
		$this->constraints = array();
		$this->textblock = "";
		$this->textblocks = array();
		$this->in_questionblock = FALSE;
		$this->questionblocks = array();
		$this->questionblock = array();
		$this->questionblocktitle = "";
		$this->question_title = "";
		$this->in_question = FALSE;
		$this->in_response = FALSE;
		$this->question_description = "";
	}
	
	/**
	* Sets a reference to a survey object
	* @access	public
	*/
	function setSurveyObject(&$a_svy)
	{
		$this->survey =& $a_svy;
	}

	/**
	* set event handler
	* should be overwritten by inherited class
	* @access	private
	*/
	function setHandlers($a_xml_parser)
	{
		xml_set_object($a_xml_parser,$this);
		xml_set_element_handler($a_xml_parser,'handlerBeginTag','handlerEndTag');
		xml_set_character_data_handler($a_xml_parser,'handlerCharacterData');
	}

	/**
	* start the parser
	*/
	function startParsing()
	{
		parent::startParsing();
	}

	/**
	* parse xml file
	* 
	* @access	private
	*/
	function parse($a_xml_parser,$a_fp = null)
	{
		switch($this->getInputType())
		{
			case 'file':

				while($data = fread($a_fp,4096))
				{
					$parseOk = xml_parse($a_xml_parser,$data,feof($a_fp));
				}
				break;
				
			case 'string':
				$parseOk = xml_parse($a_xml_parser,$this->getXMLContent());
				break;
		}
		if(!$parseOk
		   && (xml_get_error_code($a_xml_parser) != XML_ERROR_NONE))
		{
      $this->error_code = xml_get_error_code($a_xml_parser);
      $this->error_line = xml_get_current_line_number($a_xml_parser);
      $this->error_col = xml_get_current_column_number($a_xml_parser);
      $this->error_msg = xml_error_string($a_xml_parser);
			$this->has_error = TRUE;
			return false;
		}
		return true;
	}
	
	function getParent($a_xml_parser)
	{
		if ($this->depth[$a_xml_parser] > 0)
		{
			return $this->path[$this->depth[$a_xml_parser]-1];
		}
		else
		{
			return "";
		}
	}
	
	/**
	* handler for begin of element
	*/
	function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
	{
		$this->depth[$a_xml_parser]++;
		$this->path[$this->depth[$a_xml_parser]] = strtolower($a_name);
		$this->characterbuffer = "";
		$this->activetag = $a_name;
    $this->elements++;
    $this->attributes+=count($a_attribs);
		switch ($a_name)
		{
			case "survey":
				$this->in_survey = TRUE;
				if (is_object($this->survey))
				{
					if (strlen($a_attribs["title"]))
					{
						$this->survey->setTitle($a_attribs["title"]);
					}
				}
				break;
			case "section":
				//$this->spl->setTitle($a_attribs["title"]);
				break;
			case "item":
				$this->original_question_id = $a_attribs["ident"];
				$this->question_title = $a_attribs["title"];
				$this->in_question = TRUE;
				break;
			case "qtimetadata":
				$this->metadata = array();
				break;
			case "qtimetadatafield":
				array_push($this->metadata, array("label" => "", "entry" => ""));
				break;
			case "material":
				$this->material = array();
				array_push($this->material, array("text" => "", "image" => "", "label" => $a_attribs["label"]));
				break;
			case "render_fib":
				if (is_object($this->activequestion))
				{
					if (strlen($a_attribs["minnumber"]))
					{
						$this->activequestion->setMinimum($a_attribs["minnumber"]);
					}
					if (strlen($a_attribs["maxnumber"]))
					{
						$this->activequestion->setMaximum($a_attribs["maxnumber"]);
					}
				}
				break;
			case "response_lid":
				if (is_object($this->activequestion))
				{
					if (strcmp($this->activequestion->getQuestiontype(), "SurveyNominalQuestion") == 0)
					{
						switch (strtolower($a_attribs["rcardinality"]))
						{
							case "single":
								$this->activequestion->setSubtype(1);
								break;
							case "multiple":
								$this->activequestion->setSubtype(2);
								break;
						}
					}
				}
				break;
			case "response_label":
				$this->in_response = TRUE;
				break;
		}
	}

	/**
	* handler for character data
	*/
	function handlerCharacterData($a_xml_parser, $a_data)
	{
    $this->texts++;
    $this->text_size+=strlen($a_data);
		$this->characterbuffer .= $a_data;
		$a_data = $this->characterbuffer;
	}

	/**
	* handler for end of element
	*/
	function handlerEndTag($a_xml_parser, $a_name)
	{
		switch ($a_name)
		{
			case "questestinterop":
				if (is_object($this->survey))
				{
					// write constraints
					if (count($this->constraints))
					{
						$relations = $this->survey->getAllRelations(TRUE);
						foreach ($this->constraints as $constraint)
						{
							$this->survey->addConstraint($this->questions[$constraint["sourceref"]], $this->questions[$constraint["destref"]], $relations[$constraint["relation"]]["id"], $constraint["value"]);
						}
					}
					// write question blocks
					if (count($this->questionblocks))
					{
						foreach ($this->questionblocks as $data)
						{
							$questionblock = $data["questions"];
							$title = $data["title"];
							$qblock = array();
							foreach ($questionblock as $question_id)
							{
								array_push($qblock, $this->questions[$question_id]);
							}
							$this->survey->createQuestionblock($title, TRUE, $qblock);
						}
					}					
					$this->survey->saveToDb();

					// write textblocks
					if (count($this->textblocks))
					{
						foreach ($this->textblocks as $original_id => $textblock)
						{
							$this->survey->saveHeading($textblock, $this->questions[$original_id]);
						}
					}
				}
				break;
			case "survey":
				$this->in_survey = FALSE;
				break;
			case "item":
				if (is_object($this->activequestion))
				{
					$this->activequestion->setTitle($this->question_title);
					$this->activequestion->setDescription($this->question_description);
					$this->activequestion->saveToDb();
					if (is_object($this->survey))
					{
						// duplicate the question for the survey
						$question_id = $this->activequestion->duplicate(TRUE);
						$this->survey->addQuestion($question_id);
						$this->questions[$this->original_question_id] = $question_id;
					}
				}
				$this->in_question = FALSE;
				$this->question_description = "";
				$this->activequestion = NULL;
				break;
			case "qticomment":
				if (strcmp($this->getParent($a_xml_parser), "item") == 0)
				{
					if (preg_match("/Questiontype\=(.*)/", $this->characterbuffer, $matches))
					{
						$questiontype = $matches[1];
						switch ($matches[1])
						{
							case METRIC_QUESTION_IDENTIFIER:
								$questiontype = "SurveyMetricQuestion";
								break;
							case NOMINAL_QUESTION_IDENTIFIER:
								$questiontype = "SurveyMultipleChoiceQuestion";
								break;
							case ORDINAL_QUESTION_IDENTIFIER:
								$questiontype = "SurveySingleChoiceQuestion";
								break;
							case TEXT_QUESTION_IDENTIFIER:
								$questiontype = "SurveyTextQuestion";
								break;
						}
						if (strlen($questiontype))
						{
							include_once "./Modules/SurveyQuestionPool/classes/class.$questiontype.php";
							$this->activequestion = new $questiontype();
							$this->activequestion->setObjId($this->spl_id);
						}
						else
						{
							$this->activequestion = NULL;
						}
					}
					else if (preg_match("/Author\=(.*)/", $this->characterbuffer, $matches))
					{
						if (is_object($this->activequestion))
						{
							$this->activequestion->setAuthor($matches[1]);
						}
					}
					else if (preg_match("/ILIAS Version\=(.*)/", $this->characterbuffer, $matches))
					{
					}
					else
					{
						$this->question_description = $this->characterbuffer;
					}
				}
				if (strcmp($this->getParent($a_xml_parser), "survey") == 0)
				{
					if (preg_match("/Author\=(.*)/", $this->characterbuffer, $matches))
					{
						if (is_object($this->survey))
						{
							$this->survey->setAuthor($matches[1]);
						}
					}
					else if (preg_match("/ILIAS Version\=(.*)/", $this->characterbuffer, $matches))
					{
					}
					else
					{
						if (is_object($this->survey))
						{
							$this->survey->setDescription($this->characterbuffer);
						}
					}
					
				}
				break;
			case "fieldlabel":
				$this->metadata[count($this->metadata)-1]["label"] = $this->characterbuffer;
				break;
			case "fieldentry":
				$this->metadata[count($this->metadata)-1]["entry"] = $this->characterbuffer;
				break;
			case "qtimetadata":
				if (strcmp($this->getParent($a_xml_parser), "section") == 0)
				{
					foreach ($this->metadata as $meta)
					{
						switch ($meta["label"])
						{
							case "SCORM":
								if (!$this->spl_exists)
								{
									include_once "./Services/MetaData/classes/class.ilMDSaxParser.php";
									include_once "./Services/MetaData/classes/class.ilMD.php";
									include_once "./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php";
									$md_sax_parser = new ilMDSaxParser();
									$md_sax_parser->setXMLContent($value["entry"]);
									$md_sax_parser->setMDObject($tmp = new ilMD($this->spl_id,0, "spl"));
									$md_sax_parser->enableMDParsing(true);
									$md_sax_parser->startParsing();
									$spl = new ilObjSurveyQuestionPool($this->spl_id, false);
									$spl->MDUpdateListener("General");
									break;
								}
						}
					}
				}
				if (is_object($this->activequestion))
				{
					foreach ($this->metadata as $meta)
					{
						switch ($this->activequestion->getQuestionType())
						{
							case "SurveyMultipleQuestion":
								switch ($meta["label"])
								{
									case "obligatory":
										$this->activequestion->setObligatory($meta["entry"]);
										break;
									case "orientation":
										$this->activequestion->setOrientation($meta["entry"]);
										break;
								}
								break;
							case "SurveySingleChoiceQuestion":
								switch ($meta["label"])
								{
									case "obligatory":
										$this->activequestion->setObligatory($meta["entry"]);
										break;
									case "orientation":
										$this->activequestion->setOrientation($meta["entry"]);
										break;
								}
								break;
							case "SurveyMetricQuestion":
								switch ($meta["label"])
								{
									case "obligatory":
										$this->activequestion->setObligatory($meta["entry"]);
										break;
									case "subtype":
										$this->activequestion->setSubtype($meta["entry"]);
										break;
								}
								break;
							case "SurveyTextQuestion":
								switch ($meta["label"])
								{
									case "obligatory":
										$this->activequestion->setObligatory($meta["entry"]);
										break;
									case "maxchars":
										if (strlen($meta["entry"]))
										{
											$this->activequestion->setMaxChars($meta["entry"]);
										}
										break;
								}
								break;
						}
					}
				}
				if (is_object($this->survey))
				{
					foreach ($this->metadata as $meta)
					{
						switch ($meta["label"])
						{
							case "SCORM":
								if (strcmp($this->getParent($a_xml_parser), "survey") == 0)
								{
									include_once "./Services/MetaData/classes/class.ilMDSaxParser.php";
									include_once "./Services/MetaData/classes/class.ilMD.php";
									$md_sax_parser = new ilMDSaxParser();
									$md_sax_parser->setXMLContent($meta["entry"]);
									$md_sax_parser->setMDObject($tmp = new ilMD($this->survey->getId(), 0, "svy"));
									$md_sax_parser->enableMDParsing(TRUE);
									$md_sax_parser->startParsing();
									$this->survey->MDUpdateListener("General");
								}
								break;
							case "author":
								$this->survey->setAuthor($meta["entry"]);
								break;
							case "description":
								$this->survey->setDescription($meta["entry"]);
								break;
							case "evaluation_access":
								$this->survey->setEvaluationAccess($meta["entry"]);
								break;
							case "evaluation_access":
								$this->survey->setEvaluationAccess($meta["entry"]);
								break;
							case "anonymize":
								$this->survey->setAnonymize($meta["entry"]);
								break;
							case "status":
								$this->survey->setStatus($meta["entry"]);
								break;
							case "startdate":
								if (preg_match("/P(\d+)Y(\d+)M(\d+)DT0H0M0S/", $meta["entry"], $matches))
								{
									$this->survey->setStartDate(sprintf("%04d-%02d-%02d", $matches[1], $matches[2], $matches[3]));
									$this->survey->setStartDateEnabled(1);
								}
								break;
							case "enddate":
								if (preg_match("/P(\d+)Y(\d+)M(\d+)DT0H0M0S/", $meta["entry"], $matches))
								{
									$this->survey->setEndDate(sprintf("%04d-%02d-%02d", $matches[1], $matches[2], $matches[3]));
									$this->survey->setEndDateEnabled(1);
								}
								break;
							case "display_question_titles":
								if ($meta["entry"])
								{
									$this->survey->showQuestionTitles();
								}
								break;
						}
						if (preg_match("/questionblock_(\d+)/", $meta["label"], $matches))
						{
							// handle questionblocks
							$qb = $meta["entry"];
							preg_match("/<title>(.*?)<\/title>/", $qb, $matches);
							$qb_title = $matches[1];
							preg_match("/<questions>(.*?)<\/questions>/", $qb, $matches);
							$qb_questions = $matches[1];
							$qb_questions_array = explode(",", $qb_questions);
							array_push($this->questionblocks, array(
								"title" => $qb_title,
								"questions" => $qb_questions_array
							));
						}
						if (preg_match("/constraint_(\d+)/", $meta["label"], $matches))
						{
							$constraint = $meta["entry"];
							$constraint_array = explode(",", $constraint);
							if (count($constraint_array) == 3)
							{
								array_push($this->constraints, array(
									"sourceref"      => $matches[1], 
									"destref" => $constraint_array[0],
									"relation" => $constraint_array[1],
									"value"    => $constraint_array[2]
								));
							}
						}
						if (preg_match("/heading_(\d+)/", $meta["label"], $matches))
						{
							$heading = $meta["entry"];
							$this->textblocks[$matches[1]] = $heading;
						}
					}
				}
				break;
			case "mattext":
				$this->material[count($this->material)-1]["text"] = $this->characterbuffer;
				break;
			case "matimage":
				$this->material[count($this->material)-1]["image"] = $this->characterbuffer;
				break;
			case "material":
				if ($this->in_survey)
				{
					if (strcmp($this->getParent($a_xml_parser), "objectives") == 0)
					{
						if (strcmp($this->material[0]["label"], "introduction") == 0)
						{
							if (is_object($this->survey))
							{
								$this->survey->setIntroduction($this->material[0]["text"]);
							}
						}
						if (strcmp($this->material[0]["label"], "outro") == 0)
						{
							if (is_object($this->survey))
							{
								$this->survey->setOutro($this->material[0]["text"]);
							}
						}
						$this->material = array();
					}
				}
				else
				{
					switch ($this->getParent($a_xml_parser))
					{
						case "response_num":
						case "response_lid":
						case "response_str":
							if (is_object($this->activequestion))
							{
								$this->activequestion->setMaterial($this->material[0]["text"], TRUE, $this->material[0]["label"]);
							}
							break;
						case "flow":
							if (is_object($this->activequestion))
							{
								$this->activequestion->setQuestiontext($this->material[0]["text"]);
							}
							break;
					}
					if (is_object($this->activequestion))
					{
						if ($this->in_response)
						{
							switch ($this->activequestion->getQuestiontype())
							{
								case "SurveyMultipleChoiceQuestion":
								case "SurveySingleChoiceQuestion":
									$this->activequestion->categories->addCategory($this->material[0]["text"]);
									break;
							}
						}
					}
				}
				break;
			case "response_label":
				$this->in_response = FALSE;
				break;
		}
		$this->depth[$a_xml_parser]--;
	}

  function getErrorCode() 
	{
    return $this->error_code; 
  }
  
  function getErrorLine() 
	{
    return $this->error_line; 
  }
  
  function getErrorColumn() 
	{
    return $this->error_col; 
  }
  
  function getErrorMessage() 
	{
    return $this->error_msg; 
  }
  
  function getFullError() 
	{
    return "Error: ".$this->error_msg." at line:".$this->error_line ." column:".$this->error_col;
  }
  
  function getXMLSize() 
	{
    return $this->size; 
  }
  
  function getXMLElements() 
	{
    return $this->elements; 
  }
  
  function getXMLAttributes() 
	{
    return $this->attributes; 
  }
  
  function getXMLTextSections() 
	{
    return $this->texts; 
  }
  
  function getXMLTextSize() 
	{
    return $this->text_size; 
  }
  
  function hasError() 
	{
    return $this->has_error; 
  }
  
}
?>
