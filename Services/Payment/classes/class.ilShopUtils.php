<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilShopUtils
* 
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
* 
* @ingroup ServicesPayment
*  
*/
 class ilShopUtils
 {
 	/**
	* Formats a vat rate for gui output.
	* 
	* @access	public
	* @static
	* @param	float $a_vat
	* @return	string
	*/
 	public static function _formatVAT($a_vat)
 	{		
 		return ((float)$a_vat != floor((float)$a_vat) ?
				self::_formatFloat((float)$a_vat) :
				(int)$a_vat).' %';
 	}
 	
 	/**
	* Formats a float value for gui output
	* 
	* @access	public
	* @static
	* @param	float $a_vat
	* @return	string
	*/
 	public static function _formatFloat($a_float, $a_num_decimals = 2)
 	{
 		global $lng;

		return number_format((float)$a_float, $a_num_decimals, $lng->txt('lang_sep_decimal'), $lng->txt('lang_sep_thousand'));
 	} 
 	
 	/**
	* Checks if the passed vat rate is valid.
	* 
	* @access	public
	* @static
	* @param	string $a_vat
	* @return	bool
	*/
 	public static function _checkVATRate($a_vat_rate)
 	{
 		$reg = '/^([0]|([1-9][0-9]*))([\.,][0-9][0-9]*)?$/';
		return preg_match($reg, $a_vat_rate);
 	}
 	
 	/**
	* Sends a notification message to all users responsible for vat assignment.
	* 
	* @access	public
	* @static
	* @param	ilPaymentObject $oPaymentObject
	*/
 	public static function _sendNotificationToVATAdministration($oPaymentObject)
 	{
 		global $ilSetting, $lng, $ilClientIniFile;
 		
 		$payment_vat_admins = $ilSetting->get('payment_vat_admins');
 		$users = explode(',', $payment_vat_admins);
 		
 		$subject = $lng->txt('payment_vat_assignment_notification_subject');
 		$tmp_obj = ilObjectFactory::getInstanceByRefId($oPaymentObject->getRefId());
 		$message = sprintf($lng->txt('payment_vat_assignment_notification_body'), $tmp_obj->getTitle())."\n\n";
 		$message .= "------------------------------------------------------------\n";
 		$message .= sprintf($lng->txt('payment_vat_assignment_notification_intro'),
				   $ilClientIniFile->readVariable('client', 'name'),
				   ILIAS_HTTP_PATH.'/?client_id='.CLIENT_ID);	
 		
 		include_once 'Services/Mail/classes/class.ilMail.php';
 		$mail_obj = new ilMail(ANONYMOUS_USER_ID);
 		foreach((array)$users as $login)
 		{
 			if(strlen(trim($login)) && 
 			   (int)ilObjUser::_lookupId(trim($login)))
 			{
 				$success = $mail_obj->sendMail(trim($login), '', '',
								$subject, $message,
								array(),array("system"));
 			} 			
 		}
 	}
 }
?>