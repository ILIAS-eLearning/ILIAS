<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Import related features for learning modules
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesLearningModule
 */
class ilLMImportGUI
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
    public function __construct($a_lm)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->lm = $a_lm;
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

        ilUtil::sendInfo($lng->txt("cont_trans_import_info"));
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

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        // import file
        $fi = new ilFileInputGUI($lng->txt("file"), "importfile");
        $fi->setSuffixes(array("zip"));
        $fi->setRequired(true);
        $fi->setSize(30);
        $form->addItem($fi);

        include_once("./Services/MetaData/classes/class.ilMDLanguageItem.php");
        include_once("./Services/Object/classes/class.ilObjectTranslation.php");
        $ot = ilObjectTranslation::getInstance($this->lm->getId());
        foreach ($ot->getLanguages() as $l) {
            if ($l["lang_code"] != $ot->getMasterLanguage()) {
                $options[$l["lang_code"]] = $lng->txt("meta_l_" . $l["lang_code"]);
            }
        }
        $si = new ilSelectInputGUI($lng->txt("cont_import_lang"), "import_lang");
        $si->setOptions($options);
        $form->addItem($si);

        $form->addCommandButton("importTranslation", $lng->txt("import"));
        $form->setTitle($lng->txt("cont_import_trans"));
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

        include_once("./Services/Export/classes/class.ilImport.php");
        $imp = new ilImport();
        $conf = $imp->getConfig("Modules/LearningModule");

        $target_lang = ilUtil::stripSlashes($_POST["import_lang"]);
        include_once("./Services/Object/classes/class.ilObjectTranslation.php");
        $ot = ilObjectTranslation::getInstance($this->lm->getId());
        if ($target_lang == $ot->getMasterLanguage() || $target_lang == "") {
            ilUtil::sendFailure($lng->txt("cont_transl_master_language_not_allowed"), true);
            $ilCtrl->redirect($this, "showTranslationImportForm");
        }

        $conf->setTranslationImportMode($this->lm, $target_lang);
        $imp->importObject(
            null,
            $_FILES["importfile"]["tmp_name"],
            $_FILES["importfile"]["name"],
            "lm",
            "Modules/LearningModule"
        );
        //echo "h"; exit;
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "showTranslationImportForm");
    }
}
