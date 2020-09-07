<?php
/* Copyright (c) 1998-20014 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Table/classes/class.ilTable2GUI.php";

class ilLanguageExtTableGUI extends ilTable2GUI
{
    /**
     * Size of input fields
     * @var  string
     */
    private $inputsize = 40;
    private $commentsize = 30;

    /**
     * @var array   call parameters
     */
    private $params = array();

    public function __construct($a_parent_obj, $a_parent_cmd, $a_params = array())
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        // allow a different sorting/paging for admin and translation tables
        $this->params = $a_params;
        $this->setId("lang_ext_" . (ilObjLanguageAccess::_isPageTranslation() ? 'trans' : 'admin'));

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setRowTemplate("tpl.lang_items_row.html", "Services/Language");
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setDisableFilterHiding(true);

        $this->initFilter();

        // set the compare language
        $compare = $this->getFilterItemByPostVar('compare')->getValue();
        if ($compare == $this->params['lang_key']) {
            $compare_note = " " . $lng->txt("language_default_entries");
        }

        $this->addColumn(ucfirst($lng->txt("module")), "module", "10em");
        $this->addColumn(ucfirst($lng->txt("identifier")), "topic", "10em");
        $this->addColumn($lng->txt("meta_l_" . $this->params['lang_key']), "translation");
        $this->addColumn($lng->txt("meta_l_" . $compare) . $compare_note, "default");
        $this->addCommandButton('save', $lng->txt('save'));
    }


    /**
     * Fill a single data row.
     */
    protected function fillRow($data)
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        // mantis #25237
        // @see https://php.net/manual/en/language.variables.external.php
        $data['name'] = str_replace('.', '_POSTDOT_', $data['name']);
        $data['name'] = str_replace(' ', '_POSTSPACE_', $data['name']);

        if ($this->params['langmode']) {
            $this->tpl->setCurrentBlock('comment');
            $this->tpl->setVariable("COM_ID", ilUtil::prepareFormOutput($data["name"] . $lng->separator . "comment"));
            $this->tpl->setVariable("COM_NAME", ilUtil::prepareFormOutput($data["name"] . $lng->separator . "comment"));
            $this->tpl->setVariable("COM_VALUE", ilUtil::prepareFormOutput($data["comment"]));
            $this->tpl->setVariable("COM_SIZE", $this->commentsize);
            $this->tpl->setVariable("COM_MAX", 250);
            $this->tpl->setVariable("TXT_COMMENT", $lng->txt('comment'));
            $this->tpl->parseCurrentBlock();
        } else {
            $this->tpl->setCurrentBlock('hidden_comment');
            $this->tpl->setVariable("COM_NAME", ilUtil::prepareFormOutput($data["name"] . $lng->separator . "comment"));
            $this->tpl->setVariable("COM_VALUE", ilUtil::prepareFormOutput($data["comment"]));
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("T_ROWS", ceil(strlen($data["translation"]) / $this->inputsize));
        $this->tpl->setVariable("T_SIZE", $this->inputsize);
        $this->tpl->setVariable("T_NAME", ilUtil::prepareFormOutput($data["name"]));
        $this->tpl->setVariable("T_USER_VALUE", ilUtil::prepareFormOutput($data["translation"]));

        $this->tpl->setVariable("MODULE", ilUtil::prepareFormOutput($data["module"]));
        $this->tpl->setVariable("TOPIC", ilUtil::prepareFormOutput($data["topic"]));

        $this->tpl->setVariable("DEFAULT_VALUE", ilUtil::prepareFormOutput($data["default"]));
        $this->tpl->setVariable("COMMENT", ilUtil::prepareFormOutput($data["default_comment"]));
    }

    /**
     * Init filter
    */
    public function initFilter()
    {
        global $DIC;
        $lng = $DIC->language();

        // most filters are only
        if (!ilObjLanguageAccess::_isPageTranslation()) {
            // pattern
            include_once("./Services/Form/classes/class.ilTextInputGUI.php");
            $ti = new ilTextInputGUI($lng->txt("search"), "pattern");
            $ti->setParent($this->parent_obj);
            $ti->setMaxLength(64);
            $ti->setSize(20);
            $this->addFilterItem($ti);
            $ti->readFromSession();

            // module
            $options = array();
            $options["all"] = $lng->txt("language_all_modules");
            $modules = ilObjLanguageExt::_getModules($lng->getLangKey());
            foreach ($modules as $mod) {
                $options[$mod] = $mod;
            }

            include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
            $si = new ilSelectInputGUI(ucfirst($lng->txt("module")), "module");
            $si->setParent($this->parent_obj);
            $si->setOptions($options);
            $this->addFilterItem($si);
            $si->readFromSession();
            if (!$si->getValue()) {
                $si->setValue('administration');
            }

            // identifier
            include_once("./Services/Form/classes/class.ilTextInputGUI.php");
            $ti = new ilTextInputGUI(ucfirst($lng->txt("identifier")), "identifier");
            $ti->setParent($this->parent_obj);
            $ti->setMaxLength(200);
            $ti->setSize(20);
            $this->addFilterItem($ti);
            $ti->readFromSession();

            // mode
            $options = array();
            $options["all"] = $lng->txt("language_scope_global");
            $options["changed"] = $lng->txt("language_scope_local");
            if ($this->params['langmode']) {
                $options["added"] = $lng->txt("language_scope_added");
            }
            $options["unchanged"] = $lng->txt("language_scope_unchanged");
            $options["equal"] = $lng->txt("language_scope_equal");
            $options["different"] = $lng->txt("language_scope_different");
            $options["commented"] = $lng->txt("language_scope_commented");
            if ($this->params['langmode']) {
                $options["dbremarks"] = $lng->txt("language_scope_dbremarks");
            }
            $options["conflicts"] = $lng->txt("language_scope_conflicts");

            include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
            $si = new ilSelectInputGUI($lng->txt("filter"), "mode");
            $si->setParent($this->parent_obj);
            $si->setOptions($options);
            $this->addFilterItem($si);
            $si->readFromSession();
            if (!$si->getValue()) {
                $si->setValue('all');
            }
        }

        //compare
        $options = array();
        $langlist = $lng->getInstalledLanguages();
        foreach ($langlist as $lang_key) {
            $options[$lang_key] = $lng->txt("meta_l_" . $lang_key);
        }

        include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
        $si = new ilSelectInputGUI($lng->txt("language_compare"), "compare");
        $si->setParent($this->parent_obj);
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        if (!$si->getValue()) {
            $si->setValue($lng->getDefaultLanguage());
        }
    }
}
