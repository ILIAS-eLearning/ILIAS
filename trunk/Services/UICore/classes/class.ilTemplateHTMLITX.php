<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Dummy class that inherits from new ITX PEAR Class (see header.inc)
*/
class ilTemplateX extends HTML_Template_ITX
{
	function callConstructor()
	{
		$this->HTML_Template_ITX();
	}
}
?>
