<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilShopBaseGUI
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
* 
* @ingroup ServicesPayment
*/
class ilShopBaseGUI
{
	protected $ctrl = null;
	protected $lng = null;
	protected $tpl = null;
	protected $oGeneralSettings = null;	
	protected $section = 0;
	protected $sub_section = 0;
	
	public function __construct()
	{
		global $ilCtrl, $lng, $tpl, $ilMainMenu;

		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->lng = $lng;
		
		$this->lng->loadLanguageModule('search');
		$this->lng->loadLanguageModule('payment');		
		$this->oGeneralSettings = ilPaymentSettings::_getInstance();
		$ilMainMenu->setActive('shop');
	}
	
	protected function prepareOutput()
	{		
		$this->tpl->getStandardTemplate();

		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pays_cart_b.png"), $this->lng->txt("search"));
		$this->tpl->setTitle($this->lng->txt("shop"));

		ilUtil::infoPanel();
	
		$this->buildSubTabs();
	}
	
	protected function addPager($result, $a_session_key)
	{
	 	if(count($result->getResults()) < $result->getMaxHits())
	 	{
	 		return true;
	 	}

		if($result->getResultPageNumber() > 1)
		{
			$this->ctrl->setParameter($this,'page_number', $result->getResultPageNumber() - 1);
			$this->tpl->setCurrentBlock('prev');
			$this->tpl->setVariable('PREV_LINK',$this->ctrl->getLinkTarget($this, 'performSearch'));
			$this->tpl->setVariable('TXT_PREV',$this->lng->txt('search_page_prev'));
			$this->tpl->parseCurrentBlock();
		}
		for($i = 0; $i < ceil(count($result->getResults()) /  $result->getMaxHits()); $i++)
		{
			if($i + 1 == $result->getResultPageNumber())
			{
				$this->tpl->setCurrentBlock('pages_link');
				$this->tpl->setVariable('NUMBER', $i + 1);
				$this->tpl->parseCurrentBlock();
				continue;
			}
			
			$this->ctrl->setParameter($this,'page_number', $i + 1);
			$link = '<a href="'.$this->ctrl->getLinkTarget($this, 'performSearch').'">'.($i + 1).'</a> ';
			$this->tpl->setCurrentBlock('pages_link');
			$this->tpl->setVariable('NUMBER',$link);
			$this->tpl->parseCurrentBlock();
		}		

		if($result->getResultPageNumber() < ceil(count($result->getResults()) /  $result->getMaxHits()))
		{
			$this->tpl->setCurrentBlock('next');
			$this->ctrl->setParameter($this,'page_number', $result->getResultPageNumber() + 1);
			$this->tpl->setVariable('NEXT_LINK',$this->ctrl->getLinkTarget($this, 'performSearch'));
		 	$this->tpl->setVariable('TXT_NEXT',$this->lng->txt('search_page_next'));
		 	$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock('prev_next');
	 	$this->tpl->setVariable('SEARCH_PAGE',$this->lng->txt('search_page'));
	 	$this->tpl->parseCurrentBlock();
	 	
	 	$this->ctrl->clearParameters($this);
	}
	
	protected function buildSubTabs()
	{
		global $ilUser, $ilTabs;		
		
		switch($this->getSection())
		{
			case 6:
				if(ilPaymentVendors::_isVendor($ilUser->getId()) || 
				   ilPaymentTrustees::_hasStatisticPermission($ilUser->getId()))
				{
					$ilTabs->addSubTabTarget('bookings', $this->ctrl->getLinkTargetByClass('ilpaymentstatisticgui'), '', '', '');
				}		
				if(ilPaymentVendors::_isVendor($ilUser->getId()) || 
				   ilPaymentTrustees::_hasObjectPermission($ilUser->getId()))
				{
					$ilTabs->addSubTabTarget('paya_object', $this->ctrl->getLinkTargetByClass('ilpaymentobjectgui'), '', '', '');
		
				}
				if(ilPaymentVendors::_isVendor($ilUser->getId()))
				{
					$ilTabs->addSubTabTarget('paya_trustees', $this->ctrl->getLinkTargetByClass('ilpaymenttrusteegui'), '', '', '');			
				}	
				if(!(bool) $this->oGeneralSettings->get('hide_coupons'))
				{		
					if(ilPaymentVendors::_isVendor($ilUser->getId()) || 
					   ilPaymentTrustees::_hasCouponsPermission($ilUser->getId()))
					{
						$ilTabs->addSubTabTarget('paya_coupons_coupons', $this->ctrl->getLinkTargetByClass('ilpaymentcoupongui'), '', '', '');			
					}
				}
				break;
				
			default:
				break;
		}
	}
	
	protected function setSection($a_section)
	{
		$this->section = $a_section;
	}
	protected function getSection()
	{
		return $this->section;
	}
	protected function setSubSection($a_sub_section)
	{
		$this->sub_section = $a_sub_section;
	}
	protected function getSubSection()
	{
		return $this->sub_section;
	}
	
	protected function showButton($a_cmd, $a_text, $a_target = '')
	{
		global $ilToolbar;

		$ilToolbar->addButton($a_text, $this->ctrl->getLinkTarget($this, $a_cmd), $a_target);
	}
	
	protected function initTableGUI()
	{
		include_once './Services/Table/classes/class.ilTableGUI.php';

		return new ilTableGUI(0, false);
	}
	
	protected function setTableGUIBasicData($tbl, $result_set, $a_default_order_column = '')
	{
		$offset = (int)$_GET['offset'];
		$order = $_GET['sort_by'];
		$direction = $_GET['sort_order'];

		$tbl->setOrderColumn($order,$a_default_order_column);
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit((int)$_GET['limit']);
		$tbl->setMaxCount(count($result_set));
		$tbl->setFooter('tblfooter', $this->lng->txt('previous'), $this->lng->txt('next'));
		$tbl->setData($result_set);
	}
}
?>
