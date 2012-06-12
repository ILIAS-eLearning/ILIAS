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


	public static function replaceBillingMailPlaceholders($a_message, $a_user_id)
	{
		global $lng;

		$user = new ilObjUser($a_user_id);

		// determine salutation
		switch ($user->getGender())
		{
			case 'f':	$gender_salut = $lng->txt('salutation_f');
						break;
			case 'm':	$gender_salut = $lng->txt('salutation_m');
						break;
        }

		$a_message = str_replace('[MAIL_SALUTATION]', $gender_salut, $a_message);
		$a_message = str_replace('[LOGIN]', $user->getLogin(), $a_message);
		$a_message = str_replace('[FIRST_NAME]', $user->getFirstname(), $a_message);
		$a_message = str_replace('[LAST_NAME]', $user->getLastname(), $a_message);
		$a_message = str_replace('[ILIAS_URL]', ILIAS_HTTP_PATH.'/login.php?client_id='.CLIENT_ID, $a_message);
		$a_message = str_replace('[CLIENT_NAME]', CLIENT_NAME, $a_message);


		include_once './Services/Payment/classes/class.ilShopLinkBuilder.php';
		$shopLB = new ilShopLinkBuilder();
		$bought_objects_url = $shopLB->buildLink('ilShopBoughtObjectsGUI');
		$shop_url = $shopLB->buildLink('ilShopGUI');

		$a_message = str_replace('[SHOP_BOUGHT_OBJECTS_URL]', $bought_objects_url, $a_message);
		$a_message = str_replace('[SHOP_URL]', $shop_url, $a_message);

		unset($user);

		return $a_message;
	}
}

?>
