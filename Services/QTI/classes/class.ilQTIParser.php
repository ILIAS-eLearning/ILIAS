<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

include_once("./classes/class.ilSaxParser.php");

define ("IL_MO_PARSE_QTI",  1);
define ("IL_MO_VERIFY_QTI", 2);

/**
* QTI Parser
*
* @author Helmut Schottmüller <hschottm@gmx.de>
* @version $Id$
*
* @extends ilSaxParser
* @package assessment
*/
class ilQTIParser extends ilSaxParser
{
	var $lng;
	var $hasRootElement;
	var $path;
	var $items;
	var $item;
	var $depth;
	var $qti_element;
	var $in_presentation;
	var $in_response;
	var $render_type;
	var $response_label;
	var $material;
	var $matimage;
	var $response;
	var $resprocessing;
	var $outcomes;
	var $decvar;
	var $respcondition;
	var $setvar;
	var $displayfeedback;
	var $itemfeedback;
	var $flow_mat;
	var $flow;
	var $presentation;
	var $mattext;
	var $sametag;
	var $characterbuffer;
	var $conditionvar;
	var $parser_mode;
	var $import_idents;
	var $qpl_id;
	var $tst_id;
	var $tst_object;
	var $do_nothing;
	var $gap_index;
	var $assessments;
	var $assessment;
	var $assessmentcontrol;
	var $objectives;
	var $in_assessment = FALSE;
	var $section;
	var $import_mapping;
	var $question_counter = 1;
	var $in_itemmetadata;
	var $in_objectives = FALSE;

	var $founditems = array();
	var $verifyroot = false;
	var $verifyqticomment = 0;
	var $verifymetadatafield = 0;
	var $verifyfieldlabel = 0;
	var $verifyfieldlabeltext = "";
	var $verifyfieldentry = 0;
	var $verifyfieldentrytext = "";
	
	/**
	* Constructor
	*
	* @param	string		$a_xml_file			xml file
	* @param  integer $a_mode Parser mode IL_MO_PARSE_QTI | IL_MO_VERIFY_QTI
	* @access	public
	*/
	//  TODO: The following line gets me an parse error in PHP 4, but I found no hint that pass-by-reference is forbidden in PHP 4 ????
	function ilQTIParser($a_xml_file, $a_mode = IL_MO_PARSE_QTI, $a_qpl_id = 0, $a_import_idents = "")
	{
		global $lng;

		$this->setParserMode($a_mode);

		parent::ilSaxParser($a_xml_file);

		$this->qpl_id = $a_qpl_id;
		$this->import_idents = array();
		if (is_array($a_import_idents))
		{
			$this->import_idents =& $a_import_idents;
		}
		
		$this->lng =& $lng;
		$this->hasRootElement = FALSE;
		$this->import_mapping = array();
		$this->assessments = array();
		$this->assessment = NULL;
		$this->section = NULL;
		$this->path = array();
		$this->items = array();
		$this->item = NULL;
		$this->depth = array();
		$this->do_nothing = FALSE;
		$this->qti_element = "";
		$this->in_presentation = FALSE;
		$this->in_objectives = FALSE;
		$this->in_reponse = FALSE;
		$this->render_type = NULL;
		$this->render_hotspot = NULL;
		$this->response_label = NULL;
		$this->material = NULL;
		$this->response = NULL;
		$this->assessmentcontrol = NULL;
		$this->objectives = NULL;
		$this->matimage = NULL;
		$this->resprocessing = NULL;
		$this->outcomes = NULL;
		$this->decvar = NULL;
		$this->respcondition = NULL;
		$this->setvar = NULL;
		$this->displayfeedback = NULL;
		$this->itemfeedback = NULL;
		$this->flow_mat = array();
		$this->question_counter = 1;
		$this->flow = 0;
		$this->gap_index = 0;
		$this->presentation = NULL;
		$this->mattext = NULL;
		$this->matapplet = NULL;
		$this->sametag = FALSE;
		$this->in_assessment = FALSE;
		$this->characterbuffer = "";
		$this->metadata = array("label" => "", "entry" => "");
	}
	
	function setTestObject(&$a_tst_object)
	{
		$this->tst_object =& $a_tst_object;
		if (is_object($a_tst_object))
		{
			$this->tst_id = $this->tst_object->getId();
		}
	}

