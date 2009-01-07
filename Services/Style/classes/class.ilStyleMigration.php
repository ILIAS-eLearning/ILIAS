<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
* Style Migration Class (->3.11)
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesStyle
*/
class ilStyleMigration
{
	function addMissingStyleCharacteristics()
	{
		global $ilDB;
		
		$st = $ilDB->prepare("SELECT DISTINCT style_id, tag, class FROM style_parameter WHERE type = ''");
		$set = $ilDB->execute($st);
		while ($rec = $ilDB->fetchAssoc($set))
		{
//echo "<br><b>".$rec["tag"]."-".$rec["class"]."-</b>";
			// derive types from tag
			$types = array();
			switch ($rec["tag"])
			{
				case "div":
					if (in_array($rec["class"], array("Headline3", "Headline1",
						"Headline2", "TableContent", "List", "Standard", "Remark",
						"Additional", "Mnemonic", "Citation", "Example")))
					{
						$types[] = "text_block";
					}
					if (in_array($rec["class"], array("Block", "Remark",
						"Additional", "Mnemonic", "Example", "Excursus", "Special")))
					{
						$types[] = "section";
					}
					if (in_array($rec["class"], array("Page", "Footnote", "PageTitle", "LMNavigation")))
					{
						$types[] = "page";
					}
					break;
					
				case "td":
					$types[] = "table_cell";
					break;
					
				case "a":
					if (in_array($rec["class"], array("ExtLink", "IntLink", "FootnoteLink")))
					{
						$types[] = "link";
					}
					break;

				case "span":
					$types[] = "text_inline";
					break;

				case "table":
					$types[] = "table";
					break;
			}

			// check if style_char set exists
			foreach ($types as $t)
			{
				// check if second type already exists
				$st = $ilDB->prepare("SELECT * FROM style_char ".
					" WHERE style_id = ? AND type = ? AND characteristic = ?",
					array("integer", "text", "text"));
				$set4 = $ilDB->execute($st,
					array($rec["style_id"], $t, $rec["class"]));
				if ($rec4 = $ilDB->fetchAssoc($set4))
				{
					// ok
				}
				else
				{
//echo "<br>1-".$rec["style_id"]."-".$t."-".$rec["class"]."-";
					$st = $ilDB->prepareManip("INSERT INTO style_char ".
						" (style_id, type, characteristic) VALUES ".
						" (?,?,?) ",
						array("integer", "text", "text"));
					$ilDB->execute($st,
						array($rec["style_id"], $t, $rec["class"]));
				}
			}
			
			// update types
			if ($rec["type"] == "")
			{
				if (count($types) > 0)
				{
					$st = $ilDB->prepareManip("UPDATE style_parameter SET type = ? ".
						" WHERE style_id = ? AND class = ? AND type = ?",
						array("text", "integer", "text", "text"));
					$ilDB->execute($st,
						array($types[0], $rec["style_id"], $rec["class"], ""));
//echo "<br>2-".$types[0]."-".$rec["style_id"]."-".$rec["class"]."-";

					// links extra handling
					if ($types[0] == "link")
					{
						$st = $ilDB->prepareManip("UPDATE style_parameter SET type = ? ".
							" WHERE style_id = ? AND (class = ? OR class = ?) AND type = ?",
							array("text", "integer", "text", "text", "text"));
						$ilDB->execute($st,
							array($types[0], $rec["style_id"], $rec["class"].":visited",
							$rec["class"].":hover", ""));
//echo "<br>4-".$types[0]."-".$rec["style_id"]."-".$rec["class"].":visited"."-".
//	$rec["class"].":hover";
					}
				}
//echo "A";
				if (count($types) == 2)
				{
//echo "B";
					// select all records of first type and add second type 
					// records if necessary.
					$st = $ilDB->prepare("SELECT * FROM style_parameter ".
						" WHERE style_id = ? AND class = ? AND type = ?",
						array("integer", "text", "text"));
					$set2 = $ilDB->execute($st,
						array($rec["style_id"], $rec["class"], $types[0]));
					while ($rec2 = $ilDB->fetchAssoc($set2))
					{
//echo "C";
						// check if second type already exists
						$st = $ilDB->prepare("SELECT * FROM style_parameter ".
							" WHERE style_id = ? AND tag = ? AND class = ? AND type = ? AND parameter = ?",
							array("integer", "text", "text", "text", "text", "text"));
						$set3 = $ilDB->execute($st,
							array($rec["style_id"], $rec["tag"], $rec["class"], $types[1], $rec["parameter"]));
						if ($rec3 = $ilDB->fetchAssoc($set3))
						{
							// ok
						}
						else
						{
//echo "D";
							$st = $ilDB->prepareManip("INSERT INTO style_parameter ".
								" (style_id, tag, class, parameter, value, type) VALUES ".
								" (?,?,?,?,?,?) ",
								array("integer", "text", "text", "text", "text", "text"));
							$ilDB->execute($st,
								array($rec2["style_id"], $rec2["tag"], $rec2["class"],
									$rec2["parameter"], $rec2["value"], $types[1]));
//echo "<br>3-".$rec2["style_id"]."-".$rec2["tag"]."-".$rec2["class"]."-".
								$rec2["parameter"]."-"."-".$rec2["value"]."-".$types[1]."-";
						}
					}
				}
			}
		}
	}
}
?>
