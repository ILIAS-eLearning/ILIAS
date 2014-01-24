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

class ilInvoiceNumberPlaceholdersPropertyGUI extends ilFormPropertyGUI
{
	
	public function __construct()
	{
		parent::__construct('');
	}
	
	public function insert($a_tpl)
	{
		global $lng;

		$subtpl = new ilTemplate("tpl.invoice_number_placeholders.html", false, false, "Services/Payment");

		$subtpl->setVariable('TXT_USE_PLACEHOLDERS', $lng->txt('placeholders'));
		$subtpl->setVariable('TXT_PLACEHOLDERS_ADVICE', $lng->txt('inv_number_placeholder_advice'));

		$subtpl->setVariable('TXT_CURRENT_TIMESTAMP', $lng->txt('current_timestamp'));
		$subtpl->setVariable('TXT_INSTALLATION_ID', $lng->txt('inst_id'));
		$subtpl->setVariable('TXT_USER_ID', $lng->txt('user_id'));
		$subtpl->setVariable('TXT_DAY', $lng->txt('day'));
		$subtpl->setVariable('TXT_MONTH', $lng->txt('month'));
		$subtpl->setVariable('TXT_YEAR', $lng->txt('year'));
		$subtpl->setVariable('TXT_INCREMENTAL_NUMBER', $lng->txt('incremental_number'));

		$a_tpl->setCurrentBlock("prop_generic");
		$a_tpl->setVariable("PROP_GENERIC", $subtpl->get());
		$a_tpl->parseCurrentBlock();	
	}

	/*
	 *  user_id only needed if admin adds selling process for users in administration manually
	 * @param integer a_user_id
	 */

	public static function _generateInvoiceNumber($a_user_id = 0)
	{
		global $ilSetting;
		
		if($a_user_id == 0)
		{
			global $ilUser;
			$a_user_id = $ilUser->getId();
		}

		$inst_id = $ilSetting->get('inst_id');
		$cur_time = time();

		include_once './Services/Payment/classes/class.ilUserDefinedInvoiceNumber.php';
		$invObj = new ilUserDefinedInvoiceNumber();

		if($invObj->getUDInvoiceNumberActive() == 1)
		{
			$next_number = ilUserDefinedInvoiceNumber::_nextIncCurrentValue();
			
			$invoice_number = $invObj->getInvoiceNumberText();

			$invoice_number = str_replace('[CURRENT_TIMESTAMP]', $cur_time, $invoice_number);
			$invoice_number = str_replace('[INSTALLATION_ID]', $inst_id, $invoice_number);
			$invoice_number = str_replace('[USER_ID]', $a_user_id, $invoice_number);
			$invoice_number = str_replace('[DAY]', date('d', $cur_time), $invoice_number);
			$invoice_number = str_replace('[MONTH]', date('m', $cur_time), $invoice_number);
			$invoice_number = str_replace('[YEAR]', date('Y', $cur_time), $invoice_number);
			$invoice_number = str_replace('[INCREMENTAL_NUMBER]', $next_number, $invoice_number);
		}
		else
		{
			$inst_id_time = $inst_id.'_'.$a_user_id.'_'.substr((string) $cur_time,-3);
			$invoice_number = $inst_id_time.substr(md5(uniqid(rand(), true)), 0, 4);
		}
		
		return $invoice_number;
	}
}

?>
