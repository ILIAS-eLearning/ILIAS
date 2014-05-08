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

	public function __construct(ilCtrl $ctrl, ilLanguage $lng)
	{
		$this->ctrl = $ctrl;
		$this->lng = $lng;

		parent::__construct();
	}
	
	public function build()
	{
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
			$this->addButton( $this->lng->txt('tst_btn_show_best_solutions'), $this->getShowBestSolutionsLinkTarget() );
		}
		elseif( strlen($this->getHideBestSolutionsLinkTarget()) )
		{
			$this->addButton( $this->lng->txt('tst_btn_hide_best_solutions'), $this->getHideBestSolutionsLinkTarget() );
		}
	}

	private function getPdfExportLabel()
	{
		$src = ilUtil::getHtmlPath(ilUtil::getImagePath("application-pdf.png"));
		$img = '<img src="'.$src.'" style="height: 14px; position:relative; top: -1px; margin-right: 3px;" />';

		return $img . $this->lng->txt('pdf_export');
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
}
