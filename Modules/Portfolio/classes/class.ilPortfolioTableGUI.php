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
 * Portfolio table
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPortfolioTableGUI extends ilTable2GUI
{
    protected array $shared_objects;
    protected ilObjUser $user;
    protected int $user_id;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $a_user_id
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->user_id = $a_user_id;
    
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

    protected function getItems() : void
    {
        $access_handler = new ilPortfolioAccessHandler();
        $data = ilObjPortfolio::getPortfoliosOfUser($this->user_id);
        $this->shared_objects = $access_handler->getObjectsIShare(false);
        
        $this->setData($data);
    }

    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $this->tpl->setCurrentBlock("title_form");
        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable("VAL_TITLE", ilLegacyFormElementsUtil::prepareFormOutput($a_set["title"]));
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
        $this->tpl->setVariable("TXT_ACTION", $lng->txt("user_profile_preview"));
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
