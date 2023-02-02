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
 * Import related features for media pools (currently used for translation imports)
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaPoolImportGUI
{
    protected \ILIAS\MediaPool\StandardGUIRequest $request;
    protected ilObjMediaPool $mep;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;

    public function __construct(ilObjMediaPool $a_mep)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->mep = $a_mep;
        $this->request = $DIC->mediaPool()
            ->internal()
            ->gui()
            ->standardRequest();
    }

    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;

        $cmd = $ilCtrl->getCmd("showTranslationImportForm");

        if (in_array($cmd, array("showTranslationImportForm", "importTranslation"))) {
            $this->$cmd();
        }
    }

    public function showTranslationImportForm(): void
    {
        $lng = $this->lng;
        $tpl = $this->tpl;

        $this->tpl->setOnScreenMessage('info', $lng->txt("mep_trans_import_info"));
        $form = $this->initTranslationImportForm();
        $tpl->setContent($form->getHTML());
    }

    public function initTranslationImportForm(): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $lng->loadLanguageModule("meta");

        $form = new ilPropertyFormGUI();

        // import file
        $fi = new ilFileInputGUI($lng->txt("file"), "importfile");
        $fi->setSuffixes(array("zip"));
        $fi->setRequired(true);
        $fi->setSize(30);
        $form->addItem($fi);

        $ot = ilObjectTranslation::getInstance($this->mep->getId());
        $options = [];
        foreach ($ot->getLanguages() as $l) {
            if ($l->getLanguageCode() != $ot->getMasterLanguage()) {
                $options[$l->getLanguageCode()] = $lng->txt("meta_l_" . $l->getLanguageCode());
            }
        }
        $si = new ilSelectInputGUI($lng->txt("mep_import_lang"), "import_lang");
        $si->setOptions($options);
        $form->addItem($si);

        $form->addCommandButton("importTranslation", $lng->txt("import"));
        $form->setTitle($lng->txt("mep_import_trans"));
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }

    public function importTranslation(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $imp = new ilImport();

        /** @var ilMediaPoolImportConfig $conf */
        $conf = $imp->getConfig("Modules/MediaPool");

        $target_lang = $this->request->getImportLang();
        $ot = ilObjectTranslation::getInstance($this->mep->getId());
        if ($target_lang === $ot->getMasterLanguage()) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("mep_transl_master_language_not_allowed"), true);
            $ilCtrl->redirect($this, "showTranslationImportForm");
        }

        $conf->setTranslationImportMode($this->mep, $target_lang);
        $imp->importObject(
            null,
            $_FILES["importfile"]["tmp_name"],
            $_FILES["importfile"]["name"],
            "mep",
            "Modules/MediaPool"
        );
        //echo "h"; exit;
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "showTranslationImportForm");
    }
}
