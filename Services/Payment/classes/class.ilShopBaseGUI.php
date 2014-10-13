<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilShopBaseGUI
*
* @author Nadia Ahmad <nahmad@databay.de>
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
	protected $settings = null;

	public function __construct()
	{
		global $ilCtrl, $lng, $tpl, $ilMainMenu;

		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->lng = $lng;
		
		$this->settings = ilPaymentSettings::_getInstance();
		
		$this->lng->loadLanguageModule('search');
		$this->lng->loadLanguageModule('payment');		
		$ilMainMenu->setActive('shop');
	}
	
	protected function prepareOutput()
	{		
		$this->tpl->getStandardTemplate();
		$this->tpl->setTitleIcon(ilObject::_getIcon('', '', 'pays') , $this->lng->txt("shop"));
		$this->tpl->setTitle($this->lng->txt("shop"));

		ilUtil::infoPanel();
	}

	/**
	 * @param $result
	 */
	protected function addPager($result)
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
}
