<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilLTIConsumerProviderSelectionFormGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumerProviderSelectionFormTableGUI extends ilPropertyFormGUI
{
	/**
	 * @var ilLTIConsumerProviderTableGUI
	 */
	protected $table;
	
	/**
	 * ilLTIConsumerProviderSelectionFormGUI constructor.
	 * @param $newType
	 * @param $parentGui
	 * @param $parentCmd
	 * @param $applyFilterCmd
	 * @param $resetFilterCmd
	 */
	public function __construct($newType, $parentGui, $parentCmd, $applyFilterCmd, $resetFilterCmd)
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$this->table = new ilLTIConsumerProviderTableGUI($parentGui, $parentCmd);
		
		$this->table->setFilterCommand($applyFilterCmd);
		$this->table->setResetCommand($resetFilterCmd);
		
		$this->table->setSelectProviderCmd('save');
		$this->table->setOwnProviderColumnEnabled(true);
		
		$this->table->setDefaultFilterVisiblity(true);
		$this->table->setDisableFilterHiding(true);
		
		$this->table->init();
		
		$this->setTitle($DIC->language()->txt($newType.'_select_provider'));
	}
	
	public function setTitle($title)
	{
		$this->table->setTitle($title);
	}

	public function getTitle()
	{
		return $this->table->getTitle();
	}
	
	public function getHTML()
	{
		return $this->table->getHTML();
	}
	
	public function applyFilter()
	{
		$this->table->writeFilterToSession();
		$this->table->resetOffset();
	}
	
	public function resetFilter()
	{
		$this->table->resetFilter();
		$this->table->resetOffset();
	}
	
	public function getFilter($field)
	{
		$field = $this->table->getFilterItemByPostVar($field);
		
		if( $field instanceof ilCheckboxInputGUI )
		{
			return (bool)$field->getChecked();
		}
		
		return $field->getValue();
	}
	
	public function setData($data)
	{
		$this->table->setData($data);
	}
}
