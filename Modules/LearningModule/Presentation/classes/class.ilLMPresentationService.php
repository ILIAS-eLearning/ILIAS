<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Main service init and factory
 *
 * @author @leifos.de
 * @ingroup
 */
class ilLMPresentationService
{
    /**
     * @var ilObjLearningModule
     */
    protected $lm;

    /**
     * @var ilLMTracker
     */
    protected $tracker;

    /**
     * Constructor
     */
    public function __construct(
        ilObjUser $user,
        array $query_params,
        bool $offline = false,
        bool $export_all_languages = false,
        string $export_format = "",
        ilCtrl $ctrl = null
    ) {
        global $DIC;

        $ctrl = (is_null($ctrl))
            ? $DIC->ctrl()
            : $ctrl;

        $this->request = new ilLMPresentationRequest($query_params);
        $this->user = $user;
        $this->ref_id = $this->request->getRequestedRefId();
        $this->lm_set = new ilSetting("lm");
        $this->lm_gui = new ilObjLearningModuleGUI([], $this->ref_id, true, false);
        $this->lm = $this->lm_gui->object;
        $this->lm_tree = ilLMTree::getInstance($this->lm->getId());
        $this->presentation_status = new ilLMPresentationStatus(
            $user,
            $this->lm,
            $this->lm_tree,
            $this->request->getRequestedTranslation(),
            $this->request->getRequestedFocusId(),
            $this->request->getRequestedFocusReturn(),
            (string) $this->request->getRequestedSearchString(),
            $offline,
            $export_all_languages,
            $export_format
        );

        $this->navigation_status = new ilLMNavigationStatus(
            $user,
            (int) $this->request->getRequestedObjId(),
            $this->lm_tree,
            $this->lm,
            $this->lm_set,
            $this->request->getRequestedCmd(),
            (int) $this->request->getRequestedFocusId()
        );

        $this->tracker = ilLMTracker::getInstance($this->lm->getRefId());
        $this->tracker->setCurrentPage($this->navigation_status->getCurrentPage());

        $this->linker = new ilLMPresentationLinker(
            $this->lm,
            $this->lm_tree,
            $this->navigation_status->getCurrentPage(),
            $this->request->getRequestedRefId(),
            $this->presentation_status->getLang(),
            $this->request->getRequestedBackPage(),
            $this->request->getRequestedFromPage(),
            $this->presentation_status->offline(),
            $this->presentation_status->getExportFormat(),
            $this->presentation_status->exportAllLanguages(),
            $ctrl
        );
    }

    /**
     * Get learning module settings
     *
     * @return ilSetting
     */
    public function getSettings() : ilSetting
    {
        return $this->lm_set;
    }

    /**
     * @return ilObjLearningModuleGUI
     */
    public function getLearningModuleGUI() : ilObjLearningModuleGUI
    {
        return $this->lm_gui;
    }

    /**
     * @return ilObjLearningModule
     */
    public function getLearningModule() : ilObjLearningModule
    {
        return $this->lm;
    }

    /**
     * @return ilLMTree
     */
    public function getLMTree() : ilLMTree
    {
        return $this->lm_tree;
    }

    /**
     * @return ilLMPresentationStatus
     */
    public function getPresentationStatus() : ilLMPresentationStatus
    {
        return $this->presentation_status;
    }

    /**
     * @return ilLMNavigationStatus
     */
    public function getNavigationStatus() : ilLMNavigationStatus
    {
        return $this->navigation_status;
    }

    /**
     * Get tracker
     *
     * @return ilLMTracker
     */
    public function getTracker()
    {
        return $this->tracker;
    }

    /**
     * Get request
     *
     * @return ilLMPresentationRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get linker
     *
     * @return ilLMPresentationLinker
     */
    public function getLinker()
    {
        return $this->linker;
    }
}