	function setParserMode($a_mode = IL_MO_PARSE_QTI)
	{
		$this->parser_mode = $a_mode;
		$this->founditems = array();
		$this->verifyroot = false;
		$this->verifyqticomment = 0;
		$this->verifymetadatafield = 0;
		$this->verifyfieldlabel = 0;
		$this->verifyfieldentry = 0;
		$this->verifyfieldlabeltext = "";
		$this->verifyfieldentrytext = "";
		$this->question_counter = 1;
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

	function startParsing()
	{
		$this->question_counter = 1;
		parent::startParsing();
		return FALSE;
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
	function handlerBeginTag($a_xml_parser,$a_name,$a_attribs)
	{
		switch ($this->parser_mode)
		{
			case IL_MO_PARSE_QTI:
				$this->handlerParseBeginTag($a_xml_parser, $a_name, $a_attribs);
				break;
			case IL_MO_VERIFY_QTI:
				$this->handlerVerifyBeginTag($a_xml_parser, $a_name, $a_attribs);
				break;
		}
	}

	/**
	* handler for begin of element parser
	*/
	function handlerParseBeginTag($a_xml_parser,$a_name,$a_attribs)
	{
		if ($this->do_nothing) return;
		$this->sametag = FALSE;
		$this->characterbuffer = "";
		$this->depth[$a_xml_parser]++;
		$this->path[$this->depth[$a_xml_parser]] = strtolower($a_name);
		$this->qti_element = $a_name;
		
		switch (strtolower($a_name))
		{
			case "assessment":
				include_once ("./Services/QTI/classes/class.ilQTIAssessment.php");
				$this->assessment =& $this->assessments[array_push($this->assessments, new ilQTIAssessment())-1];
				$this->in_assessment = TRUE;
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "title":
								$this->assessment->setTitle($value);
								break;
							case "ident":
								$this->assessment->setIdent($value);
								break;
						}
					}
				}
				break;
			case "assessmentcontrol":
				include_once ("./Services/QTI/classes/class.ilQTIAssessmentcontrol.php");
				$this->assessmentcontrol = new ilQTIAssessmentcontrol();
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "solutionswitch":
								$this->assessmentcontrol->setSolutionswitch($value);
								break;
							case "hintswitch":
								$this->assessmentcontrol->setHintswitch($value);
								break;
							case "feedbackswitch":
								$this->assessmentcontrol->setFeedbackswitch($value);
								break;
						}
					}
				}
				break;
			case "objectives":
				include_once ("./Services/QTI/classes/class.ilQTIObjectives.php");
				$this->objectives = new ilQTIObjectives();
				$this->in_objectives = TRUE;
				break;
			case "section":
				include_once ("./Services/QTI/classes/class.ilQTISection.php");
				$this->section = new ilQTISection();
				break;
			case "itemmetadata":
				$this->in_itemmetadata = TRUE;
				break;
			case "qtimetadatafield":
				$this->metadata = array("label" => "", "entry" => "");
				break;
			case "flow":
				include_once ("./Services/QTI/classes/class.ilQTIFlow.php");
				$this->flow++;
				break;
			case "flow_mat":
				include_once ("./Services/QTI/classes/class.ilQTIFlowMat.php");
				array_push($this->flow_mat, new ilQTIFlowMat());
				break;
			case "itemfeedback":
				include_once ("./Services/QTI/classes/class.ilQTIItemfeedback.php");
				$this->itemfeedback = new ilQTIItemfeedback();
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "ident":
								$this->itemfeedback->setIdent($value);
								break;
							case "view":
								$this->itemfeedback->setView($value);
								break;
						}
					}
				}
				break;
			case "displayfeedback":
				include_once ("./Services/QTI/classes/class.ilQTIDisplayfeedback.php");
				$this->displayfeedback = new ilQTIDisplayfeedback();
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "feedbacktype":
								$this->displayfeedback->setFeedbacktype($value);
								break;
							case "linkrefid":
								$this->displayfeedback->setLinkrefid($value);
								break;
						}
					}
				}
				break;
			case "setvar":
				include_once ("./Services/QTI/classes/class.ilQTISetvar.php");
				$this->setvar = new ilQTISetvar();
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "action":
								$this->setvar->setAction($value);
								break;
							case "varname":
								$this->setvar->setVarname($value);
								break;
						}
					}
				}
				break;
			case "conditionvar":
				include_once ("./Services/QTI/classes/class.ilQTIConditionvar.php");
				$this->conditionvar = new ilQTIConditionvar();
				break;
			case "not":
				if ($this->conditionvar != NULL)
				{
					$this->conditionvar->addNot();
				}
				break;
			case "and":
				if ($this->conditionvar != NULL)
				{
					$this->conditionvar->addAnd();
				}
				break;
			case "or":
				if ($this->conditionvar != NULL)
				{
					$this->conditionvar->addOr();
				}
				break;
			case "varequal":
				include_once("./Services/QTI/classes/class.ilQTIResponseVar.php");
				$this->responsevar = new ilQTIResponseVar(RESPONSEVAR_EQUAL);
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "case":
								$this->responsevar->setCase($value);
								break;
							case "respident":
								$this->responsevar->setRespident($value);
								break;
							case "index":
								$this->responsevar->setIndex($value);
								break;
						}
					}
				}
				break;
			case "varlt":
				include_once("./Services/QTI/classes/class.ilQTIResponseVar.php");
				$this->responsevar = new ilQTIResponseVar(RESPONSEVAR_LT);
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "respident":
								$this->responsevar->setRespident($value);
								break;
							case "index":
								$this->responsevar->setIndex($value);
								break;
						}
					}
				}
				break;
			case "varlte":
				include_once("./Services/QTI/classes/class.ilQTIResponseVar.php");
				$this->responsevar = new ilQTIResponseVar(RESPONSEVAR_LTE);
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "respident":
								$this->responsevar->setRespident($value);
								break;
							case "index":
								$this->responsevar->setIndex($value);
								break;
						}
					}
				}
				break;
			case "vargt":
				include_once("./Services/QTI/classes/class.ilQTIResponseVar.php");
				$this->responsevar = new ilQTIResponseVar(RESPONSEVAR_GT);
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "respident":
								$this->responsevar->setRespident($value);
								break;
							case "index":
								$this->responsevar->setIndex($value);
								break;
						}
					}
				}
				break;
			case "vargte":
				include_once("./Services/QTI/classes/class.ilQTIResponseVar.php");
				$this->responsevar = new ilQTIResponseVar(RESPONSEVAR_GTE);
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "respident":
								$this->responsevar->setRespident($value);
								break;
							case "index":
								$this->responsevar->setIndex($value);
								break;
						}
					}
				}
				break;
			case "varsubset":
				include_once("./Services/QTI/classes/class.ilQTIResponseVar.php");
				$this->responsevar = new ilQTIResponseVar(RESPONSEVAR_SUBSET);
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "respident":
								$this->responsevar->setRespident($value);
								break;
							case "setmatch":
								$this->responsevar->setSetmatch($value);
								break;
							case "index":
								$this->responsevar->setIndex($value);
								break;
						}
					}
				}
				break;
			case "varinside":
				include_once("./Services/QTI/classes/class.ilQTIResponseVar.php");
				$this->responsevar = new ilQTIResponseVar(RESPONSEVAR_INSIDE);
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "respident":
								$this->responsevar->setRespident($value);
								break;
							case "areatype":
								$this->responsevar->setAreatype($value);
								break;
							case "index":
								$this->responsevar->setIndex($value);
								break;
						}
					}
				}
				break;
			case "varsubstring":
				include_once("./Services/QTI/classes/class.ilQTIResponseVar.php");
				$this->responsevar = new ilQTIResponseVar(RESPONSEVAR_SUBSTRING);
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "case":
								$this->responsevar->setCase($value);
								break;
							case "respident":
								$this->responsevar->setRespident($value);
								break;
							case "index":
								$this->responsevar->setIndex($value);
								break;
						}
					}
				}
				break;
			case "respcondition":
				include_once("./Services/QTI/classes/class.ilQTIRespcondition.php");
				$this->respcondition = new ilQTIRespcondition();
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "continue":
								$this->respcondition->setContinue($value);
								break;
							case "title":
								$this->respcondition->setTitle($value);
								break;
						}
					}
				}
				break;
			case "outcomes":
				include_once("./Services/QTI/classes/class.ilQTIOutcomes.php");
				$this->outcomes = new ilQTIOutcomes();
				break;
			case "decvar":
				include_once("./Services/QTI/classes/class.ilQTIDecvar.php");
				$this->decvar = new ilQTIDecvar();
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "varname":
								$this->decvar->setVarname($value);
								break;
							case "vartype":
								$this->decvar->setVartype($value);
								break;
							case "defaultval":
								$this->decvar->setDefaultval($value);
								break;
							case "minvalue":
								$this->decvar->setMinvalue($value);
								break;
							case "maxvalue":
								$this->decvar->setMaxvalue($value);
								break;
							case "members":
								$this->decvar->setMembers($value);
								break;
							case "cutvalue":
								$this->decvar->setCutvalue($value);
								break;
						}
					}
				}
				break;
			case "matimage":
				include_once("./Services/QTI/classes/class.ilQTIMatimage.php");
				$this->matimage = new ilQTIMatimage();
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "imagtype":
								$this->matimage->setImagetype($value);
								break;
							case "label":
								$this->matimage->setLabel($value);
								break;
							case "height":
								$this->matimage->setHeight($value);
								break;
							case "width":
								$this->matimage->setWidth($value);
								break;
							case "uri":
								$this->matimage->setUri($value);
								break;
							case "embedded":
								$this->matimage->setEmbedded($value);
								break;
							case "x0":
								$this->matimage->setX0($value);
								break;
							case "y0":
								$this->matimage->setY0($value);
								break;
							case "entityref":
								$this->matimage->setEntityref($value);
								break;
						}
					}
				}
				break;
			case "material":
				include_once("./Services/QTI/classes/class.ilQTIMaterial.php");
				$this->material = new ilQTIMaterial();
				$this->material->setFlow($this->flow);
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "label":
								$this->material->setLabel($value);
								break;
						}
					}
				}
				break;
			case "mattext":
				include_once ("./Services/QTI/classes/class.ilQTIMattext.php");
				$this->mattext = new ilQTIMattext();
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "texttype":
								$this->mattext->setTexttype($value);
								break;
							case "label":
								$this->mattext->setLabel($value);
								break;
							case "charset":
								$this->mattext->setCharset($value);
								break;
							case "uri":
								$this->mattext->setUri($value);
								break;
							case "xml:space":
								$this->mattext->setXmlspace($value);
								break;
							case "xml:lang":
								$this->mattext->setXmllang($value);
								break;
							case "entityref":
								$this->mattext->setEntityref($value);
								break;
							case "height":
								$this->mattext->setHeight($value);
								break;
							case "width":
								$this->mattext->setWidth($value);
								break;
							case "x0":
								$this->mattext->setX0($value);
								break;
							case "y0":
								$this->mattext->setY0($value);
								break;
						}
					}
				}
				break;
			case "matapplet":
				include_once ("./Services/QTI/classes/class.ilQTIMatapplet.php");
				$this->matapplet = New ilQTIMatapplet();
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "label":
								$this->matapplet->setLabel($value);
								break;
							case "uri":
								$this->matapplet->setUri($value);
								break;
							case "y0":
								$this->matapplet->setY0($value);
								break;
							case "height":
								$this->matapplet->setHeight($value);
								break;
							case "width":
								$this->matapplet->setWidth($value);
								break;
							case "x0":
								$this->matapplet->setX0($value);
								break;
							case "embedded":
								$this->matapplet->setEmbedded($value);
								break;
							case "entityref":
								$this->matapplet->setEntityref($value);
								break;
						}
					}
				}
				break;
			case "questestinterop":
				$this->hasRootElement = TRUE;
				break;
			case "qticomment":
				break;
			case "objectbank":
				// not implemented yet
				break;
			case "section":
				if ($this->assessment != NULL)
				{
					$this->assessment->addSection($this->section);
				}
				$this->section = NULL;
				break;
			case "presentation":
				$this->in_presentation = TRUE;
				include_once ("./Services/QTI/classes/class.ilQTIPresentation.php");
				$this->presentation = new ilQTIPresentation();
				break;
			case "response_label":
				if ($this->render_type != NULL)
				{
				include_once("./Services/QTI/classes/class.ilQTIResponseLabel.php");
					$this->response_label = new ilQTIResponseLabel();
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "rshuffle":
								$this->response_label->setRshuffle($value);
								break;
							case "rarea":
								$this->response_label->setRarea($value);
								break;
							case "rrange":
								$this->response_label->setRrange($value);
								break;
							case "labelrefid":
								$this->response_label->setLabelrefid($value);
								break;
							case "ident":
								$this->response_label->setIdent($value);
								break;
							case "match_group":
								$this->response_label->setMatchGroup($value);
								break;
							case "match_max":
								$this->response_label->setMatchMax($value);
								break;
						}
					}
				}
				break;
			case "render_choice":
				if ($this->in_response)
				{
					include_once("./Services/QTI/classes/class.ilQTIRenderChoice.php");
					$this->render_type = new ilQTIRenderChoice();
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "shuffle":
								$this->render_type->setShuffle($value);
								break;
						}
					}
				}
				break;
			case "render_hotspot":
				if ($this->in_response)
				{
					include_once("./Services/QTI/classes/class.ilQTIRenderHotspot.php");
					$this->render_type = new ilQTIRenderHotspot();
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "showdraw":
								$this->render_type->setShuffle($value);
								break;
							case "minnumber":
								$this->render_type->setMinnumber($value);
								break;
							case "maxnumber":
								$this->render_type->setMaxnumber($value);
								break;
						}
					}
				}
				break;
			case "render_fib":
				if ($this->in_response)
				{
					include_once("./Services/QTI/classes/class.ilQTIRenderFib.php");
					$this->render_type = new ilQTIRenderFib();
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "encoding":
								$this->render_type->setEncoding($value);
								break;
							case "fibtype":
								$this->render_type->setFibtype($value);
								break;
							case "rows":
								$this->render_type->setRows($value);
								break;
							case "maxchars":
								$this->render_type->setMaxchars($value);
								break;
							case "prompt":
								$this->render_type->setPrompt($value);
								break;
							case "columns":
								$this->render_type->setColumns($value);
								break;
							case "charset":
								$this->render_type->setCharset($value);
								break;
							case "maxnumber":
								$this->render_type->setMaxnumber($value);
								break;
							case "minnumber":
								$this->render_type->setMinnumber($value);
								break;
						}
					}
				}
				break;
			case "response_lid":
				// Ordering Terms and Definitions    or
				// Ordering Terms and Pictures       or
				// Multiple choice single response   or
				// Multiple choice multiple response
			case "response_xy":
				// Imagemap question
			case "response_str":
				// Close question
			case "response_num":
			case "response_grp":
				// Matching terms and definitions
				// Matching terms and images
				include_once "./Services/QTI/classes/class.ilQTIResponse.php";
				switch (strtolower($a_name))
				{
					case "response_lid":
						$response_type = RT_RESPONSE_LID;
						break;
					case "response_xy":
						$response_type = RT_RESPONSE_XY;
						break;
					case "response_str":
						$response_type = RT_RESPONSE_STR;
						break;
					case "response_num":
						$response_type = RT_RESPONSE_NUM;
						break;
					case "response_grp":
						$response_type = RT_RESPONSE_GRP;
						break;
				}
				$this->in_response = TRUE;
				$this->response = new ilQTIResponse($response_type);
				$this->response->setFlow($this->flow);
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "ident":
								$this->response->setIdent($value);
								break;
							case "rtiming":
								$this->response->setRTiming($value);
								break;
							case "rcardinality":
								$this->response->setRCardinality($value);
								break;
							case "numtype":
								$this->response->setNumtype($value);
								break;
						}
					}
				}
				break;
			case "item":
				include_once("./Services/QTI/classes/class.ilQTIItem.php");
				$this->gap_index = 0;
				$this->item =& $this->items[array_push($this->items, new ilQTIItem())-1];
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "ident":
								$this->item->setIdent($value);
								if (count($this->import_idents) > 0)
								{
									if (!in_array($value, $this->import_idents))
									{
										$this->do_nothing = TRUE;
									}
								}
								break;
							case "title":
								$this->item->setTitle($value);
								break;
						}
					}
				}
				break;
			case "resprocessing":
				include_once("./Services/QTI/classes/class.ilQTIResprocessing.php");
				$this->resprocessing = new ilQTIResprocessing();
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "scoremodel":
								$this->resprocessing->setScoremodel($value);
								break;
						}
					}
				}
				break;
		}
	}

	/**
	* handler for end of element
	*/
	function handlerEndTag($a_xml_parser,$a_name)
	{
		switch ($this->parser_mode)
		{
			case IL_MO_PARSE_QTI:
				$this->handlerParseEndTag($a_xml_parser, $a_name);
				break;
			case IL_MO_VERIFY_QTI:
				$this->handlerVerifyEndTag($a_xml_parser, $a_name);
				break;
		}
	}
	
	/**
	* handler for end of element parser
	*/
	function handlerParseEndTag($a_xml_parser,$a_name)
	{
		if (($this->do_nothing) && (strcmp(strtolower($a_name), "item") != 0)) return;
		switch (strtolower($a_name))
		{
			case "assessment":
				if (is_object($this->tst_object))
				{
					$this->tst_object->fromXML($this->assessment);
				}
				$this->in_assessment = FALSE;
				break;
			case "assessmentcontrol":
				$this->assessment->addAssessmentcontrol($this->assessmentcontrol);
				$this->assessmentcontrol = NULL;
				break;
			case "objectives":
				if (strcmp(strtolower($this->getParent($a_xml_parser)), "assessment") == 0)
				{
					$this->assessment->addObjectives($this->objectives);
				}
				$this->in_objectives = FALSE;
				break;
			case "itemmetadata":
				$this->in_itemmetadata = FALSE;
				break;
			case "qtimetadatafield":
				// handle only specific ILIAS metadata
				switch ($this->metadata["label"])
				{
					case "ILIAS_VERSION":
						break;
					case "QUESTIONTYPE":
						if ($this->item != NULL)
						{
							$this->item->setQuestiontype($this->metadata["entry"]);
						}
						break;
					case "AUTHOR":
						if ($this->item != NULL)
						{
							$this->item->setAuthor($this->metadata["entry"]);
						}
					default:
						if ($this->item != NULL)
						{
							$this->item->addMetadata($this->metadata);
						}
						break;
				}
				if ($this->in_assessment)
				{
					$this->assessment->addQtiMetadata($this->metadata);
				}
				$this->metadata = array("label" => "", "entry" => "");
				break;
			case "flow":
				$this->flow--;
				break;
			case "flow_mat":
				if (count($this->flow_mat))
				{
					$flow_mat = array_pop($this->flow_mat);
					if (count($this->flow_mat))
					{
						$this->flow_mat[count($this->flow_mat)-1]->addFlow_mat($flow_mat);
					}
					else if ($this->itemfeedback != NULL)
					{
						$this->itemfeedback->addFlow_mat($flow_mat);
					}
					else if ($this->response_label != NULL)
					{
						$this->response_label->addFlow_mat($flow_mat);
					}
				}
				break;
			case "itemfeedback":
				if ($this->item != NULL)
				{
					if ($this->itemfeedback != NULL)
					{
						$this->item->addItemfeedback($this->itemfeedback);
					}
				}
				$this->itemfeedback = NULL;
				break;
			case "displayfeedback":
				if ($this->respcondition != NULL)
				{
					if ($this->displayfeedback != NULL)
					{
						$this->respcondition->addDisplayfeedback($this->displayfeedback);
					}
				}
				$this->displayfeedback = NULL;
				break;
			case "setvar":
				if ($this->respcondition != NULL)
				{
					if ($this->setvar != NULL)
					{
						$this->respcondition->addSetvar($this->setvar);
					}
				}
				$this->setvar = NULL;
				break;
			case "conditionvar":
				if ($this->respcondition != NULL)
				{
					$this->respcondition->setConditionvar($this->conditionvar);
				}
				$this->conditionvar = NULL;
				break;
			case "varequal":
			case "varlt":
			case "varlte":
			case "vargt":
			case "vargte":
			case "varsubset":
			case "varinside":
			case "varsubstring":
				if ($this->conditionvar != NULL)
				{
					if ($this->responsevar != NULL)
					{
						$this->conditionvar->addResponseVar($this->responsevar);
					}
				}
				$this->responsevar = NULL;
				break;
			case "respcondition":
				if ($this->resprocessing != NULL)
				{
					$this->resprocessing->addRespcondition($this->respcondition);
				}
				$this->respcondition = NULL;
				break;
			case "outcomes":
				if ($this->resprocessing != NULL)
				{
					$this->resprocessing->setOutcomes($this->outcomes);
				}
				$this->outcomes = NULL;
				break;
			case "decvar":
				if ($this->outcomes != NULL)
				{
					$this->outcomes->addDecvar($this->decvar);
				}
				$this->decvar = NULL;
				break;
			case "presentation":
				$this->in_presentation = FALSE;
				if ($this->presentation != NULL)
				{
					if ($this->item != NULL)
					{
						$this->item->setPresentation($this->presentation);
					}
				}
				$this->presentation = NULL;
				break;
			case "response_label":
				if ($this->render_type != NULL)
				{
					$this->render_type->addResponseLabel($this->response_label);
					$this->response_label = NULL;
				}
				break;
			case "render_choice":
			case "render_hotspot":
			case "render_fib":
				if ($this->in_response)
				{
					if ($this->response != NULL)
					{
						if ($this->render_type != NULL)
						{
							$this->response->setRenderType($this->render_type);
							$this->render_type = NULL;
						}
					}
				}
				break;
			case "response_lid":
			case "response_xy":
			case "response_str":
			case "response_num":
			case "response_grp":
				$this->gap_index++;
				if ($this->presentation != NULL)
				{
					if ($this->response != NULL)
					{
						$this->presentation->addResponse($this->response);
						if ($this->item != NULL)
						{
							$this->item->addPresentationitem($this->response);
						}
					}
				}
				$this->response = NULL;
				$this->in_response = FALSE;
				break;
			case "item":
				if ($this->do_nothing)
				{
					$this->do_nothing = FALSE;
					return;
				}
				if (strlen($this->item->getQuestionType()))
				{
					// this is an ILIAS QTI question
				}
				else
				{
					// this is a QTI question which wasn't generated by ILIAS
				}
				global $ilDB;
				global $ilUser;
				// save the item directly to save memory
				// the database id's of the created items are exported. if the import fails
				// ILIAS can delete the already imported items
				
				// problems: the object id of the parent questionpool is not yet known. must be set later
				//           the complete flag must be calculated?
				$qt = $this->item->determineQuestionType();
				$presentation = $this->item->getPresentation(); 
				
				include_once "./Modules/TestQuestionPool/classes/class.assQuestion.php";
				assQuestion::_includeClass($qt);
				$question = new $qt();
				$question->fromXML($this->item, $this->qpl_id, $this->tst_id, $this->tst_object, $this->question_counter, $this->import_mapping);
				break;
			case "material":
				if ($this->material)
				{
					$mat = $this->material->getMaterial(0);
					if ((strcmp($mat["type"], "mattext") == 0) && (strcmp($mat["material"]->getLabel(), "suggested_solution") == 0))
					{
						$this->item->addSuggestedSolution($mat["material"], $this->gap_index);
					}
					if ($this->in_objectives)
					{
						$this->objectives->addMaterial($this->material);
					}
					else if (($this->render_type != NULL) && (strcmp(strtolower($this->getParent($a_xml_parser)), "render_hotspot") == 0))
					{
						$this->render_type->addMaterial($this->material);
					}
					else if (count($this->flow_mat) && (strcmp(strtolower($this->getParent($a_xml_parser)), "flow_mat") == 0))
					{
						$this->flow_mat[count($this->flow_mat)-1]->addMaterial($this->material);
					}
					else if ($this->itemfeedback != NULL)
					{
						$this->itemfeedback->addMaterial($this->material);
					}
					else if ($this->response_label != NULL)
					{
						$this->response_label->addMaterial($this->material);
					}
					else if ($this->response != NULL)
					{
						if ($this->response->hasRendering())
						{
							$this->response->setMaterial2($this->material);
						}
						else
						{
							$this->response->setMaterial1($this->material);
						}
					}
					elseif (($this->in_presentation) && (!$this->in_response))
					{
						if (!is_object($this->item->getQuestiontext()))
						{
							$this->item->setQuestiontext($this->material);
						}
						$this->presentation->addMaterial($this->material);
					}
					else if ($this->presentation != NULL)
					{
						$this->presentation->addMaterial($this->material);
						if ($this->item != NULL)
						{
							$this->item->addPresentationitem($this->material);
						}
					}
				}
				$this->material = NULL;
				break;
			case "matimage";
				if ($this->material != NULL)
				{
					if ($this->matimage != NULL)
					{
						$this->material->addMatimage($this->matimage);
					}
				}
				$this->matimage = NULL;
				break;
			case "resprocessing":
				if ($this->item != NULL)
				{
					$this->item->addResprocessing($this->resprocessing);
				}
				$this->resprocessing = NULL;
				break;
			case "mattext":
				if ($this->material != NULL)
				{
					$this->material->addMattext($this->mattext);
				}
				$this->mattext = NULL;
				break;
			case "matapplet":
				if ($this->material != NULL)
				{
					$this->material->addMatapplet($this->matapplet);
				}
				$this->matapplet = NULL;
				break;
		}
		$this->depth[$a_xml_parser]--;
	}

	/**
	* handler for character data
	*/
	function handlerCharacterData($a_xml_parser,$a_data)
	{
		switch ($this->parser_mode)
		{
			case IL_MO_PARSE_QTI:
				$this->handlerParseCharacterData($a_xml_parser, $a_data);
				break;
			case IL_MO_VERIFY_QTI:
				$this->handlerVerifyCharacterData($a_xml_parser, $a_data);
				break;
		}
	}

  /**
	* handler for character data
	*/
	function handlerParseCharacterData($a_xml_parser,$a_data)
	{
		if ($this->do_nothing) return;
		$this->characterbuffer .= $a_data;
		$a_data = $this->characterbuffer;
		switch ($this->qti_element)
		{
			case "fieldlabel":
				$this->metadata["label"] = $a_data;
				break;
			case "fieldentry":
				$this->metadata["entry"] = $a_data;
				break;
			case "response_label":
				if ($this->response_label != NULL)
				{
					$this->response_label->setContent($a_data);
				}
				break;
			case "setvar":
				if ($this->setvar != NULL)
				{
					$this->setvar->setContent($a_data);
				}
				break;
			case "displayfeedback":
				if ($this->displayfeedback != NULL)
				{
					$this->displayfeedback->setContent($a_data);
				}
				break;
			case "varequal":
			case "varlt":
			case "varlte":
			case "vargt":
			case "vargte":
			case "varsubset":
			case "varinside":
			case "varsubstring":
				if ($this->responsevar != NULL)
				{
					$this->responsevar->setContent($a_data);
				}
				break;
			case "decvar":
				if (strlen($a_data))
				{
					if ($this->decvar != NULL)
					{
						$this->decvar->setContent($a_data);
					}
				}
				break;
			case "mattext":
				if ($this->mattext != NULL)
				{
					$this->mattext->setContent($a_data);
				}
				break;
			case "matapplet":
				if ($this->matapplet != NULL)
				{
					$this->matapplet->setContent($a_data);
				}
				break;
			case "matimage":
				if ($this->matimage != NULL)
				{
					$this->matimage->setContent($a_data);
				}
				break;
			case "duration":
				switch ($this->getParent($a_xml_parser))
				{
					case "assessment":
						// to be done
						break;
					case "section":
						// to be done
						break;
					case "item":
						$this->item->setDuration($a_data);
						break;
				}
				break;
			case "qticomment":
				switch ($this->getParent($a_xml_parser))
				{
					case "item":
						$this->item->setComment($a_data);
						break;
					case "assessment":
						$this->assessment->setComment($a_data);
						break;
					default:
						break;
				}
				break;
		}
		$this->sametag = TRUE;
	}

	/**
	* handler for begin of element verification
	*/
	function handlerVerifyBeginTag($a_xml_parser,$a_name,$a_attribs)
	{
		switch (strtolower($a_name))
		{
			case "questestinterop":
				$this->verifyroot = true;
				break;
			case "qtimetadatafield":
				$this->verifymetadatafield = 1;
				break;
			case "fieldlabel":
				$this->verifyfieldlabeltext = "";
				if ($this->verifymetadatafield == 1) $this->verifyfieldlabel = 1;
				break;
			case "fieldentry":
				$this->verifyfieldentrytext = "";
				if ($this->verifymetadatafield == 1) $this->verifyfieldentry = 1;
				break;
			case "item":
				$title = "";
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "title":
								$title = $value;
								break;
						}
					}
				}
				array_push($this->founditems, array("title" => "$title", "type" => "", "ident" => $a_attribs["ident"]));
				break;
			case "response_lid":
				if (strlen($this->founditems[count($this->founditems)-1]["type"]) == 0)
				{
					// test for non ILIAS generated question types
					if (is_array($a_attribs))
					{
						foreach ($a_attribs as $attribute => $value)
						{
							switch (strtolower($attribute))
							{
								case "rcardinality":
									include_once "./Services/QTI/classes/class.ilQTIItem.php";
									switch (strtolower($value))
									{
										case "single":
											$this->founditems[count($this->founditems)-1]["type"] = QT_MULTIPLE_CHOICE_SR;
											break;
										case "multiple":
											$this->founditems[count($this->founditems)-1]["type"] = QT_MULTIPLE_CHOICE_MR;
											break;
										case "ordered":
											$this->founditems[count($this->founditems)-1]["type"] = QT_ORDERING;
											break;
									}
									break;
							}
						}
					}
				}
				break;
			case "response_str":
				if (strlen($this->founditems[count($this->founditems)-1]["type"]) == 0)
				{
					// test for non ILIAS generated question types
					if (is_array($a_attribs))
					{
						foreach ($a_attribs as $attribute => $value)
						{
							switch (strtolower($attribute))
							{
								case "rcardinality":
									include_once "./Services/QTI/classes/class.ilQTIItem.php";
									switch (strtolower($value))
									{
										case "single":
											$this->founditems[count($this->founditems)-1]["type"] = QT_CLOZE;
											break;
										case "ordered":
											$this->founditems[count($this->founditems)-1]["type"] = QT_TEXT;
											break;
									}
									break;
							}
						}
					}
				}
				break;
			case "response_xy":
				if (strlen($this->founditems[count($this->founditems)-1]["type"]) == 0)
				{
					$this->founditems[count($this->founditems)-1]["type"] = QT_IMAGEMAP;
				}
				break;
			case "response_num":
				if (strlen($this->founditems[count($this->founditems)-1]["type"]) == 0)
				{
					$this->founditems[count($this->founditems)-1]["type"] = QT_NUMERIC;
				}
				break;
			case "response_grp":
				if (strlen($this->founditems[count($this->founditems)-1]["type"]) == 0)
				{
					$this->founditems[count($this->founditems)-1]["type"] = QT_MATCHING;
				}
				break;
			case "qticomment":
				// check for "old" ILIAS qti format (not well formed)
				$this->verifyqticomment = 1;
				break;
			case "presentation":
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "label":
								$this->founditems[count($this->founditems)-1]["title"] = $value;
								break;
						}
					}
				}
				break;
		}
	}

	/**
	* handler for end of element verification
	*/
	function handlerVerifyEndTag($a_xml_parser,$a_name)
	{
		switch (strtolower($a_name))
		{
			case "qticomment":
				// check for "old" ILIAS qti format (not well formed)
				$this->verifyqticomment = 0;
				break;
			case "qtimetadatafield":
				$this->verifymetadatafield = 0;
				if (strcmp($this->verifyfieldlabeltext, "QUESTIONTYPE") == 0)
				{
					$this->founditems[count($this->founditems)-1]["type"] = $this->verifyfieldentrytext;
				}
				break;
			case "fieldlabel":
				$this->verifyfieldlabel = 0;
				break;
			case "fieldentry":
				$this->verifyfieldentry = 0;
				break;
		}
	}

	/**
	* handler for character data verification
	*/
	function handlerVerifyCharacterData($a_xml_parser,$a_data)
	{
		if ($this->verifyqticomment == 1)
		{
			if (preg_match("/Questiontype\=(.*)/", $a_data, $matches))
			{
				if (count($this->founditems))
				{
					$this->founditems[count($this->founditems)-1]["type"] = $matches[1];
				}
			}
		}
		else if ($this->verifyfieldlabel == 1)
		{
			$this->verifyfieldlabeltext = $a_data;
		}
		else if ($this->verifyfieldentry == 1)
		{
			$this->verifyfieldentrytext = $a_data;
		}
	}
	
	function &getFoundItems()
	{
		return $this->founditems;
	}

	/**
	* get array of new created questions for
	* import id
	*/
	function getImportMapping()
	{
		if (!is_array($this->import_mapping))
		{
			return array();
		}
		else
		{
			return $this->import_mapping;
		}
	}
	
}
?>
