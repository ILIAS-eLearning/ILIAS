<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Import related features for media pools (currently used for translation imports)
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilMediaPoolImportGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    protected $lm;

    /**
     * Constructor
     */
    public function __construct($a_mep)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->mep = $a_mep;
    }
    
    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;

        $cmd = $ilCtrl->getCmd("showTranslationImportForm");

        if (in_array($cmd, array("showTranslationImportForm", "importTranslation"))) {
            $this->$cmd();
        }
    }
    
    /**
     * Translation import
     *
     * @param
     * @return
     */
    public function showTranslationImportForm()
    {
        $lng = $this->lng;
        $tpl = $this->tpl;

        ilUtil::sendInfo($lng->txt("mep_trans_import_info"));
        $form = $this->initTranslationImportForm();
        $tpl->setContent($form->getHTML());
    }

    /**
     * Init translation input form.
     */
    public function initTranslationImportForm()
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
        foreach ($ot->getLanguages() as $l) {
            if ($l["lang_code"] != $ot->getMasterLanguage()) {
                $options[$l["lang_code"]] = $lng->txt("meta_l_" . $l["lang_code"]);
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

    /**
     * Import translation
     */
    public function importTranslation()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $imp = new ilImport();
        $conf = $imp->getConfig("Modules/MediaPool");

        $target_lang = ilUtil::stripSlashes($_POST["import_lang"]);
        $ot = ilObjectTranslation::getInstance($this->mep->getId());
        if ($target_lang == $ot->getMasterLanguage()) {
            ilUtil::sendFailure($lng->txt("mep_transl_master_language_not_allowed"), true);
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
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "showTranslationImportForm");
    }
}
