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

/**
* QTI helper class to support operations on QTI files
*
* QTI helper class to support operations on QTI files
*
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version	$Id$
* @module   class.ilQTIUtils.php
* @modulegroup   Assessment
*/
class ilQTIUtils
{
	/**
	* Retrieves the information on an respcondition node in a DOM XML QTI document
	*
	* Retrieves the information on an respcondition node in a DOM XML QTI document
	*
	* @param object $respcondition_node A DOM XML node representing a respcondition tag
	* @return array The data representation of the respcondition tag
	* @access public
	*/
	function &_getRespcondition($respcondition_node)
	{
		$result = array();
		$children = $respcondition_node->child_nodes();
		$not = 0;
		foreach ($children as $index => $node)
		{
			switch ($node->node_name())
			{
				case "conditionvar":
					$not = 0;
					$operation = $node->first_child();
					$selected = 1;
					if (strcmp($operation->node_name(), "not") == 0)
					{
						$selected = 0;
						$operation = $operation->first_child();
						$not = 1;
					}
					if (strcmp($operation->node_name(), "varequal") == 0)
					{
						$respident = $operation->get_attribute("respident");
						$idx = $operation->get_attribute("index");
						$value = $operation->get_content();
					}
					elseif (strcmp($operation->node_name(), "varsubset") == 0)
					{
						$respident = $operation->get_attribute("respident");
						$value = $operation->get_content();
					}
					elseif (strcmp($operation->node_name(), "varinside") == 0)
					{
						$respident = $operation->get_attribute("respident");
						$areatype = $operation->get_attribute("areatype");
						$value = $operation->get_content();
					}
					$result["conditionvar"]["selected"] = $selected;
					$result["conditionvar"]["respident"] = $respident;
					$result["conditionvar"]["areatype"] = $areatype;
					$result["conditionvar"]["index"] = $idx;					
					$result["conditionvar"]["not"] = $not;					
					$result["conditionvar"]["value"] = $value;					
					break;
				case "setvar":
					$action = $node->get_attribute("action");
					$points = $node->get_content();
					$result["setvar"]["action"] = $action;
					$result["setvar"]["points"] = $points;
					break;
				case "displayfeedback":
					$feedbacktype = $node->get_attribute("feedbacktype");
					$linkrefid = $node->get_attribute("linkrefid");
					$result["displayfeedback"]["feedbacktype"] = $feedbacktype;
					$result["displayfeedback"]["linkrefid"] = $linkrefid;
					break;
			}
		}
		return $result;
	}
}

?>
