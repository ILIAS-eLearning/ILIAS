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

/**
* @author Alexander Killing <killing@leifos.de>
* @ilCtrl_Calls ilObjLearningModuleGUI: ilLMPageObjectGUI, ilStructureObjectGUI, ilObjectContentStyleSettingsGUI, ilObjectMetaDataGUI
* @ilCtrl_Calls ilObjLearningModuleGUI: ilLearningProgressGUI, ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjLearningModuleGUI: ilExportGUI, ilCommonActionDispatcherGUI, ilPageMultiLangGUI, ilObjectTranslationGUI
* @ilCtrl_Calls ilObjLearningModuleGUI: ilMobMultiSrtUploadGUI, ilLMImportGUI, ilLMEditShortTitlesGUI, ilLTIProviderObjectSettingGUI
*/
class ilObjLearningModuleGUI extends ilObjContentObjectGUI
{
    protected ilLMTree $lm_tree;

    /**
     * @param mixed $a_data
     */
    public function __construct(
        $a_data,
        int $a_id = 0,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        $this->type = "lm";

        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        # BETTER DO IT HERE THAN IN PARENT CLASS ( PROBLEMS FOR import, create)
        $this->assignObject();

        // SAME REASON
        if ($a_id != 0) {
            $this->lm_tree = $this->object->getLMTree();
        }
    }

    protected function assignObject(): void
    {
        $this->link_params = "ref_id=" . $this->ref_id;
        $this->object = new ilObjLearningModule($this->id, true);
        /** @var ilObjLearningModule $lm */
        $lm = $this->object;
        $this->lm = $lm;
    }

    public function view(): void
    {
        if (strtolower($this->edit_request->getBaseClass()) == "iladministrationgui") {
            $this->prepareOutput();
            parent::viewObject();
        } else {
            $this->properties();
        }
    }
}
