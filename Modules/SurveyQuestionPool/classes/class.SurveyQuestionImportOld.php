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

include_once "./Modules/Survey/classes/inc.SurveyConstants.php";

/**
* Survey Question Importer for ILIAS versions < 3.8
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @ingroup ModulesSurveyQuestionPool
*/
class SurveyQuestionImportOld
{
	var $object;
	var $lng;
	var $ilias;
	
	/**
	* Constructor
	*
	* @param	string		$a_xml_file		xml file
	*
	* @access	public
	*/
	function SurveyQuestionImportOld(&$qpl_object)
	{
		global $lng, $ilias;
		
		$this->object =& $qpl_object;
		$this->ilias =& $ilias;
		$this->lng =& $lng;
	}

	function importXML($xml)
	{
		global $ilLog;
		
		$metadata = "";

		// read questionpool metadata from xml file
		$xml = preg_replace("/>\s*?</ims", "><", $xml);
		$domxml = domxml_open_mem($xml);
		if (!empty($domxml))
		{
			$nodeList = $domxml->get_elements_by_tagname("fieldlabel");
			foreach ($nodeList as $node)
			{
				switch ($node->get_content())
				{
					case "SCORM":
						$metanode = $node->next_sibling();
						if (strcmp($metanode->node_name(), "fieldentry") == 0)
						{
							$metadata = $metanode->get_content();
						}
				}
			}
			$domxml->free();
		}

		// import file into questionpool
		if (preg_match_all("/(<item[^>]*>.*?<\/item>)/si", $xml, $matches))
		{
			foreach ($matches[1] as $index => $item)
			{
				// get identifier
				if (preg_match("/(<item[^>]*>)/is", $item, $start_tag))
				{
					if (preg_match("/(ident=\"([^\"]*)\")/is", $start_tag[1], $ident))
					{
						$ident = $ident[2];
					}
				}
				$question = "";
				if (preg_match("/<qticomment>Questiontype\=(.*?)<\/qticomment>/is", $item, $questiontype))
				{
					$type = $questiontype[1];
					// conversion of old question type identifiers
					switch ($type)
					{
						case METRIC_QUESTION_IDENTIFIER:
							$type = "SurveyMetricQuestion";
							break;
						case NOMINAL_QUESTION_IDENTIFIER:
							$type = "SurveyNominalQuestion";
							break;
						case ORDINAL_QUESTION_IDENTIFIER:
							$type = "SurveyOrdinalQuestion";
							break;
						case TEXT_QUESTION_IDENTIFIER:
							$type = "SurveyTextQuestion";
							break;
					}
					if (file_exists("./Modules/SurveyQuestionPool/classes/class.$type.php"))
					{
						include_once "./Modules/SurveyQuestionPool/classes/class.$type.php";
						$question = new $type();
						$question->setObjId($this->object->getId());
						$method = "fromXML" . $type;
						$this->$method($question, "<questestinterop>$item</questestinterop>");
					}
				}
			}
		}

		if ($metadata)
		{
			include_once "./Services/MetaData/classes/class.ilMDSaxParser.php";
			include_once "./Services/MetaData/classes/class.ilMD.php";
			$md_sax_parser = new ilMDSaxParser();
			$md_sax_parser->setXMLContent($metadata);
			$md_sax_parser->setMDObject($tmp = new ilMD($this->object->getId(),0,'spl'));
			$md_sax_parser->enableMDParsing(true);
			$md_sax_parser->startParsing();

			// Finally update title description
			// Update title description
			$this->object->MDUpdateListener('General');
		}
	}

