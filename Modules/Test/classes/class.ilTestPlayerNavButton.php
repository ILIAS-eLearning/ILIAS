<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestPlayerNavButton extends ilLinkButton
{
	/**
	 * @var string
	 */
	private $nextCommand = '';

	/**
	 * @return string
	 */
	public function getNextCommand()
	{
		return $this->nextCommand;
	}

	/**
	 * @param string $nextCommand
	 */
	public function setNextCommand($nextCommand)
	{
		$this->nextCommand = $nextCommand;
	}

	/**
	 * @return string
	 */
	public function render()
	{
		$this->prepareRender();

		$attr = array(
			'href' => $this->getUrl() ? $this->getUrl() : "#",
			'target' => $this->getTarget()
		);
		
		if( strlen($this->getNextCommand()) )
		{
			$attr['data-nextcmd'] = $this->getNextCommand();
		}

		return '<a'.$this->renderAttributes($attr).'>'.$this->renderCaption().'</a>';
	}

	public static function getInstance()
	{
		return new self(self::TYPE_LINK);
	}
}