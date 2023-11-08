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
 * GUI class for Glossary Editor
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilGlossaryEditorGUI: ilObjGlossaryGUI
 */
class ilGlossaryEditorGUI implements ilCtrlBaseClassInterface
{
    protected int $requested_ref_id;
    protected \ILIAS\Glossary\Editing\EditingGUIRequest $request;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilAccessHandler $access;
    protected ilNavigationHistory $nav_history;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $ilNavigationHistory = $DIC["ilNavigationHistory"];

        $this->request = $DIC->glossary()
            ->internal()
            ->gui()
            ->editing()
            ->request();

        $this->requested_ref_id = $this->request->getRefId();

        // initialisation stuff
        $this->ctrl = $ilCtrl;
        $lng->loadLanguageModule("content");

        $DIC->globalScreen()->tool()->context()->claim()->repository();

        // check write permission
        if (!$ilAccess->checkAccess("write", "", $this->requested_ref_id) &&
            !$ilAccess->checkAccess("edit_content", "", $this->requested_ref_id)) {
            throw new ilPermissionException($lng->txt("permission_denied"));
        }

        $ilNavigationHistory->addItem(
            $this->requested_ref_id,
            "ilias.php?baseClass=ilGlossaryEditorGUI&ref_id=" . $this->requested_ref_id,
            "glo"
        );
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        if ($next_class == "") {
            $this->ctrl->saveParameterByClass(ilObjGlossaryGUI::class, "ref_id");
            $this->ctrl->redirectByClass(ilObjGlossaryGUI::class);
        }

        switch ($next_class) {
            case 'ilobjglossarygui':
            default:
                $glossary_gui = new ilObjGlossaryGUI(
                    "",
                    $this->requested_ref_id,
                    true,
                    false
                );
                $this->ctrl->forwardCommand($glossary_gui);
                break;
        }
    }
}
