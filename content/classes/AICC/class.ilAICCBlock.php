<?php

require_once("content/classes/AICC/class.ilAICCObject.php");

class ilAICCBlock extends ilAICCObject
{

/**
* AICC Block
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @extends ilAICCObject
* @package content
*/

	/**
	* Constructor
	*
	* @param	int		$a_id		Object ID
	* @access	public
	*/
	function ilAICCBlock($a_id = 0)
	{
		parent::ilAICCObject($a_id);

		$this->type="sbl";

	}
	

}
?>
