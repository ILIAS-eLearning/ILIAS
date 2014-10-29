<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';

/**
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @package	Modules/Test
 */
class ilTestResultsToolbarGUI extends ilToolbarGUI
{
	/**
	 * @var ilCtrl
	 */
	public $ctrl = null;

	/**
	 * @var ilTemplate
	 */
	public $tpl = null;

	/**
	 * @var ilLanguage
	 */
	public $lng = null;

	/**
	 * @var string
	 */
	private $pdfExportLinkTarget = null;

	/**
	 * @var string
	 */
	private $certificateLinkTarget = null;

	/**
	 * @var string
	 */
	private $showBestSolutionsLinkTarget = null;

	/**
	 * @var string
	 */
	private $hideBestSolutionsLinkTarget = null;

	/**
	 * @var array
	 */
	private $participantSelectorOptions = array();

	public function __construct(ilCtrl $ctrl, ilTemplate $tpl, ilLanguage $lng)
	{
		$this->ctrl = $ctrl;
		$this->tpl = $tpl;
		$this->lng = $lng;

		parent::__construct();
	}
	
	public function build()
	{
		$this->setId('tst_results_toolbar');
		
		$this->addButton($this->lng->txt('print'), 'javascript:window.print();');

		if( strlen($this->getPdfExportLinkTarget()) )
		{
			$this->addButton( $this->getPdfExportLabel(), $this->getPdfExportLinkTarget() );
		}

		if( strlen($this->getCertificateLinkTarget()) )
		{
			$this->addButton( $this->lng->txt('certificate'), $this->getCertificateLinkTarget() );
		}

		if( strlen($this->getShowBestSolutionsLinkTarget()) )
		{
			$this->addSeparator();
			$this->addButton( $this->lng->txt('tst_btn_show_best_solutions'), $this->getShowBestSolutionsLinkTarget() );
		}
		elseif( strlen($this->getHideBestSolutionsLinkTarget()) )
		{
			$this->addSeparator();
			$this->addButton( $this->lng->txt('tst_btn_hide_best_solutions'), $this->getHideBestSolutionsLinkTarget() );
		}
		
		if( count($this->getParticipantSelectorOptions()) )
		{
			$this->addSeparator();

			require_once 'Services/Form/classes/class.ilSelectInputGUI.php';
			$sel = new ilSelectInputGUI('', 'active_id');
			$sel->setOptions($this->getParticipantSelectorOptionsWithHintOption());
			$this->addInputItem($sel);
			
			require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
			$link = ilLinkButton::getInstance(); // always returns a new instance
			$link->setUrl('#');
			$link->setId('ilTestResultParticipantJumper');
			$link->setCaption($this->lng->txt('tst_res_jump_to_participant_btn'));
			$this->addButtonInstance($link);
			
			$this->tpl->addJavaScript('Modules/Test/js/ilTestResultParticipantSelector.js');
		}
	}

	private function getPdfExportLabel()
	{
		return $this->lng->txt('pdf_export');
	}

	public function setPdfExportLinkTarget($pdfExportLinkTarget)
	{
		$this->pdfExportLinkTarget = $pdfExportLinkTarget;
	}

	public function getPdfExportLinkTarget()
	{
		return $this->pdfExportLinkTarget;
	}

	public function setCertificateLinkTarget($certificateLinkTarget)
	{
		$this->certificateLinkTarget = $certificateLinkTarget;
	}

	public function getCertificateLinkTarget()
	{
		return $this->certificateLinkTarget;
	}

	public function setShowBestSolutionsLinkTarget($showBestSolutionsLinkTarget)
	{
		$this->showBestSolutionsLinkTarget = $showBestSolutionsLinkTarget;
	}

	public function getShowBestSolutionsLinkTarget()
	{
		return $this->showBestSolutionsLinkTarget;
	}

	public function setHideBestSolutionsLinkTarget($hideBestSolutionsLinkTarget)
	{
		$this->hideBestSolutionsLinkTarget = $hideBestSolutionsLinkTarget;
	}

	public function getHideBestSolutionsLinkTarget()
	{
		return $this->hideBestSolutionsLinkTarget;
	}

	public function setParticipantSelectorOptions($participantSelectorOptions)
	{
		$this->participantSelectorOptions = $participantSelectorOptions;
	}

	public function getParticipantSelectorOptions()
	{
		return $this->participantSelectorOptions;
	}
	
	public function getParticipantSelectorOptionsWithHintOption()
	{
		$options = array($this->lng->txt('tst_res_jump_to_participant_hint_opt'));
		
		if( function_exists('array_replace') )
		{
			return array_replace($options, $this->getParticipantSelectorOptions());
		}
		
		foreach($this->getParticipantSelectorOptions() as $key => $val)
		{
			$options[$key] = $val;
		}

		return $options;
	}
}
