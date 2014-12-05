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

	public static function _createRandomUserAccount($keyarray)
	{
		global $ilDB, $ilUser, $ilSetting, $rbacadmin;

		if($_SESSION['create_user_account'] != NULL)
		{
		
		    $obj_user = new ilObjUser($_SESSION['create_user_account']);
		    return $obj_user;
		}
		else
		{
			$userLogin = array();
			$res = $ilDB->query('SELECT sequence FROM object_data_seq');
			$row = $ilDB->fetchAssoc($res);

			$temp_user_id = (int)$row['sequence'] + 1;

			$userLogin['login'] = 'shop_user_'.$temp_user_id;

			$userLogin['passwd'] = ilUtil::generatePasswords(1);

			require_once 'Services/User/classes/class.ilObjUser.php';
			include_once("Services/Mail/classes/class.ilAccountMail.php");

			$obj_user = new ilObjUser();
			$obj_user->setId($temp_user_id);

			$obj_user->setLogin($userLogin['login']);
			$obj_user->setPasswd((string)$userLogin['passwd'][0], IL_PASSWD_PLAIN);

			$_SESSION['tmp_user_account']['login'] = $userLogin['login'];
			$_SESSION['tmp_user_account']['passwd'] = $userLogin['passwd'];

			$obj_user->setFirstname($keyarray['first_name']);
			$obj_user->setLastname($keyarray['last_name']);
			$obj_user->setEmail($keyarray['payer_email']);
		#	$obj_user->setEmail('nkrzywon@databay.de');

			$obj_user->setGender('f');
			$obj_user->setLanguage( $ilSetting->get("language"));
			$obj_user->setActive(true);
			$obj_user->setTimeLimitUnlimited(true);

			$obj_user->setTitle($obj_user->getFullname());
			$obj_user->setDescription($obj_user->getEmail());
			$obj_user->setTimeLimitOwner(7);
			$obj_user->setTimeLimitUnlimited(1);
			$obj_user->setTimeLimitMessage(0);
			$obj_user->setApproveDate(date("Y-m-d H:i:s"));

			// Set default prefs
			$obj_user->setPref('hits_per_page',$ilSetting->get('hits_per_page',30));
			$obj_user->setPref('show_users_online',$ilSetting->get('show_users_online','y'));
			$obj_user->writePrefs();

			// at the first login the user must complete profile
			$obj_user->setProfileIncomplete(true);
			$obj_user->create();
			$obj_user->saveAsNew();

			$user_role = ilObject::_exists(4, false);

			if(!$user_role)
			{
				include_once("./Services/AccessControl/classes/class.ilObjRole.php");
				$reg_allowed = ilObjRole::_lookupRegisterAllowed();
				$user_role = $reg_allowed[0]['id'];

			}
			else $user_role = 4;

			$rbacadmin->assignUser((int)$user_role, $obj_user->getId(), true);

			include_once "Services/Mail/classes/class.ilMimeMail.php";
			global $ilias, $lng;

			$settings = $ilias->getAllSettings();
						$mmail = new ilMimeMail();
						$mmail->autoCheck(false);
						$mmail->From($settings["admin_email"]);
						$mmail->To($obj_user->getEmail());

			// mail subject
			$subject = $lng->txt("reg_mail_subject");

			// mail body
			$body = $lng->txt("reg_mail_body_salutation")." ".$obj_user->getFullname().",\n\n".
				$lng->txt("reg_mail_body_text1")."\n\n".
				$lng->txt("reg_mail_body_text2")."\n".
				ILIAS_HTTP_PATH."/login.php?client_id=".$ilias->client_id."\n";
			$body .= $lng->txt("login").": ".$obj_user->getLogin()."\n";


			$body.= $lng->txt("passwd").": ".$userLogin['passwd'][0]."\n";

			$body.= "\n";

			$body .= ($lng->txt("reg_mail_body_text3")."\n\r");
			$body .= $obj_user->getProfileAsString($lng);
			$mmail->Subject($subject);
			$mmail->Body($body);
			$mmail->Send();

			$_SESSION['create_user_account'] = $obj_user->getId();
			return $obj_user;
		}

	}
	public static function _assignTransactionToCustomerId($a_old_user_id, $a_new_user_id, $a_transaction_extern)
	{
		global $ilDB;

		$res = $ilDB->manipulateF('
			UPDATE payment_statistic
			SET	customer_id = %s
			WHERE customer_id = %s
			AND transaction_extern = %s',
			array('integer', 'integer', 'text'),
			array($a_new_user_id, $a_old_user_id, $a_transaction_extern));
	}

	public static function _addPurchasedObjToDesktop($oPaymentObject, $a_user_id = 0)
	{
		global $ilUser;

		$type = ilObject::_lookupType($oPaymentObject->getRefId(),true);

		if($a_user_id > 0)
		{
			// administrator added a selling process to statistics
			$tmp_usr = new ilObjUser($a_user_id);
			$tmp_usr->addDesktopItem($oPaymentObject->getRefId(),$type);
		}
		else
		{
			// user purchased object
			$ilUser->addDesktopItem($oPaymentObject->getRefId(),$type);
		}
	}

	public static function _assignPurchasedCourseMemberRole($oPaymentObject, $a_user_id = 0)
	{
		global $ilUser;
		include_once './Modules/Course/classes/class.ilCourseParticipants.php';
		$obj_id = ilObject::_lookupObjectId($oPaymentObject->getRefId());

		$participants = ilCourseParticipants::_getInstanceByObjId($obj_id);

		if($a_user_id > 0)
		{
			// administrator added a selling process to statistics
			$res = $participants->add($a_user_id, IL_CRS_MEMBER);
		}
		else
		{
			$res = $participants->add($ilUser->getId(),IL_CRS_MEMBER);
		}
	}
	
	public static function _addToShoppingCartSymbol($a_ref_id)
	{
		global $ilCtrl;

		$detail_link = $ilCtrl->getLinkTargetByClass("ilShopPurchaseGUI", "showDetails").'&ref_id='.$a_ref_id;
		$img = ilUtil::img('./templates/default/images/payment/shopcart_add_32.png');
		
		$link = '<a href="'.$detail_link.'">'.$img.'</a>';

		return $link;
	}
	 
	public static function _getSpecialObjectSymbol()
	{
		return $img = ilUtil::img('./templates/default/images/icon_rate_10.svg','', '24px', '24px');
	}

	public static function _getPaymethodSymbol($a_paymethod)
	{
		switch($a_paymethod)
		{
			case '1':
			case 'pm_bill':
			case 'bill':
			case 'PAY_METHOD_BILL': return '';
				break;

			case '2':
			case 'pm_bmf':
			case 'bmf':
			case 'PAY_METHOD_BMF': return '';
				break;

			case '3':
			case 'pm_paypal':
			case 'paypal':
			case 'PAY_METHOD_PAYPAL': return ilUtil::img('./templates/default/images/payment/paypal.svg');
				break;

			case '4':
			case 'pm_epay':
			case 'epay':
			case 'PAY_METHOD_EPAY': return '';
				break;
			case 'PAY_METHOD_NOT_SPECIFIED': return '';
				break;
			default:

				break;
		}
	}
	public static function _deassignPurchasedCourseMemberRole($a_ref_id, $a_user_id)
	{
		include_once './Modules/Course/classes/class.ilCourseParticipants.php';
		$obj_id = ilObject::_lookupObjectId($a_ref_id);

		$participants = ilCourseParticipants::_getInstanceByObjId($obj_id);
		$res = $participants->delete($a_user_id, IL_CRS_MEMBER);
	}	
}