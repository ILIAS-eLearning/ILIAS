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
* base script for terms and conditions
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias-core
*/
class ilTermsCondition
{
	var $tpl = null;
	var $user_obj = null;

	function ilTermsCondition(&$user_obj)
	{
		global $tpl;

		define('DEFAULT_LANG','de');

		$this->user_obj =& $user_obj;
		$this->tpl =& $tpl;
	}
	
	function show()
	{
		$lang = $this->user_obj->getLanguage();

		if(file_exists($this->tpl->getTemplatePath('tpl.pay_terms_conditions_'.$lang.'.html',true)))
		{
			$this->tpl->addBlockFile("CONTENT", "content",'tpl.pay_terms_conditions_'.$lang.'.html',true);
			$this->tpl->touchBlock('tc');
		}
		else
		{
			$this->tpl->addBlockFile("CONTENT", "content",'tpl.pay_terms_conditions_'.DEFAULT_LANG.'.html',true);
			$this->tpl->touchBlock('tc');
		}
	}
}
?>
