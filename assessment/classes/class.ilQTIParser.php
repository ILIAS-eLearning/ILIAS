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
	var $activeItem;
	var $depth;
	var $qti_element;
	var $in_presentation;
	var $in_flow;
	var $in_response;
	var $render_choice;
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
	
	/**
	* Constructor
	*
	* @param	string		$a_xml_file			xml file
	* @access	public
	*/
	function ilQTIParser($a_xml_file)
	{
		global $lng;

		parent::ilSaxParser($a_xml_file);

		$this->lng =& $lng;
		$this->hasRootElement = FALSE;
		$this->path = array();
		$this->items = array();
		$this->activeItem = null;
		$this->depth = array();
		$this->qti_element = "";
		$this->in_presentation = FALSE;
		$this->in_flow = FALSE;
		$this->in_reponse = FALSE;
		$this->render_choice = null;
		$this->response_label = null;
		$this->material = null;
		$this->response = null;
		$this->matimage = null;
		$this->resprocessing = null;
		$this->outcomes = null;
		$this->decvar = null;
		$this->respcondition = null;
		$this->setvar = null;
		$this->displayfeedback = null;
		$this->itemfeedback = null;
		$this->flow_mat = array();
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
		parent::startParsing();
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
		$this->depth[$a_xml_parser]++;
		$this->path[$this->depth[$a_xml_parser]] = strtolower($a_name);
		$this->qti_element = $a_name;
		
		switch (strtolower($a_name))
		{
			case "flow_mat":
				include_once ("./assessment/classes/class.ilQTIFlowMat.php");
				array_push($this->flow_mat, new ilQTIFlowMat());
				break;
			case "itemfeedback":
				include_once ("./assessment/classes/class.ilQTIItemfeedback.php");
				$this->itemfeedback = new ilQTIItemfeedback();
				break;
			case "displayfeedback":
				include_once ("./assessment/classes/class.ilQTIDisplayfeedback.php");
				$this->displayfeedback = new ilQTIDisplayfeedback();
				break;
			case "setvar":
				include_once ("./assessment/classes/class.ilQTISetvar.php");
				$this->setvar = new ilQTISetvar();
				break;
			case "varequal":
				include_once("./assessment/classes/class.ilQTIResponseVar.php");
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
				include_once("./assessment/classes/class.ilQTIResponseVar.php");
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
				include_once("./assessment/classes/class.ilQTIResponseVar.php");
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
				include_once("./assessment/classes/class.ilQTIResponseVar.php");
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
				include_once("./assessment/classes/class.ilQTIResponseVar.php");
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
				include_once("./assessment/classes/class.ilQTIResponseVar.php");
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
				include_once("./assessment/classes/class.ilQTIResponseVar.php");
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
				include_once("./assessment/classes/class.ilQTIResponseVar.php");
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
				include_once("./assessment/classes/class.ilQTIRespcondition.php");
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
				include_once("./assessment/classes/class.ilQTIOutcomes.php");
				$this->outcomes = new ilQTIOutcomes();
				break;
			case "decvar":
				include_once("./assessment/classes/class.ilQTIDecvar.php");
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
				include_once("./assessment/classes/class.ilQTIMatimage.php");
				$this->matimage = new ilQTIMatimage();
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "imagetype":
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
				include_once("./assessment/classes/class.ilQTIMaterial.php");
				$this->material = new ilQTIMaterial();
				break;
			case "questestinterop":
				$this->hasRootElement = TRUE;
				break;
			case "qticomment":
				break;
			case "objectbank":
				// not implemented yet
				break;
			case "assessment":
				break;
			case "section":
				break;
			case "presentation":
				$this->in_presentation = TRUE;
				break;
			case "flow":
				$this->in_flow = TRUE;
				break;
			case "response_label":
				if ($this->render_choice != null)
				{
				include_once("./assessment/classes/class.ilQTIResponseLabel.php");
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
					include_once("./assessment/classes/class.ilQTIRenderChoice.php");
					$this->render_choice = new ilQTIRenderChoice();
				}
				break;
			case "response_lid":
				$this->in_response = TRUE;
				include_once("./assessment/classes/class.ilQTIResponse.php");
				$this->response = new ilQTIResponse(RT_RESPONSE_LID);
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
								// to be done
								break;
							case "rcardinality":
								$this->response->setRCardinality($value);
								break;
						}
					}
				}
				break;
			case "response_xy":
				$this->in_response = TRUE;
				break;
			case "response_str":
				$this->in_response = TRUE;
				break;
			case "response_num":
				$this->in_response = TRUE;
				break;
			case "response_grp":
				$this->in_response = TRUE;
				break;
			case "item":
				include_once("./assessment/classes/class.ilQTIItem.php");
				$this->activeItem =& $this->items[array_push($this->items, new ilQTIItem())-1];
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "ident":
								$this->activeItem->setIdent($value);
								break;
							case "title":
								$this->activeItem->setTitle($value);
								break;
						}
					}
				}
				break;
			case "resprocessing":
				include_once("./assessment/classes/class.ilQTIResprocessing.php");
				$this->resprocessing = new ilQTIResprocessing();
				if (is_array($a_attribs))
				{
					foreach ($a_attribs as $attribute => $value)
					{
						switch (strtolower($attribute))
						{
							case "scoremodel":
								$this->resprocessing->addScoremodel($value);
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
		$this->depth[$a_xml_parser]--;
		switch (strtolower($a_name))
		{
			case "flow_mat":
				if (count($this->flow_mat))
				{
					$flow_mat = array_pop($this->flow_mat);
					if (count($this->flow_mat))
					{
						$this->flow_mat[count($this->flow_mat)-1]->addFlow_mat($flow_mat);
					}
					else if ($this->itemfeedback != null)
					{
						$this->itemfeedback->addFlow_mat($flow_mat);
					}
					else if ($this->response_label != null)
					{
						$this->response_label->addFlow_mat($flow_mat);
					}
				}
				break;
			case "itemfeedback":
				if ($this->activeItem != null)
				{
					if ($this->itemfeedback != null)
					{
						$this->activeItem->addItemfeedback($this->itemfeedback);
					}
				}
				$this->itemfeedback = null;
				break;
			case "displayfeedback":
				if ($this->respcondition != null)
				{
					if ($this->displayfeedback != null)
					{
						$this->respcondition->addDisplayfeedback($this->displayfeedback);
					}
				}
				$this->displayfeedback = null;
				break;
			case "setvar":
				if ($this->respcondition != null)
				{
					if ($this->setvar != null)
					{
						$this->respcondition->addSetvar($this->setvar);
					}
				}
				$this->setvar = null;
				break;
			case "varequal":
			case "varlt":
			case "varlte":
			case "vargt":
			case "vargte":
			case "varsubset":
			case "varinside":
			case "varsubstring":
				if ($this->conditionvar != null)
				{
					if ($this->responsevar != null)
					{
						$this->conditionvar->addResponseVar($this->responsevar);
					}
				}
				$this->responsevar = null;
				break;
			case "respcondition":
				if ($this->resprocessing != null)
				{
					$this->resprocessing->addRespcondition($this->respcondition);
				}
				$this->respcondition = null;
				break;
			case "outcomes":
				if ($this->resprocessing != null)
				{
					$this->resprocessing->setOutcomes($this->outcomes);
				}
				$this->outcomes = null;
				break;
			case "decvar":
				if ($this->outcomes != null)
				{
					$this->outcomes->addDecvar($this->decvar);
				}
				$this->decvar = null;
				break;
			case "presentation":
				$this->in_presentation = FALSE;
				break;
			case "flow":
				$this->in_flow = FALSE;
				break;
			case "response_label":
				if ($this->render_choice != null)
				{
					$this->render_choice->addResponseLabel($this->response_label);
					$this->response_label = null;
				}
				break;
			case "render_choice":
				if ($this->in_response)
				{
					if ($this->response != null)
					{
						if ($this->render_choice != null)
						{
							$this->response->addRenderChoice($this->render_choice);
							$this->render_choice = null;
						}
					}
				}
				break;
			case "response_lid":
				if ($this->activeItem != null)
				{
					$this->activeItem->addResponse($this->response);
				}
				$this->response = null;
				break;
			case "response_xy":
			case "response_str":
			case "response_num":
			case "response_grp":
				$this->in_response = FALSE;
				break;
			case "item":
				$this->activeItem = null;
				break;
			case "material":
				if (count($this->flow_mat))
				{
					$this->flow_mat[count($this->flow_mat)-1]->addMaterial($this->material);
				}
				else if ($this->itemfeedback != null)
				{
					$this->itemfeedback->addMaterial($this->material);
				}
				else if ($this->response_label != null)
				{
					$this->response_label->addMaterial($this->material);
				}
				$this->material = null;
				break;
			case "matimage";
				if ($this->material != null)
				{
					if ($this->matimage != null)
					{
						$this->material->addMatimage($this->matimage);
					}
				}
				$this->matimage = null;
				break;
			case "resprocessing":
				if ($this->activeItem != null)
				{
					$this->activeItem->addResprocessing($this->resprocessing);
				}
				$this->resprocessing = null;
				break;
		}
	}

	/**
	* handler for character data
	*/
	function handlerCharacterData($a_xml_parser,$a_data)
	{
		switch ($this->qti_element)
		{
			case "setvar":
				if ($this->setvar != null)
				{
					$this->setvar->setContent($a_data);
				}
				break;
			case "displayfeedback":
				if ($this->displayfeedback != null)
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
				if ($this->responsevar != null)
				{
					$this->responsevar->setContent($a_data);
				}
				break;
			case "decvar":
				if (strlen($a_data))
				{
					if ($this->outcomes != null)
					{
						$this->outcomes->setContent($a_data);
					}
				}
				break;
			case "mattext":
				if ($this->response_label != null)
				{
					$this->material->addMattext($a_data);
				}
				else if (($this->in_presentation) && (!$this->in_response))
				{
					// question text
					$this->activeItem->setQuestiontext($a_data);
				}
				break;
			case "matimage":
				$this->matimage->setContent($a_data);
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
						$this->activeItem->setDuration($a_data);
						break;
				}
				break;
			case "qticomment":
				switch ($this->getParent($a_xml_parser))
				{
					case "item":
						$this->activeItem->setComment($a_data);
						break;
					default:
						break;
				}
				break;
		}
	}

}
?>