	/**
	* Imports a nominal question from XML
	*
	* Sets the attributes of the question from the XML text passed
	* as argument
	*
	* @return boolean True, if the import succeeds, false otherwise
	* @access public
	*/
	function fromXMLSurveyNominalQuestion(&$question, $xml_text)
	{
		$result = false;
		$xml_text = preg_replace("/>\s*?</ims", "><", $xml_text);
		$domxml = domxml_open_mem($xml_text);
		if (!empty($domxml))
		{
			$root = $domxml->document_element();
			$item = $root->first_child();
			$question->setTitle($item->get_attribute("title"));
			$itemnodes = $item->child_nodes();
			foreach ($itemnodes as $index => $node)
			{
				switch ($node->node_name())
				{
					case "qticomment":
						$comment = $node->get_content();
						if (strpos($comment, "ILIAS Version=") !== false)
						{
						}
						elseif (strpos($comment, "Questiontype=") !== false)
						{
						}
						elseif (strpos($comment, "Author=") !== false)
						{
							$comment = str_replace("Author=", "", $comment);
							$question->setAuthor($comment);
						}
						else
						{
							$question->setDescription($comment);
						}
						break;
					case "itemmetadata":
						$qtimetadata = $node->first_child();
						$metadata_fields = $qtimetadata->child_nodes();
						foreach ($metadata_fields as $index => $metadata_field)
						{
							$fieldlabel = $metadata_field->first_child();
							$fieldentry = $fieldlabel->next_sibling();
							switch ($fieldlabel->get_content())
							{
								case "obligatory":
									$question->setObligatory($fieldentry->get_content());
									break;
								case "orientation":
									$question->setOrientation($fieldentry->get_content());
									break;
							}
						}
						break;
					case "presentation":
						$flow = $node->first_child();
						$flownodes = $flow->child_nodes();
						foreach ($flownodes as $idx => $flownode)
						{
							if (strcmp($flownode->node_name(), "material") == 0)
							{
								$mattext = $flownode->first_child();
								$question->setQuestiontext($mattext->get_content());
							}
							elseif (strcmp($flownode->node_name(), "response_lid") == 0)
							{
								$ident = $flownode->get_attribute("ident");
								if (strcmp($ident, "MCSR") == 0)
								{
									$question->setSubtype(1);
								}
								else
								{
									$question->setSubtype(2);
								}
								$shuffle = "";
								$response_lid_nodes = $flownode->child_nodes();
								foreach ($response_lid_nodes as $resp_lid_id => $resp_lid_node)
								{
									switch ($resp_lid_node->node_name())
									{
										case "render_choice":
											$render_choice = $resp_lid_node;
											$labels = $render_choice->child_nodes();
											foreach ($labels as $lidx => $response_label)
											{
												$material = $response_label->first_child();
												$mattext = $material->first_child();
												$shuf = 0;
												$question->categories->addCategoryAtPosition($mattext->get_content(), $response_label->get_attribute("ident"));
											}
											break;
										case "material":
											$matlabel = $resp_lid_node->get_attribute("label");
											$mattype = $resp_lid_node->first_child();
											if (strcmp($mattype->node_name(), "mattext") == 0)
											{
												$material = $mattype->get_content();
												if ($material)
												{
													if ($question->getId() < 1)
													{
														$question->saveToDb();
													}
													$question->setMaterial($material, true, $matlabel);
												}
											}
											break;
									}
								}
							}
						}
						break;
				}
			}
			$result = true;
			$domxml->free();
		}
		if ($result)
		{
			$question->saveToDb();
		}
		else
		{
			$this->ilias->raiseError($this->lng->txt("error_importing_question"), $this->ilias->error_obj->MESSAGE);
		}
		return $result;
	}	

