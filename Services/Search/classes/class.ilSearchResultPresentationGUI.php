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

/**
* Class ilSearchResultPresaentationGUI
*
* class for presentastion of search results. Called from class.ilSearchGUI or class.ilAdvancedSearchGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @extends ilObjectGUI
* @package ilias-core
*/

class ilSearchResultPresentationGUI
{
	var $tpl;
	var $lng;

	var $result = 0;

	function ilSearchResultPresentationGUI(&$result)
	{
		global $tpl,$lng;

		$this->tpl =& $tpl;
		$this->lng =& $lng;
		
		$this->result =& $result;

		$this->type_ordering = array(
			"cat", "crs", "grp", "chat", "frm", "lres",
			"glo", "webr", "file", "exc",
			"tst", "svy", "mep", "qpl", "spl");

	}

	function showResults()
	{
		// Get results
		$results = $this->result->getResultsForPresentation();

		$this->renderItemList($results);
	}

	function renderItemList(&$results)
	{
		global $objDefinition;

		$html = '';

		$cur_obj_type = "";
		$tpl =& $this->newBlockTemplate();
		$first = true;
		
		foreach($this->type_ordering as $act_type)
		{
			$item_html = array();

			if (is_array($results[$act_type]))
			{
				foreach($results[$act_type] as $key => $item)
				{
					// I use here a searchObjListFactory to disable link, delete ... for all object types

					// get list gui class for each object type
					if ($cur_obj_type != $item["type"])
					{
						include_once 'Services/Search/classes/class.ilSearchObjectListFactory.php';

						
					}
					

					// render item row
							$ilBench->start("ilContainerGUI", "0210_getListHTML");
							$html = $item_list_gui->getListItemHTML($item["ref_id"],
								$item["obj_id"], $item["title"], $item["description"]);
							$ilBench->stop("ilContainerGUI", "0210_getListHTML");
							if ($html != "")
							{
								$item_html[] = $html;
							}
						}

						// output block for resource type
						if (count($item_html) > 0)
						{
							// separator row
							if (!$first)
							{
								$this->addSeparatorRow($tpl);
							}
							$first = false;

							// add a header for each resource type
							$this->addHeaderRow($tpl, $type);
							$this->resetRowType();

							// content row
							foreach($item_html as $html)
							{
								$this->addStandardRow($tpl, $html);
							}


						}
					}
				}
				$html = $tpl->get();

				break;

			default:
				// to do:
				break;
		}

		return $html;

}

?>