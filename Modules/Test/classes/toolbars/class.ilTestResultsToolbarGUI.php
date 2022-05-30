<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';

/**
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @package	Modules/Test
 */
class ilTestResultsToolbarGUI extends ilToolbarGUI
{
    public ilCtrl $ctrl;
    public ilGlobalTemplateInterface $tpl;

    private ?string $pdfExportLinkTarget = null;
    private ?string $certificateLinkTarget = null;
    private ?string $showBestSolutionsLinkTarget = null;
    private ?string $hideBestSolutionsLinkTarget = null;
    private array $participantSelectorOptions = array();

    public function __construct(ilCtrl $ctrl, ilGlobalTemplateInterface $tpl, ilLanguage $lng)
    {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        parent::__construct();
    }
    
    public function build() : void
    {
        $this->setId('tst_results_toolbar');
        
        $this->addButton($this->lng->txt('print'), 'javascript:window.print();');

        if (strlen($this->getPdfExportLinkTarget())) {
            require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
            $link = ilLinkButton::getInstance(); // always returns a new instance
            $link->setUrl($this->getPdfExportLinkTarget());
            $link->setCaption($this->getPdfExportLabel(), false);
            $link->setOmitPreventDoubleSubmission(true);
            $this->addButtonInstance($link);
        }

        if (strlen($this->getCertificateLinkTarget())) {
            $this->addButton($this->lng->txt('certificate'), $this->getCertificateLinkTarget());
        }

        if (strlen($this->getShowBestSolutionsLinkTarget())) {
            $this->addSeparator();
            $this->addButton($this->lng->txt('tst_btn_show_best_solutions'), $this->getShowBestSolutionsLinkTarget());
        } elseif (strlen($this->getHideBestSolutionsLinkTarget())) {
            $this->addSeparator();
            $this->addButton($this->lng->txt('tst_btn_hide_best_solutions'), $this->getHideBestSolutionsLinkTarget());
        }
        
        if (count($this->getParticipantSelectorOptions())) {
            $this->addSeparator();

            require_once 'Services/Form/classes/class.ilSelectInputGUI.php';
            $sel = new ilSelectInputGUI('', 'active_id');
            $sel->setOptions($this->getParticipantSelectorOptionsWithHintOption());
            $this->addInputItem($sel);
            
            $link = ilLinkButton::getInstance(); // always returns a new instance
            $link->setUrl('#');
            $link->setId('ilTestResultParticipantJumper');
            $link->setCaption($this->lng->txt('tst_res_jump_to_participant_btn'), false);
            $this->addButtonInstance($link);
            
            $this->tpl->addJavaScript('Modules/Test/js/ilTestResultParticipantSelector.js');
        }
    }

    private function getPdfExportLabel() : string
    {
        return $this->lng->txt('pdf_export');
    }

    public function setPdfExportLinkTarget(string $pdfExportLinkTarget) : void
    {
        $this->pdfExportLinkTarget = $pdfExportLinkTarget;
    }

    public function getPdfExportLinkTarget() : ?string
    {
        return $this->pdfExportLinkTarget;
    }

    public function setCertificateLinkTarget(string $certificateLinkTarget) : void
    {
        $this->certificateLinkTarget = $certificateLinkTarget;
    }

    public function getCertificateLinkTarget() : ?string
    {
        return $this->certificateLinkTarget;
    }

    public function setShowBestSolutionsLinkTarget(string $showBestSolutionsLinkTarget) : void
    {
        $this->showBestSolutionsLinkTarget = $showBestSolutionsLinkTarget;
    }

    public function getShowBestSolutionsLinkTarget() : ?string
    {
        return $this->showBestSolutionsLinkTarget;
    }

    public function setHideBestSolutionsLinkTarget(string $hideBestSolutionsLinkTarget) : void
    {
        $this->hideBestSolutionsLinkTarget = $hideBestSolutionsLinkTarget;
    }

    public function getHideBestSolutionsLinkTarget() : ?string
    {
        return $this->hideBestSolutionsLinkTarget;
    }

    public function setParticipantSelectorOptions(array $participantSelectorOptions) : void
    {
        $this->participantSelectorOptions = $participantSelectorOptions;
    }

    public function getParticipantSelectorOptions() : array
    {
        return $this->participantSelectorOptions;
    }
    
    public function getParticipantSelectorOptionsWithHintOption() : array
    {
        $options = array($this->lng->txt('tst_res_jump_to_participant_hint_opt'));
        
        if (function_exists('array_replace')) {
            return array_replace($options, $this->getParticipantSelectorOptions());
        }
        
        foreach ($this->getParticipantSelectorOptions() as $key => $val) {
            $options[$key] = $val;
        }

        return $options;
    }
}
