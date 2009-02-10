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
include_once "./payment/classes/class.ilPaymentVendors.php";

class ilPaymentBaseGUI
{
	var $ilias;
	var $lng;
	var $db;
	var $tpl;
	var $rbacsystem;
	var $tabs_gui;

	var $user_obj;

	var $section;
	var $main_section;

	function ilPaymentBaseGUI()
	{

		global $ilias,$ilDB,$lng,$tpl,$rbacsystem,$ilTabs;

		$this->ilias =& $ilias;
		$this->db =& $ilDB;
		$this->lng =& $lng;
		$this->tpl =& $tpl;

		$this->tabs_gui =& $ilTabs;

		$this->SECTION_STATISTIC = 1;
		$this->SECTION_OBJECT = 2;
		$this->SECTION_TRUSTEE = 3;
		$this->SECTION_SHOPPING_CART = 4;
		$this->SECTION_BUYED_OBJECTS = 5;
		$this->SECTION_COUPONS = 6;

		$this->ADMIN = 4;
		$this->BASE = 5;
	}

	function setSection($a_section)
	{
		$this->section = $a_section;
	}
	function getSection()
	{
		return $this->section;
	}
	function setMainSection($a_main_section)
	{
		$this->main_section = $a_main_section;
	}
	function getMainSection()
	{
		return $this->main_section;
	}
	
	function buildHeader()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.payb_content.html");
		
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
		$this->lng->txt("personal_desktop"));
		$this->tpl->setTitle($this->lng->txt("personal_desktop"));

#		switch($this->getMainSection())
#		{
#			case $this->ADMIN:
#				$this->tpl->setVariable("HEADER",$this->lng->txt('paya_header'));
#				break;

#			case $this->BASE:
#				$this->tpl->setVariable("HEADER",$this->lng->txt('pay_header'));
#				break;
#		}