	/**
	* Imports an ordinal question from XML
	*
	* Sets the attributes of the question from the XML text passed
	* as argument
	*
	* @return boolean True, if the import succeeds, false otherwise
	* @access public
	*/
	function fromXMLSurveyOrdinalQuestion(&$question, $xml_text)
	{
		$result = false;
		$xml_text = preg_replace("/>\s*?</ims", "><", $xml_text);
		$domxml = domxml_open_mem($xml_text);
		if (!empty($domxml))
		{
			$root = $domxml->document_element();
			$item = $root->first_child();
			$question->setTitle($item->get_attribute("title"));
			$itemnodes = $item->child_nodes();
			foreach ($itemnodes as $index => $node)
			{
				switch ($node->node_name())
				{
					case "qticomment":
						$comment = $node->get_content();
						if (strpos($comment, "ILIAS Version=") !== false)
						{
						}
						elseif (strpos($comment, "Questiontype=") !== false)
						{
						}
						elseif (strpos($comment, "Author=") !== false)
						{
							$comment = str_replace("Author=", "", $comment);
							$question->setAuthor($comment);
						}
						else
						{
							$question->setDescription($comment);
						}
						break;
					case "itemmetadata":
						$qtimetadata = $node->first_child();
						$metadata_fields = $qtimetadata->child_nodes();
						foreach ($metadata_fields as $index => $metadata_field)
						{
							$fieldlabel = $metadata_field->first_child();
							$fieldentry = $fieldlabel->next_sibling();
							switch ($fieldlabel->get_content())
							{
								case "obligatory":
									$question->setObligatory($fieldentry->get_content());
									break;
								case "orientation":
									$question->setOrientation($fieldentry->get_content());
									break;
							}
						}
						break;
					case "presentation":
						$flow = $node->first_child();
						$flownodes = $flow->child_nodes();
						foreach ($flownodes as $idx => $flownode)
						{
							if (strcmp($flownode->node_name(), "material") == 0)
							{
								$mattext = $flownode->first_child();
								$question->setQuestiontext($mattext->get_content());
							}
							elseif (strcmp($flownode->node_name(), "response_lid") == 0)
							{
								$ident = $flownode->get_attribute("ident");
								$shuffle = "";

								$response_lid_nodes = $flownode->child_nodes();
								foreach ($response_lid_nodes as $resp_lid_id => $resp_lid_node)
								{
									switch ($resp_lid_node->node_name())
									{
										case "render_choice":
											$render_choice = $resp_lid_node;
											$labels = $render_choice->child_nodes();
											foreach ($labels as $lidx => $response_label)
											{
												$material = $response_label->first_child();
												$mattext = $material->first_child();
												$shuf = 0;
												$question->categories->addCategoryAtPosition($mattext->get_content(), $response_label->get_attribute("ident"));
											}
											break;
										case "material":
											$matlabel = $resp_lid_node->get_attribute("label");
											$mattype = $resp_lid_node->first_child();
											if (strcmp($mattype->node_name(), "mattext") == 0)
											{
												$material = $mattype->get_content();
												if ($material)
												{
													if ($question->getId() < 1)
													{
														$question->saveToDb();
													}
													$question->setMaterial($material, true, $matlabel);
												}
											}
											break;
									}
								}
							}
						}
						break;
				}
			}
			$result = true;
			$domxml->free();
		}
		if ($result)
		{
			$question->saveToDb();
		}
		else
		{
			$this->ilias->raiseError($this->lng->txt("error_importing_question"), $this->ilias->error_obj->MESSAGE);
		}
		return $result;
	}

	/**
	* Imports a metric question from XML
	*
	* Sets the attributes of the question from the XML text passed
	* as argument
	*
	* @return boolean True, if the import succeeds, false otherwise
	* @access public
	*/
	function fromXMLSurveyMetricQuestion(&$question, $xml_text)
	{
		$result = false;
		$xml_text = preg_replace("/>\s*?</ims", "><", $xml_text);
		$domxml = domxml_open_mem($xml_text);
		if (!empty($domxml))
		{
			$root = $domxml->document_element();
			$item = $root->first_child();
			$question->setTitle($item->get_attribute("title"));
			$itemnodes = $item->child_nodes();
			foreach ($itemnodes as $index => $node)
			{
				switch ($node->node_name())
				{
					case "qticomment":
						$comment = $node->get_content();
						if (strpos($comment, "ILIAS Version=") !== false)
						{
						}
						elseif (strpos($comment, "Questiontype=") !== false)
						{
						}
						elseif (strpos($comment, "Author=") !== false)
						{
							$comment = str_replace("Author=", "", $comment);
							$question->setAuthor($comment);
						}
						else
						{
							$question->setDescription($comment);
						}
						break;
					case "itemmetadata":
						$qtimetadata = $node->first_child();
						$metadata_fields = $qtimetadata->child_nodes();
						foreach ($metadata_fields as $index => $metadata_field)
						{
							$fieldlabel = $metadata_field->first_child();
							$fieldentry = $fieldlabel->next_sibling();
							switch ($fieldlabel->get_content())
							{
								case "obligatory":
									$question->setObligatory($fieldentry->get_content());
									break;
								case "subtype":
									$question->setSubtype($fieldentry->get_content());
									break;
							}
						}
						break;
					case "presentation":
						$flow = $node->first_child();
						$flownodes = $flow->child_nodes();
						foreach ($flownodes as $idx => $flownode)
						{
							if (strcmp($flownode->node_name(), "material") == 0)
							{
								$mattext = $flownode->first_child();
								$question->setQuestiontext($mattext->get_content());
							}
							elseif (strcmp($flownode->node_name(), "response_num") == 0)
							{
								$ident = $flownode->get_attribute("ident");
								$shuffle = "";

								$response_lid_nodes = $flownode->child_nodes();
								foreach ($response_lid_nodes as $resp_lid_id => $resp_lid_node)
								{
									switch ($resp_lid_node->node_name())
									{
										case "render_fib":
											$render_choice = $resp_lid_node;
											$minnumber = $render_choice->get_attribute("minnumber");
											$question->setMinimum($minnumber);
											$maxnumber = $render_choice->get_attribute("maxnumber");
											$question->setMaximum($maxnumber);
											break;
										case "material":
											$matlabel = $resp_lid_node->get_attribute("label");
											$mattype = $resp_lid_node->first_child();
											if (strcmp($mattype->node_name(), "mattext") == 0)
											{
												$material = $mattype->get_content();
												if ($material)
												{
													if ($question->getId() < 1)
													{
														$question->saveToDb();
													}
													$question->setMaterial($material, true, $matlabel);
												}
											}
											break;
									}
								}
							}
						}
						break;
				}
			}
			$result = true;
			$domxml->free();
		}
		if ($result)
		{
			$question->saveToDb();
		}
		else
		{
			$this->ilias->raiseError($this->lng->txt("error_importing_question"), $this->ilias->error_obj->MESSAGE);
		}
		return $result;
	}
	
