<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Portfolio table
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPortfolioTableGUI extends ilTable2GUI
{
    /**
     * @var ilObjUser
     */
    protected $user;

    protected $user_id;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_user_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->user_id = (int) $a_user_id;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($lng->txt("prtf_portfolios"));

        $this->addColumn($this->lng->txt(""), "", "1");
        $this->addColumn($this->lng->txt("title"), "title", "50%");
        $this->addColumn($this->lng->txt("online"), "is_online");
        $this->addColumn($this->lng->txt("prtf_default_portfolio"), "is_default");
        $this->addColumn($this->lng->txt("actions"));

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.portfolio_row.html", "Modules/Portfolio");

        $this->addMultiCommand("confirmPortfolioDeletion", $lng->txt("delete"));
        $this->addCommandButton("saveTitles", $lng->txt("prtf_save_status_and_titles"));

        $this->getItems();

        $lng->loadLanguageModule("wsp");
    }

    protected function getItems()
    {
        $ilUser = $this->user;

        $access_handler = new ilPortfolioAccessHandler();

        $data = ilObjPortfolio::getPortfoliosOfUser($this->user_id);

        $this->shared_objects = $access_handler->getObjectsIShare(false);

        $this->setData($data);
    }

    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->tpl->setCurrentBlock("title_form");
        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable("VAL_TITLE", ilUtil::prepareFormOutput($a_set["title"]));
        $this->tpl->parseCurrentBlock();

        if (in_array($a_set["id"], $this->shared_objects)) {
            $this->tpl->setCurrentBlock("shared");
            $this->tpl->setVariable("TXT_SHARED", $lng->txt("wsp_status_shared"));
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setCurrentBlock("chck");
        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->parseCurrentBlock();

        $this->tpl->setCurrentBlock("edit");
        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable(
            "STATUS_ONLINE",
            ($a_set["is_online"]) ? " checked=\"checked\"" : ""
        );
        $this->tpl->setVariable(
            "VAL_DEFAULT",
            ($a_set["is_default"]) ? $lng->txt("yes") : ""
        );
        $this->tpl->parseCurrentBlock();

        $prtf_path = array(get_class($this->parent_obj), "ilobjportfoliogui");

        $ilCtrl->setParameterByClass("ilobjportfoliogui", "prt_id", $a_set["id"]);
        $this->tpl->setCurrentBlock("action");

        $this->tpl->setVariable(
            "URL_ACTION",
            $ilCtrl->getLinkTargetByClass($prtf_path, "preview")
        );
        $this->tpl->setVariable("TXT_ACTION", $lng->txt("preview"));
        $this->tpl->parseCurrentBlock();

        $this->tpl->setVariable(
            "URL_ACTION",
            $ilCtrl->getLinkTargetByClass($prtf_path, "view")
        );
        $this->tpl->setVariable("TXT_ACTION", $lng->txt("prtf_edit_portfolio"));
        $this->tpl->parseCurrentBlock();

        $ilCtrl->setParameterByClass("ilobjportfoliogui", "prt_id", "");

        if ($a_set["is_online"]) {
            if (!$a_set["is_default"]) {
                $ilCtrl->setParameter($this->parent_obj, "prt_id", $a_set["id"]);

                $this->tpl->setVariable(
                    "URL_ACTION",
                    $ilCtrl->getLinkTarget($this->parent_obj, "setDefaultConfirmation")
                );
                $this->tpl->setVariable("TXT_ACTION", $lng->txt("prtf_set_as_default"));
                $this->tpl->parseCurrentBlock();

                $ilCtrl->setParameter($this->parent_obj, "prt_id", "");
            } else {
                $this->tpl->setVariable(
                    "URL_ACTION",
                    $ilCtrl->getLinkTarget($this->parent_obj, "unsetDefault")
                );
                $this->tpl->setVariable("TXT_ACTION", $lng->txt("prtf_unset_as_default"));
                $this->tpl->parseCurrentBlock();
            }
        }
    }
}
