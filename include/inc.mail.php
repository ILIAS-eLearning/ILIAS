<?php
function setLocator($a_obj_id,$a_user_id,$a_txt_prefix)
{
		global $lng,$tpl;

		$tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		$mtree = new Tree($a_user_id);
		$mtree->setTableNames('mail_tree','mail_obj_data');
		$path_full = $mtree->getPathFull($a_obj_id,$mtree->readRootId());
		
		unset($path_full[0]);
		foreach ($path_full as $key => $row)
		{
			if($row["type"] != 'user_folder')
			{
				$row["title"] = $lng->txt("mail_".$row["title"]);
			}
			if ($key < count($path_full))
			{
				$tpl->touchBlock("locator_separator");
			}
			$tpl->setCurrentBlock("locator_item");
			$tpl->setVariable("ITEM", $row["title"]);
			// TODO: SCRIPT NAME HAS TO BE VARIABLE!!!
			$tpl->setVariable("LINK_ITEM", "mail.php?mobj_id=".$row["child"]);
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("locator");
		

		$tpl->setVariable("TXT_PATH",$a_txt_prefix);
		$tpl->parseCurrentBlock();
}
/**
 * Builds a select form field with options and shows the selected option first
 * @param	string	value to be selected
 * @param	string	variable name in formular
 * @param	array	array with $options (key = lang_key, value = long name)
 * @param	boolean
 */
function formSelect ($selected,$varname,$options,$multiple = false)
{
	global $lng;
		
	$multiple ? $multiple = " multiple=\"multiple\"" : "";
	$str = "<select name=\"".$varname ."\"".$multiple.">\n";

	foreach ($options as $key => $val)
	{
		
		$str .= " <option value=\"".$val."\"";
			
		if ($selected == $key)
		{
			$str .= " selected=\"selected\"";
		}
			
		$str .= ">".$val."</option>\n";
	}

	$str .= "</select>\n";
		
	return $str;
}

function assignMailToPost($a_mail_data)
{
	foreach($a_mail_data as $key => $data)
	{
		$_POST[$key] = $data;
	}
}

?>