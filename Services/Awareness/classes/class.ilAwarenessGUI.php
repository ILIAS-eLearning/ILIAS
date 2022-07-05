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

use ILIAS\Awareness\InternalDataService;
use ILIAS\Awareness\InternalDomainService;
use ILIAS\Awareness\InternalGUIService;
use ILIAS\Awareness\WidgetManager;
use ILIAS\DI\UIServices;

/**
 * Awareness GUI class
 * @author Alexander Killing <killing@leifos.de>
 */
class ilAwarenessGUI implements ilCtrlBaseClassInterface
{
    protected ilGlobalTemplateInterface $main_tpl;
    protected int $ref_id;
    protected \ILIAS\Awareness\StandardGUIRequest $request;
    protected WidgetManager $manager;
    protected ilObjUser $user;
    protected ilCtrl $ctrl;
    protected UIServices $ui;
    protected ilLanguage $lng;
    protected InternalDataService $data_service;
    
    public function __construct(
        InternalDataService $data_service = null,
        InternalDomainService $domain_service = null,
        InternalGUIService $gui_service = null
    ) {
        global $DIC;

        $this->data_service = $data_service
            ?? $DIC->awareness()->internal()->data();
        $domain_service = $domain_service
            ?? $DIC->awareness()->internal()->domain();
        $gui_service = $gui_service
            ?? $DIC->awareness()->internal()->gui();
        $this->user = $domain_service->user();
        $this->lng = $domain_service->lng();
        $this->ui = $gui_service->ui();
        $this->ctrl = $gui_service->ctrl();

        $this->lng->loadLanguageModule("awrn");
        $this->request = $gui_service->standardRequest();
        $this->main_tpl = $gui_service->mainTemplate();

        $this->ref_id = $this->request->getRefId();
        $this->manager = $domain_service->widget(
            $this->user->getId(),
            $this->ref_id
        );
    }

    public function executeCommand() : void
    {
        $cmd = $this->ctrl->getCmd();

        if (in_array($cmd, array("getAwarenessList"))) {
            $this->$cmd();
        }
    }

    public function initJS() : void
    {
        $ilUser = $this->user;
        // init js
        $this->main_tpl->addJavaScript("./Services/Awareness/js/Awareness.js");
        $this->ctrl->setParameter($this, "ref_id", $this->ref_id);
        $this->main_tpl->addOnLoadCode("il.Awareness.setBaseUrl('" . $this->ctrl->getLinkTarget(
            $this,
            "",
            "",
            true,
            false
        ) . "');");
        $this->main_tpl->addOnLoadCode("il.Awareness.setLoaderSrc('" . ilUtil::getImagePath("loader.svg") . "');");
        $this->main_tpl->addOnLoadCode("il.Awareness.init();");

        // include user action js
        $ua_gui = ilUserActionGUI::getInstance(new ilAwarenessUserActionContext(), $GLOBALS["tpl"], $ilUser->getId());
        $ua_gui->init();
    }

    /**
     * Get awareness list (ajax)
     * @return ?array<string,string>
     * @throws ilWACException
     */
    public function getAwarenessList(bool $return = false) : ?array
    {
        $filter = $this->request->getFilter();

        $tpl = new ilTemplate("tpl.awareness_list.html", true, true, "Services/Awareness");

        $ad = $this->manager->getListData($filter);

        $users = $ad["data"];

        $ucnt = 0;
        $last_uc_title = "";
        foreach ($users as $u) {
            if ($u->collector != $last_uc_title) {
                if ($u->highlighted) {
                    $tpl->touchBlock("highlighted");
                }
                $tpl->setCurrentBlock("uc_title");
                $tpl->setVariable("UC_TITLE", $u->collector);
                $tpl->parseCurrentBlock();
                $tpl->setCurrentBlock("item");
                $tpl->parseCurrentBlock();
            }
            $last_uc_title = $u->collector;

            $ucnt++;

            $fcnt = 0;
            foreach ($u->actions as $act) {
                $fcnt++;
                if ($fcnt == 1) {
                    $tpl->touchBlock("arrow");
                    //$tpl->setCurrentBlock("arrow");
                    //$tpl->parseCurrentBlock();
                }
                if (is_array($act->data) && count($act->data) > 0) {
                    foreach ($act->data as $k => $v) {
                        $tpl->setCurrentBlock("f_data");
                        $tpl->setVariable("DATA_KEY", $k);
                        $tpl->setVariable("DATA_VAL", ilLegacyFormElementsUtil::prepareFormOutput($v));
                        $tpl->parseCurrentBlock();
                    }
                }
                $tpl->setCurrentBlock("feature");
                $tpl->setVariable("FEATURE_HREF", $act->href);
                $tpl->setVariable("FEATURE_TEXT", $act->text);
                $tpl->parseCurrentBlock();
            }

            if ($u->online) {
                $tpl->touchBlock("uonline");
                $tpl->setCurrentBlock("uonline_text");
                $tpl->setVariable("TXT_ONLINE", $this->lng->txt("awrn_online"));
                $tpl->parseCurrentBlock();
            }

            $tpl->setCurrentBlock("user");
            if ($u->public_profile) {
                $tpl->setVariable("UNAME", $u->lastname . ", " . $u->firstname);
            } else {
                $tpl->setVariable("UNAME", "-");
            }
            $tpl->setVariable("UACCOUNT", $u->login);

            $tpl->setVariable("USERIMAGE", $u->img);
            $tpl->setVariable("CNT", $ucnt);
            $tpl->parseCurrentBlock();
            $tpl->setCurrentBlock("item");
            $tpl->parseCurrentBlock();
        }

        $tpl->setCurrentBlock("filter");
        $tpl->setVariable("GL_FILTER", ilGlyphGUI::get(ilGlyphGUI::FILTER));
        $tpl->setVariable("FILTER_INPUT_LABEL", $this->lng->txt("awrn_filter"));
        $tpl->parseCurrentBlock();


        $result = ["html" => $tpl->get(),
                   "filter_val" => ilLegacyFormElementsUtil::prepareFormOutput($filter),
                   "cnt" => $ad["cnt"]];

        if ($return) {
            $this->initJS();
            return $result;
        }

        echo json_encode($result, JSON_THROW_ON_ERROR);
        exit;
    }
}
