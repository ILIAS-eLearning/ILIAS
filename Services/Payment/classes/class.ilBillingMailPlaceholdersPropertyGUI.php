<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* 
* @author Nadia Ahmad <nahmad@databay.de>
* @version $Id:$
* 
* 
* @ingroup ServicesPayment
*/
include_once 'Services/Form/classes/class.ilFormPropertyGUI.php';

class ilBillingMailPlaceholdersPropertyGUI extends ilFormPropertyGUI
{
	
	public function __construct()
	{
		global $lng;
		parent::__construct('');
	}
	
	public function insert($a_tpl)
	{
		global $lng;

		$subtpl = new ilTemplate("tpl.billingmail_new_placeholders.html", false, false, "Services/Payment");
		$subtpl->setVariable('TXT_USE_PLACEHOLDERS', $lng->txt('mail_nacc_use_placeholder'));
		$subtpl->setVariable('TXT_PLACEHOLDERS_ADVISE', sprintf($lng->txt('placeholders_advise'), '<br />'));
		$subtpl->setVariable('TXT_MAIL_SALUTATION', $lng->txt('mail_nacc_salutation'));
		$subtpl->setVariable('TXT_FIRST_NAME', $lng->txt('firstname'));
		$subtpl->setVariable('TXT_LAST_NAME', $lng->txt('lastname'));
		$subtpl->setVariable('TXT_LOGIN', $lng->txt('mail_nacc_login'));
		$subtpl->setVariable('TXT_ILIAS_URL', $lng->txt('mail_nacc_ilias_url'));
		$subtpl->setVariable('TXT_CLIENT_NAME', $lng->txt('mail_nacc_client_name'));

		#$subtpl->setVariable('TXT_TRANSACTION', $lng->txt('transaction'));
		#$subtpl->setVariable('TXT_TRANSACTION_EXTERN', $lng->txt('transaction_extern'));
		#$subtpl->setVariable('TXT_ORDER_DATE', $lng->txt('order_date'));

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $subtpl->get());
		$a_tpl->parseCurrentBlock();	
	}


	public function replaceBillingMailPlaceholders()
	{

	}



}

?>
