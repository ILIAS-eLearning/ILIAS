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
* Class ilPaymentBillAdminGUI
*
* @author Stefan Meyer
* @version $Id$
*
* @package core
*/
include_once './payment/classes/class.ilPaymentObject.php';

class ilPaymentBillAdminGUI extends ilPaymentBaseGUI
{
	var $ctrl;

	var $lng;
	var $user_obj = null;
	var $pobject = null;
	var $pobject_id = null;

	function ilPaymentBillAdminGUI(&$user_obj,$a_pobject_id)
	{
		global $ilCtrl;

		$this->ctrl =& $ilCtrl;
		$this->ctrl->setParameter($this,'pobject_id',$a_pobject_id);

		$this->ilPaymentBaseGUI();

		$this->pobject =& new ilPaymentObject($user_obj,$a_pobject_id);
		$this->user_obj =& $user_obj;
		$this->pobject_id = $a_pobject_id;
	}
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $tree;

		$cmd = $this->ctrl->getCmd();
		switch ($this->ctrl->getNextClass($this))
		{

			default:
				if(!$cmd = $this->ctrl->getCmd())
				{
					$cmd = 'showBillData';
				}
				$this->$cmd();
				break;
		}
	}

	function showBillData()
	{
		$this->__initBillVendorData();

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.paya_bill_admin.html',true);
		$this->__showButtons();

		$this->tpl->setVariable("BILL_FORMACTION",$this->ctrl->getFormAction($this));

		// set table header
		$tmp_obj =& ilObjectFactory::getInstanceByRefId($this->pobject->getRefId());
		
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_'.$tmp_obj->getType().'.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_'.$tmp_obj->getType()));
		$this->tpl->setVariable("TITLE",$tmp_obj->getTitle());
		$this->tpl->setVariable("DESCRIPTION",$this->lng->txt('paya_bill_data'));

		// set plain text variables
		$this->tpl->setVariable("TXT_CONTACT",$this->lng->txt('contact_data'));
		$this->tpl->setVariable("TXT_GENDER",$this->lng->txt('gender'));
		$this->tpl->setVariable("TXT_GENDER_F",$this->lng->txt("gender_f"));
		$this->tpl->setVariable("TXT_GENDER_M",$this->lng->txt("gender_m"));
		$this->tpl->setVariable("TXT_FIRSTNAME",$this->lng->txt('firstname'));
		$this->tpl->setVariable("TXT_LASTNAME",$this->lng->txt('lastname'));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt('person_title'));
		$this->tpl->setVariable("TXT_INSTITUTION",$this->lng->txt('institution'));
		$this->tpl->setVariable("TXT_DEPARTMENT",$this->lng->txt('department'));
		$this->tpl->setVariable("TXT_STREET",$this->lng->txt('street'));
		$this->tpl->setVariable("TXT_ZIPCODE",$this->lng->txt('zipcode'));
		$this->tpl->setVariable("TXT_CITY",$this->lng->txt('city'));
		$this->tpl->setVariable("TXT_COUNTRY",$this->lng->txt('country'));
		$this->tpl->setVariable("TXT_PHONE",$this->lng->txt('phone'));
		$this->tpl->setVariable("TXT_FAX",$this->lng->txt('fax'));
		$this->tpl->setVariable("TXT_EMAIL",$this->lng->txt('email'));
		$this->tpl->setVariable("TXT_ACCOUNT_DATA",$this->lng->txt('account_data'));
		$this->tpl->setVariable("TXT_ACCOUNT_NUMBER",$this->lng->txt('account_number'));
		$this->tpl->setVariable("TXT_BANKCODE",$this->lng->txt('bankcode'));
		$this->tpl->setVariable("TXT_IBAN",$this->lng->txt('iban'));
		$this->tpl->setVariable("TXT_BIC",$this->lng->txt('bic'));
		$this->tpl->setVariable("TXT_BANKNAME",$this->lng->txt('bankname'));

		
		$this->tpl->setVariable("INPUT_VALUE",$this->lng->txt('save'));

		// set radios

		$gender = isset($_POST['gender']) ? $_POST['gender'] : $this->bvd_obj->getGender();
 
		$this->tpl->setVariable("GENDER_CHECK_M",ilUtil::formRadioButton($gender == 1 ? 1 : 0,'gender',1));
		$this->tpl->setVariable("GENDER_CHECK_F",ilUtil::formRadioButton($gender == 2 ? 1 : 0,'gender',2));

		// fill defaults

		$this->tpl->setVariable("FIRSTNAME",
								isset($_POST['firstname']) 
								? ilUtil::prepareFormOutput($_POST['firstname'],true) 
								: ilUtil::prepareFormOutput($this->bvd_obj->getFirstname()));
		$this->tpl->setVariable("LASTNAME",
								isset($_POST['lastname']) 
								? ilUtil::prepareFormOutput($_POST['lastname'],true) 
								: ilUtil::prepareFormOutput($this->bvd_obj->getLastname()));
		$this->tpl->setVariable("INSTITUTION",
								isset($_POST['institution']) 
								? ilUtil::prepareFormOutput($_POST['institution'],true) 
								: ilUtil::prepareFormOutput($this->bvd_obj->getInstitution()));
		$this->tpl->setVariable("DEPARTMENT",
								isset($_POST['department']) 
								? ilUtil::prepareFormOutput($_POST['department'],true) 
								: ilUtil::prepareFormOutput($this->bvd_obj->getDepartment()));
		$this->tpl->setVariable("STREET",
								isset($_POST['street']) 
								? ilUtil::prepareFormOutput($_POST['street'],true) 
								: ilUtil::prepareFormOutput($this->bvd_obj->getStreet()));
		$this->tpl->setVariable("ZIPCODE",
								isset($_POST['zipcode']) 
								? ilUtil::prepareFormOutput($_POST['zipcode'],true) 
								: ilUtil::prepareFormOutput($this->bvd_obj->getZipcode()));
		$this->tpl->setVariable("CITY",
								isset($_POST['city']) 
								? ilUtil::prepareFormOutput($_POST['city'],true) 
								: ilUtil::prepareFormOutput($this->bvd_obj->getCity()));
		$this->tpl->setVariable("COUNTRY",
								isset($_POST['country']) 
								? ilUtil::prepareFormOutput($_POST['country'],true) 
								: ilUtil::prepareFormOutput($this->bvd_obj->getCountry()));
		$this->tpl->setVariable("PHONE",
								isset($_POST['phone']) 
								? ilUtil::prepareFormOutput($_POST['phone'],true) 
								: ilUtil::prepareFormOutput($this->bvd_obj->getPhone()));
		$this->tpl->setVariable("FAX",
								isset($_POST['fax']) 
								? ilUtil::prepareFormOutput($_POST['fax'],true) 
								: ilUtil::prepareFormOutput($this->bvd_obj->getFax()));
		$this->tpl->setVariable("EMAIL",
								isset($_POST['email']) 
								? ilUtil::prepareFormOutput($_POST['email'],true) 
								: ilUtil::prepareFormOutput($this->bvd_obj->getEmail()));
		$this->tpl->setVariable("ACCOUNT_NUMBER",
								isset($_POST['account_number']) 
								? ilUtil::prepareFormOutput($_POST['account_number'],true) 
								: ilUtil::prepareFormOutput($this->bvd_obj->getAccountNumber()));
		$this->tpl->setVariable("BANKCODE",
								isset($_POST['bankcode']) 
								? ilUtil::prepareFormOutput($_POST['bankcode'],true) 
								: ilUtil::prepareFormOutput($this->bvd_obj->getBankcode()));
		$this->tpl->setVariable("IBAN",
								isset($_POST['iban']) 
								? ilUtil::prepareFormOutput($_POST['iban'],true) 
								: ilUtil::prepareFormOutput($this->bvd_obj->getIban()));
		$this->tpl->setVariable("BIC",
								isset($_POST['bic']) 
								? ilUtil::prepareFormOutput($_POST['bic'],true) 
								: ilUtil::prepareFormOutput($this->bvd_obj->getBic()));
		$this->tpl->setVariable("BANKNAME",
								isset($_POST['bankname']) 
								? ilUtil::prepareFormOutput($_POST['bankname'],true) 
								: ilUtil::prepareFormOutput($this->bvd_obj->getBankname()));
	}

	function update()
	{
		include_once './payment/classes/class.ilPaymentBillVendor.php';

		$this->bvd_obj =& new ilPaymentBillVendor($this->pobject_id);

		$this->bvd_obj->setGender($_POST['gender']);
		$this->bvd_obj->setFirstname(ilUtil::stripSlashes($_POST['firstname']));
 		$this->bvd_obj->setLastname(ilUtil::stripSlashes($_POST['lastname']));
		$this->bvd_obj->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->bvd_obj->setInstitution(ilUtil::stripSlashes($_POST['institution']));
		$this->bvd_obj->setDepartment(ilUtil::stripSlashes($_POST['department']));
		$this->bvd_obj->setStreet(ilUtil::stripSlashes($_POST['street']));
		$this->bvd_obj->setZipcode(ilUtil::stripSlashes($_POST['zipcode']));
		$this->bvd_obj->setCity(ilUtil::stripSlashes($_POST['city']));
		$this->bvd_obj->setCountry(ilUtil::stripSlashes($_POST['country']));
		$this->bvd_obj->setPhone(ilUtil::stripSlashes($_POST['phone']));
		$this->bvd_obj->setFax(ilUtil::stripSlashes($_POST['fax']));
		$this->bvd_obj->setEmail(ilUtil::stripSlashes($_POST['email']));
		$this->bvd_obj->setAccountNumber(ilUtil::stripSlashes($_POST['account_number']));
		$this->bvd_obj->setBankcode(ilUtil::stripSlashes($_POST['bankcode']));
		$this->bvd_obj->setIban(ilUtil::stripSlashes($_POST['iban']));
		$this->bvd_obj->setBic(ilUtil::stripSlashes($_POST['bic']));
		$this->bvd_obj->setBankname(ilUtil::stripSlashes($_POST['bankname']));

		if($this->bvd_obj->validate())
		{
			$this->bvd_obj->update();
			ilUtil::sendInfo($this->lng->txt('paya_bill_data_updated'));
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('paya_pay_method_fill_out_required').$this->bvd_obj->getMessage());
		}
		$this->showBillData();

		return true;
	}


	// PRIVATE
	function __showButtons()
	{
		// DETAILS LINK
		$this->ctrl->setParameterByClass('ilpaymentobjectgui','pobject_id',$this->pobject_id);

		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTargetByClass('ilpaymentobjectgui','editDetails'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt('paya_edit_details'));
		$this->tpl->parseCurrentBlock();

		// PRICES LINK
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTargetByClass('ilpaymentobjectgui','editPrices'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt('paya_edit_prices'));
		$this->tpl->parseCurrentBlock();

		$this->showButton('showBillData',$this->lng->txt('paya_edit_pay_method'));
	}

	function __initBillVendorData()
	{
		include_once './payment/classes/class.ilPaymentBillVendor.php';

		$this->bvd_obj =& new ilPaymentBillVendor($this->pobject_id);

		if(!$this->bvd_obj->hasData())
		{
			ilUtil::sendInfo($this->lng->txt('paya_read_from_personal_profile'));

			$tmp_user =& ilObjectFactory::getInstanceByObjId($this->pobject->getVendorId());

			switch($tmp_user->getGender())
			{
				case 'm':
					$this->bvd_obj->setGender(1);
					break;
				case 'f':
					$this->bvd_obj->setGender(2);
					break;
			}
			$this->bvd_obj->setFirstname($tmp_user->getFirstname());
			$this->bvd_obj->setLastname($tmp_user->getLastname());
			$this->bvd_obj->setTitle($tmp_user->getTitle());
			$this->bvd_obj->setInstitution($tmp_user->getInstitution());
			$this->bvd_obj->setDepartment($tmp_user->getDepartment());
			$this->bvd_obj->setStreet($tmp_user->getStreet());
			$this->bvd_obj->setZipcode($tmp_user->getZipcode());
			$this->bvd_obj->setCity($tmp_user->getCity());
			$this->bvd_obj->setCountry($tmp_user->getCountry());
			$this->bvd_obj->setPhone($tmp_user->getPhoneOffice());
			$this->bvd_obj->setFax($tmp_user->getFax());
			$this->bvd_obj->setEmail($tmp_user->getEmail());
		}
	}
}
?>