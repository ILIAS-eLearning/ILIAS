<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* base script for terms and conditions
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id: class.ilTermsCondition.php 5974 2004-11-24 17:03:42Z smeyer $
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

		$this->user_obj = $user_obj;
		$this->tpl = $tpl;
	}
	
	function show()
	{
		$lang = $this->user_obj->getLanguage();

		if(file_exists($this->tpl->getTemplatePath('tpl.pay_terms_conditions_'.$lang.'.html',false)))
		{
			$this->tpl->addBlockFile("CONTENT", "content",'tpl.pay_terms_conditions_'.$lang.'.html','Services/Payment');
			$this->tpl->touchBlock('tc');
		}
		else
		{
			$this->tpl->addBlockFile("CONTENT", "content",'tpl.pay_terms_conditions_'.DEFAULT_LANG.'.html','Services/Payment');
			$this->tpl->touchBlock('tc');
		}
	}
}
?>
