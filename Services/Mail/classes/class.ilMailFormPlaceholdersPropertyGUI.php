<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* 
* @author Jan Posselt <jposselt@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesMail
*/
include_once 'Services/Form/classes/class.ilFormPropertyGUI.php';

class ilMailFormPlaceholdersPropertyGUI extends ilFormPropertyGUI
{
	
	public function __construct()
	{
		global $lng;
		parent::__construct('');
	}
	
	public function insert($a_tpl)
	{
		global $lng;
		$subtpl = new ilTemplate("tpl.mail_new_placeholders.html", false, false, "Services/Mail");
		$subtpl->setVariable('TXT_USE_PLACEHOLDERS', $lng->txt('mail_nacc_use_placeholder'));
		$subtpl->setVariable('TXT_PLACEHOLDERS_ADVISE', sprintf($lng->txt('placeholders_advise'), '<br />'));
		$subtpl->setVariable('TXT_MAIL_SALUTATION', $lng->txt('mail_nacc_salutation'));
		$subtpl->setVariable('TXT_FIRST_NAME', $lng->txt('firstname'));
		$subtpl->setVariable('TXT_LAST_NAME', $lng->txt('lastname'));
		$subtpl->setVariable('TXT_LOGIN', $lng->txt('mail_nacc_login'));
		$subtpl->setVariable('TXT_ILIAS_URL', $lng->txt('mail_nacc_ilias_url'));
		$subtpl->setVariable('TXT_CLIENT_NAME', $lng->txt('mail_nacc_client_name'));
		
		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $subtpl->get());
		$a_tpl->parseCurrentBlock();	
	}
}

?>