#		$this->__buildStylesheet();
#		$this->__buildStatusline();
		$this->__buildButtons();
	}

	
	
	function setTableGUIBasicData(&$tbl,&$result_set,$a_default_order_column = '')
	{
		$offset = $_GET["offset"];
		$order = $_GET["sort_by"];
		$direction = $_GET["sort_order"];

		$tbl->setOrderColumn($order,$a_default_order_column);
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setMaxCount(count($result_set));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($result_set);
	}

		

	// PIRVATE
	function  __buildStatusline()
	{
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$this->__buildLocator();
	}	
	function __buildLocator()
	{
		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
		$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));

		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("ITEM", $this->lng->txt("personal_desktop"));
		$this->tpl->setVariable("LINK_ITEM","../ilias.php?baseClass=ilPersonalDesktopGUI");
		#$this->tpl->setVariable("LINK_ITEM", "../usr_personaldesktop.php");
		$this->tpl->parseCurrentBlock();

		switch($this->getMainSection())
		{
			case $this->ADMIN:
				$this->tpl->setCurrentBlock("locator_item");
				$this->tpl->setVariable("PREFIX",'>&nbsp;');
				$this->tpl->setVariable("ITEM", $this->lng->txt("paya_locator"));
				$this->tpl->setVariable("LINK_ITEM", "./payment.php?view=payment_admin");
				$this->tpl->parseCurrentBlock();
				break;

			case $this->BASE:
				$this->tpl->setCurrentBlock("locator_item");
				$this->tpl->setVariable("PREFIX",'>&nbsp;');
				$this->tpl->setVariable("ITEM", $this->lng->txt("pay_locator"));
				$this->tpl->setVariable("LINK_ITEM", "./payment.php");
				$this->tpl->parseCurrentBlock();
				break;
		}

		// CHECK for new mail and info
		ilUtil::sendInfo();

		return true;
	}
	function __buildStylesheet()
	{
		$this->tpl->setVariable("LOCATION_STYLESHEET",ilUtil::getStyleSheetLocation());
	}

	function __buildButtons()
	{
		$this->tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");

		if($this->getMainSection() == $this->ADMIN)
		{
			if(ilPaymentVendors::_isVendor($this->user_obj->getId()) or 
			   ilPaymentTrustees::_hasStatisticPermission($this->user_obj->getId()))
			{
				//$this->tabs_gui->addSubTabTarget('paya_statistic',
				$this->tabs_gui->addSubTabTarget('bookings',
												 $this->ctrl->getLinkTargetByClass('ilpaymentstatisticgui'),
												 '',
												 '',
												 '',
												 $this->getSection() == $this->SECTION_STATISTIC ? true : false);
/*				$this->tpl->setCurrentBlock("tab");
				$this->tpl->setVariable('TAB_TYPE',$this->getSection() == $this->SECTION_STATISTIC ? 'tabactive' : 'tabinactive');
				$this->tpl->setVariable("TAB_LINK",$this->ctrl->getLinkTargetByClass('ilpaymentstatisticgui'));
				$this->tpl->setVariable("TAB_TEXT",$this->lng->txt('paya_statistic'));
				$this->tpl->parseCurrentBlock();*/
			}
			if(ilPaymentVendors::_isVendor($this->user_obj->getId()) or 
			   ilPaymentTrustees::_hasObjectPermission($this->user_obj->getId()))
			{
				$this->tabs_gui->addSubTabTarget('paya_object',
												 $this->ctrl->getLinkTargetByClass('ilpaymentobjectgui'),
												 '',
												 '',
												 '',
												 $this->getSection() == $this->SECTION_OBJECT ? true : false);
/*				$this->tpl->setCurrentBlock("tab");
				$this->tpl->setVariable('TAB_TYPE',$this->getSection() == $this->SECTION_OBJECT ? 'tabactive' : 'tabinactive');
				$this->tpl->setVariable("TAB_LINK",$this->ctrl->getLinkTargetByClass('ilpaymentobjectgui'));
				$this->tpl->setVariable("TAB_TEXT",$this->lng->txt('paya_object'));
				$this->tpl->parseCurrentBlock();*/
			}
			if(ilPaymentVendors::_isVendor($this->user_obj->getId()))
			{
				$this->tabs_gui->addSubTabTarget('paya_trustees',
												 $this->ctrl->getLinkTargetByClass('ilpaymenttrusteegui'),
												 '',
												 '',
												 '',
												 $this->getSection() == $this->SECTION_TRUSTEE ? true : false);
/*				$this->tpl->setCurrentBlock("tab");
				$this->tpl->setVariable('TAB_TYPE',$this->getSection() == $this->SECTION_TRUSTEE ? 'tabactive' : 'tabinactive');
				$this->tpl->setVariable("TAB_LINK",$this->ctrl->getLinkTargetByClass('ilpaymenttrusteegui'));
				$this->tpl->setVariable("TAB_TEXT",$this->lng->txt('paya_trustees'));
				$this->tpl->parseCurrentBlock();*/
			}			
			if(ilPaymentVendors::_isVendor($this->user_obj->getId()) or 
			   ilPaymentTrustees::_hasCouponsPermission($this->user_obj->getId()))
			{
				$this->tabs_gui->addSubTabTarget('paya_coupons_coupons',
												 $this->ctrl->getLinkTargetByClass('ilpaymentcoupongui'),
												 '',
												 '',
												 '',
												 $this->getSection() == $this->SECTION_COUPONS ? true : false);
/*				$this->tpl->setCurrentBlock("tab");
				$this->tpl->setVariable('TAB_TYPE',$this->getSection() == $this->SECTION_TRUSTEE ? 'tabactive' : 'tabinactive');
				$this->tpl->setVariable("TAB_LINK",$this->ctrl->getLinkTargetByClass('ilpaymenttrusteegui'));
				$this->tpl->setVariable("TAB_TEXT",$this->lng->txt('paya_trustees'));
				$this->tpl->parseCurrentBlock();*/
			}
		}
		if($this->getMainSection() == $this->BASE)
		{
			$this->tabs_gui->addSubTabTarget('paya_shopping_cart',
											 $this->ctrl->getLinkTargetByClass('ilpaymentshoppingcartgui'),
											 '',
											 '',
											 '',
											 $this->getSection() == $this->SECTION_SHOPPING_CART ? true : false);
/*			$this->tpl->setCurrentBlock("tab");
			$this->tpl->setVariable('TAB_TYPE',$this->getSection() == $this->SECTION_SHOPPING_CART ? 'tabactive' : 'tabinactive');
			$this->tpl->setVariable("TAB_LINK",$this->ctrl->getLinkTargetByClass('ilpaymentshoppingcartgui'));
			$this->tpl->setVariable("TAB_TEXT",$this->lng->txt('paya_shopping_cart'));
			$this->tpl->parseCurrentBlock();*/

			$this->tabs_gui->addSubTabTarget('paya_buyed_objects',
											 $this->ctrl->getLinkTargetByClass('ilpaymentbuyedobjectsgui'),
											 '',
											 '',
											 '',
											 $this->getSection() == $this->SECTION_BUYED_OBJECTS ? true : false);
/*			$this->tpl->setCurrentBlock("tab");
			$this->tpl->setVariable('TAB_TYPE',$this->getSection() == $this->SECTION_BUYED_OBJECTS ? 'tabactive' : 'tabinactive');
			$this->tpl->setVariable("TAB_LINK",$this->ctrl->getLinkTargetByClass('ilpaymentbuyedobjectsgui'));
			$this->tpl->setVariable("TAB_TEXT",$this->lng->txt('paya_buyed_objects'));
			$this->tpl->parseCurrentBlock();*/
		}
	}

}
?>