<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Simple panel class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup Services/UIComponent
 */
class ilPanelGUI
{
	const PANEL_STYLE_PRIMARY = 0;
	const PANEL_STYLE_SECONDARY = 1;

	const HEADING_STYLE_SUBHEADING = 0;
	const HEADING_STYLE_BLOCK = 1;
	
	const FOOTER_STYLE_BLOCK = 0;

	protected $heading = "";
	protected $body = "";
	protected $footer = "";
	protected $panel_style = 0;
	protected $heading_style = 0;	
	protected $footer_style = 0;	

	/**
	 * Constructor
	 */
	protected function  __construct()
	{

	}

	/**
	 * Get instance
	 *
	 * @return ilPanelGUI panel instance
	 */
	static function getInstance()
	{
		return new ilPanelGUI();
	}

	/**
	 * Set heading
	 *
	 * @param string $a_val heading
	 */
	function setHeading($a_val)
	{
		$this->heading = $a_val;
	}

	/**
	 * Get heading
	 *
	 * @return string heading
	 */
	function getHeading()
	{
		return $this->heading;
	}

	/**
	 * Set body
	 *
	 * @param string $a_val body
	 */
	function setBody($a_val)
	{
		$this->body = $a_val;
	}

	/**
	 * Get body
	 *
	 * @return string body
	 */
	function getBody()
	{
		return $this->body;
	}
	
	/**
	 * Set footer
	 *
	 * @param string $a_val footer
	 */
	function setFooter($a_val)
	{
		$this->footer = $a_val;
	}

	/**
	 * Get body
	 *
	 * @return string body
	 */
	function getFooter()
	{
		return $this->footer;
	}

	/**
	 * Set panel style
	 *
	 * @param int $a_val panel_style
	 */
	function setPanelStyle($a_val)
	{
		$this->panel_style = $a_val;
	}

	/**
	 * Get panel style
	 *
	 * @return int panel_style
	 */
	function getPanelStyle()
	{
		return $this->panel_style;
	}

	/**
	 * Set heading style
	 *
	 * @param int $a_val heading style
	 */
	function setHeadingStyle($a_val)
	{
		$this->heading_style = $a_val;
	}

	/**
	 * Get heading style
	 *
	 * @return int heading style
	 */
	function getHeadingStyle()
	{
		return $this->heading_style;
	}
	
	/**
	 * Set footer style
	 *
	 * @param int $a_val footer style
	 */
	function setFooterStyle($a_val)
	{
		$this->footer_style = $a_val;
	}

	/**
	 * Get footer style
	 *
	 * @return int footer style
	 */
	function getFooterStyle()
	{
		return $this->footer_style;
	}

	/**
	 * Get HTML
	 *
	 * @return string html
	 */
	function getHTML()
	{
		$tpl = new ilTemplate("tpl.panel.html", true, true, "Services/UIComponent/Panel");

		$head_outer_div_style = "";
		if ($this->getHeading() != "")
		{
			$tpl->setCurrentBlock("heading");
			$tpl->setVariable("HEADING", $this->getHeading());

			switch ($this->getHeadingStyle())
			{
				case self::HEADING_STYLE_BLOCK:
					$tpl->setVariable("HEAD_DIV_STYLE", "panel-heading ilBlockHeader");
					$tpl->setVariable("HEAD_H3_STYLE", "ilBlockHeader");
					$head_outer_div_style = "il_Block";
					break;

				case self::HEADING_STYLE_SUBHEADING:
					$tpl->setVariable("HEAD_DIV_STYLE", "panel-heading ilHeader");
					$tpl->setVariable("HEAD_H3_STYLE", "ilHeader");
					break;
			}

			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("BODY", $this->getBody());
		
		if ($this->getFooter() != "")
		{
			$tpl->setCurrentBlock("footer");
			$tpl->setVariable("FOOTER", $this->getFooter());
			
			switch ($this->getFooterStyle())
			{
				case self::FOOTER_STYLE_BLOCK:
					$tpl->setVariable("FOOT_DIV_STYLE", "panel-footer ilBlockInfo");					
					break;
			}
						
			$tpl->parseCurrentBlock();
		}

		switch ($this->getPanelStyle())
		{
			case self::PANEL_STYLE_SECONDARY:
				$tpl->setVariable("PANEL_STYLE", "panel panel-default ".$head_outer_div_style);
				break;

			default:
				$tpl->setVariable("PANEL_STYLE", "panel panel-primary ".$head_outer_div_style);
				break;
		}				

		return $tpl->get();
	}


}

?>