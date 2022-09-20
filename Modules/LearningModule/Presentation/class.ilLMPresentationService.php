<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\LearningModule\Presentation\PresentationGUIRequest;

/**
 * Main service init and factory
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMPresentationService
{
    protected ilLMPresentationLinker $linker;
    protected ilLMNavigationStatus $navigation_status;
    protected ilLMPresentationStatus $presentation_status;
    protected ilLMTree $lm_tree;
    protected ilObjLearningModuleGUI $lm_gui;
    protected ilSetting $lm_set;
    protected int $ref_id;
    protected ilObjUser $user;
    protected PresentationGUIRequest $request;
    protected ilObjLearningModule $lm;
    protected ilLMTracker $tracker;

    public function __construct(
        ilObjUser $user,
        ?array $query_params,
        bool $offline = false,
        bool $export_all_languages = false,
        string $export_format = "",
        ilCtrl $ctrl = null,
        bool $embed_mode = false
    ) {
        global $DIC;

        $ctrl = (is_null($ctrl))
            ? $DIC->ctrl()
            : $ctrl;

        $post = is_null($query_params)
            ? null
            : [];

        $this->request = $DIC->learningModule()
            ->internal()
            ->gui()
            ->presentation()
            ->request(
                $query_params,
                $post
            );

        $this->user = $user;
        $this->ref_id = $this->request->getRefId();
        $this->lm_set = new ilSetting("lm");
        $this->lm_gui = new ilObjLearningModuleGUI([], $this->ref_id, true, false);
        /** @var ilObjLearningModule $lm */
        $lm = $this->lm_gui->getObject();
        $this->lm = $lm;
        $this->lm_tree = ilLMTree::getInstance($this->lm->getId());
        $this->presentation_status = new ilLMPresentationStatus(
            $user,
            $this->lm,
            $this->lm_tree,
            $this->request->getTranslation(),
            $this->request->getFocusId(),
            $this->request->getFocusReturn(),
            $this->request->getSearchString(),
            $offline,
            $export_all_languages,
            $export_format
        );

        $this->navigation_status = new ilLMNavigationStatus(
            $user,
            $this->request->getObjId(),
            $this->lm_tree,
            $this->lm,
            $this->lm_set,
            $this->request->getBackPage(),
            $this->request->getCmd(),
            $this->request->getFocusId()
        );

        $this->tracker = ilLMTracker::getInstance($this->lm->getRefId());
        $this->tracker->setCurrentPage($this->navigation_status->getCurrentPage());

        $this->linker = new ilLMPresentationLinker(
            $this->lm,
            $this->lm_tree,
            $this->navigation_status->getCurrentPage(),
            $this->request->getRefId(),
            $this->presentation_status->getLang(),
            $this->request->getBackPage(),
            $this->request->getFromPage(),
            $this->presentation_status->offline(),
            $this->presentation_status->getExportFormat(),
            $this->presentation_status->exportAllLanguages(),
            $ctrl,
            $embed_mode,
            $this->request->getFrame(),
            $this->request->getObjId()
        );
    }

    /**
     * Get learning module settings
     */
    public function getSettings(): ilSetting
    {
        return $this->lm_set;
    }

    public function getLearningModuleGUI(): ilObjLearningModuleGUI
    {
        return $this->lm_gui;
    }

    public function getLearningModule(): ilObjLearningModule
    {
        return $this->lm;
    }

    public function getLMTree(): ilLMTree
    {
        return $this->lm_tree;
    }

    public function getPresentationStatus(): ilLMPresentationStatus
    {
        return $this->presentation_status;
    }

    public function getNavigationStatus(): ilLMNavigationStatus
    {
        return $this->navigation_status;
    }

    public function getTracker(): ilLMTracker
    {
        return $this->tracker;
    }

    public function getRequest(): PresentationGUIRequest
    {
        return $this->request;
    }

    public function getLinker(): ilLMPresentationLinker
    {
        return $this->linker;
    }
}
