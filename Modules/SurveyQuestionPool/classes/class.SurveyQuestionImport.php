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

require_once("./classes/class.ilSaxParser.php");

/**
* Survey Question Import Parser
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @extends ilSaxParser
* @ingroup ModulesSurveyQuestionPool
*/
class SurveyQuestionImport extends ilSaxParser
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
	
	/**
	* Constructor
	*
	* @param	string		$a_xml_file		xml file
	*
	* @access	public
	*/
	function SurveyQuestionImport(&$a_spl, $a_xml_file = '', $spl_exists = FALSE)
	{
		parent::ilSaxParser($a_xml_file);
		$this->spl =& $a_spl;
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
			case "question":
				// start with a new survey question
				$type = $a_attribs["type"];
				if (strlen($type))
				{
					include_once "./Modules/SurveyQuestionPool/classes/class.$type.php";
					$this->activequestion = new $type();
					$this->activequestion->setObjId($this->spl->getId());
				}
				else
				{
					$this->activequestion = NULL;
				}
				if (is_object($this->activequestion))
				{
					foreach ($a_attribs as $key => $value)
					{
						switch ($key)
						{
							case "title":
								$this->activequestion->setTitle($value);
								break;
							case "subtype":
								$this->activequestion->setSubtype($value);
								break;
							case "obligatory":
								$this->activequestion->setObligatory($value);
								break;
						}
					}
				}
				break;
			case "material":
				switch ($this->getParent($a_xml_parser))
				{
					case "question":
					case "questiontext":
						$this->material = array();
						break;
				}
				array_push($this->material, array("text" => "", "image" => "", "label" => $a_attribs["label"]));
				break;
			case "metadata":
				$this->metadata = array();
				break;
			case "metadatafield":
				array_push($this->metadata, array("label" => "", "entry" => ""));
				break;
			case "matrix":
				$this->is_matrix = TRUE;
				$this->matrix = array();
				break;
			case "matrixrow":
				$this->material = array();
				array_push($this->matrix, "");
				break;
			case "responses":
				$this->material = array();
				$this->responses = array();
				break;
			case "response_single":
				$this->material = array();
				$this->responses[$a_attribs["id"]] = array("type" => "single", "id" => $a_attribs["id"], "label" => $a_attribs["label"]);
				$this->response_id = $a_attribs["id"];
				break;
			case "response_multiple":
				$this->material = array();
				$this->responses[$a_attribs["id"]] = array("type" => "multiple", "id" => $a_attribs["id"], "label" => $a_attribs["label"]);
				$this->response_id = $a_attribs["id"];
				break;
			case "response_text":
				$this->material = array();
				$this->responses[$a_attribs["id"]] = array("type" => "text", "id" => $a_attribs["id"], "columns" => $a_attribs["columns"], "maxlength" => $a_attribs["maxlength"], "rows" => $a_attribs["rows"], "label" => $a_attribs["label"]);
				$this->response_id = $a_attribs["id"];
				break;
			case "response_num":
				$this->material = array();
				$this->responses[$a_attribs["id"]] = array("type" => "num", "id" => $a_attribs["id"], "format" => $a_attribs["format"], "max" => $a_attribs["max"], "min" => $a_attribs["min"], "size" => $a_attribs["size"], "label" => $a_attribs["label"]);
				$this->response_id = $a_attribs["id"];
				break;
			case "response_time":
				$this->material = array();
				$this->responses[$a_attribs["id"]] = array("type" => "time", "id" => $a_attribs["id"], "format" => $a_attribs["format"], "max" => $a_attribs["max"], "min" => $a_attribs["min"], "label" => $a_attribs["label"]);
				$this->response_id = $a_attribs["id"];
				break;
			case "bipolar_adjectives":
				$this->adjectives = array();
				break;
			case "adjective":
				array_push($this->adjectives, array("label" => $a_attribs["label"], "text" => ""));
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
			case "description":
				if (is_object($this->activequestion))
				{
					$this->activequestion->setDescription($this->characterbuffer);
				}
				break;
			case "question":
				if (is_object($this->activequestion))
				{
					global $ilLog;
					$this->activequestion->saveToDb();
					$this->activequestion = NULL;
				}
				break;
			case "author":
				if (is_object($this->activequestion))
				{
					$this->activequestion->setAuthor($this->characterbuffer);
				}
				break;
			case "mattext":
				$this->material[count($this->material)-1]["text"] = $this->characterbuffer;
				break;
			case "matimage":
				$this->material[count($this->material)-1]["image"] = $this->characterbuffer;
				break;
			case "material":
				if (strcmp($this->getParent($a_xml_parser), "question") == 0)
				{
					$this->activequestion->setMaterial($this->material[0]["text"], TRUE, $this->material[0]["label"]);
				}
				break;
			case "questiontext":
				if (is_object($this->activequestion))
				{
					$questiontext = "";
					foreach ($this->material as $matarray)
					{
						$questiontext .= $matarray["text"];
					}
					$this->activequestion->setQuestiontext($questiontext);
				}
				$this->material = array();
				break;
			case "fieldlabel":
				$this->metadata[count($this->metadata)-1]["label"] = $this->characterbuffer;
				break;
			case "fieldentry":
				$this->metadata[count($this->metadata)-1]["entry"] = $this->characterbuffer;
				break;
			case "metadata":
				if (strcmp($this->getParent($a_xml_parser), "question") == 0)
				{
					if (is_object($this->activequestion))
					{
						$this->activequestion->importAdditionalMetadata($this->metadata);
					}
				}
				if (!$this->spl_exists)
				{
					if (strcmp($this->getParent($a_xml_parser), "surveyquestions") == 0)
					{
						foreach ($this->metadata as $key => $value)
						{
							if (strcmp($value["label"], "SCORM") == 0)
							{
								if (strlen($value["entry"]))
								{
									include_once "./Services/MetaData/classes/class.ilMDSaxParser.php";
									include_once "./Services/MetaData/classes/class.ilMD.php";
									$md_sax_parser = new ilMDSaxParser();
									$md_sax_parser->setXMLContent($value["entry"]);
									$md_sax_parser->setMDObject($tmp = new ilMD($this->spl->getId(),0, "spl"));
									$md_sax_parser->enableMDParsing(true);
									$md_sax_parser->startParsing();
						
									// Finally update title description
									// Update title description
									$this->spl->MDUpdateListener("General");
								}
							}
						}
					}
				}
				break;
			case "responses":
				if (is_object($this->activequestion))
				{
					$this->activequestion->importResponses($this->responses);
				}
				$this->is_matrix = FALSE;
				break;
			case "response_single":
			case "response_multiple":
			case "response_text":
			case "response_num":
			case "response_time":
				$this->responses[$this->response_id]["material"] = $this->material;
				break;
			case "adjective":
				$this->adjectives[count($this->adjectives)-1]["text"] = $this->characterbuffer;
				break;
			case "bipolar_adjectives":
				if (is_object($this->activequestion))
				{
					$this->activequestion->importAdjectives($this->adjectives);
				}
				break;
			case "matrixrow":
				$row = "";
				foreach ($this->material as $material)
				{
					$row .= $material["text"];
				}
				$this->matrix[count($this->matrix)-1] = $row;
				break;
			case "matrix":
				if (is_object($this->activequestion))
				{
					$this->activequestion->importMatrix($this->matrix);
				}
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