	/**
	* Imports an essay question from XML
	*
	* Sets the attributes of the question from the XML text passed
	* as argument
	*
	* @return boolean True, if the import succeeds, false otherwise
	* @access public
	*/
	function fromXMLSurveyTextQuestion(&$question, $xml_text)
	{
		$result = false;
		$xml_text = preg_replace("/>\s*?</ims", "><", $xml_text);
		$domxml = domxml_open_mem($xml_text);
		if (!empty($domxml))
		{
			$root = $domxml->document_element();
			$item = $root->first_child();
			$question->setTitle($item->get_attribute("title"));
			$itemnodes = $item->child_nodes();
			foreach ($itemnodes as $index => $node)
			{
				switch ($node->node_name())
				{
					case "qticomment":
						$comment = $node->get_content();
						if (strpos($comment, "ILIAS Version=") !== false)
						{
						}
						elseif (strpos($comment, "Questiontype=") !== false)
						{
						}
						elseif (strpos($comment, "Author=") !== false)
						{
							$comment = str_replace("Author=", "", $comment);
							$question->setAuthor($comment);
						}
						else
						{
							$question->setDescription($comment);
						}
						break;
					case "itemmetadata":
						$qtimetadata = $node->first_child();
						$metadata_fields = $qtimetadata->child_nodes();
						foreach ($metadata_fields as $index => $metadata_field)
						{
							$fieldlabel = $metadata_field->first_child();
							$fieldentry = $fieldlabel->next_sibling();
							switch ($fieldlabel->get_content())
							{
								case "obligatory":
									$question->setObligatory($fieldentry->get_content());
									break;
								case "maxchars":
									$question->setMaxChars($fieldentry->get_content());
									break;
							}
						}
						break;
					case "presentation":
						$flow = $node->first_child();
						$flownodes = $flow->child_nodes();
						foreach ($flownodes as $idx => $flownode)
						{
							if (strcmp($flownode->node_name(), "material") == 0)
							{
								$mattext = $flownode->first_child();
								$question->setQuestiontext($mattext->get_content());
							}
							elseif (strcmp($flownode->node_name(), "response_str") == 0)
							{
								$ident = $flownode->get_attribute("ident");
								$response_lid_nodes = $flownode->child_nodes();
								foreach ($response_lid_nodes as $resp_lid_id => $resp_lid_node)
								{
									switch ($resp_lid_node->node_name())
									{
										case "material":
											$matlabel = $resp_lid_node->get_attribute("label");
											$mattype = $resp_lid_node->first_child();
											if (strcmp($mattype->node_name(), "mattext") == 0)
											{
												$material = $mattype->get_content();
												if ($material)
												{
													if ($question->getId() < 1)
													{
														$question->saveToDb();
													}
													$question->setMaterial($material, true, $matlabel);
												}
											}
											break;
									}
								}
							}
						}
						break;
				}
			}
			$result = true;
			$domxml->free();
		}
		if ($result)
		{
			$question->saveToDb();
		}
		else
		{
			$this->ilias->raiseError($this->lng->txt("error_importing_question"), $this->ilias->error_obj->MESSAGE);
		}
		return $result;
	}
	
}
?>
