<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Dummy access handler
 *
 * This can be used in contexts where no (proper) access handling is possible
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilPersonalDesktopGUI.php 26976 2010-12-16 13:24:38Z akill $
 */
class ilDummyAccessHandler
{
	/**
	 * check access for an object
	 *
	 * @param	string		$a_permission
	 * @param	string		$a_cmd
	 * @param	int			$a_node_id
	 * @param	string		$a_type (optional)
	 * @return	bool
	 */
	public function checkAccess($a_permission, $a_cmd, $a_node_id, $a_type = "")
	{
		return true;
	}
}

?>