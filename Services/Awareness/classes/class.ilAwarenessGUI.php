<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Awareness GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessGUI
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        global $DIC;
        $this->ui = $DIC->ui();

        $this->ref_id = (int) $_GET["ref_id"];
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule("awrn");
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        if (in_array($cmd, array("getAwarenessList"))) {
            $this->$cmd();
        }
    }


    /**
     * Get instance
     *
     * @return ilAwarenessGUI awareness gui object
     */
    public static function getInstance()
    {
        return new ilAwarenessGUI();
    }

    /**
     * Get main menu html
     */
    public function getMainMenuHTML()
    {
        $ilUser = $this->user;

        $awrn_set = new ilSetting("awrn");
        if (!$awrn_set->get("awrn_enabled", false) || ANONYMOUS_USER_ID == $ilUser->getId()) {
            return "";
        }

        $cache_period = (int) $awrn_set->get("caching_period");
        $last_update = ilSession::get("awrn_last_update");
        $now = time();

        // init js
        $GLOBALS["tpl"]->addJavascript("./Services/Awareness/js/Awareness.js");
        $this->ctrl->setParameter($this, "ref_id", $this->ref_id);
        $GLOBALS["tpl"]->addOnloadCode("il.Awareness.setBaseUrl('" . $this->ctrl->getLinkTarget(
            $this,
            "",
            "",
            true,
            false
        ) . "');");
        $GLOBALS["tpl"]->addOnloadCode("il.Awareness.setLoaderSrc('" . ilUtil::getImagePath("loader.svg") . "');");
        $GLOBALS["tpl"]->addOnloadCode("il.Awareness.init();");

        // include user action js
        include_once("./Services/User/Actions/classes/class.ilUserActionGUI.php");
        include_once("./Services/Awareness/classes/class.ilAwarenessUserActionContext.php");
        $ua_gui = ilUserActionGUI::getInstance(new ilAwarenessUserActionContext(), $GLOBALS["tpl"], $ilUser->getId());
        $ua_gui->init();

        $tpl = new ilTemplate("tpl.awareness.html", true, true, "Services/Awareness");

        include_once("./Services/Awareness/classes/class.ilAwarenessAct.php");
        $act = ilAwarenessAct::getInstance($ilUser->getId());
        $act->setRefId($this->ref_id);

        if ($last_update == "" || ($now - $last_update) >= $cache_period) {
            $cnt = explode(":", $act->getAwarenessUserCounter());
            $hcnt = $cnt[1];
            $cnt = $cnt[0];
            $act->notifyOnNewOnlineContacts();
            ilSession::set("awrn_last_update", $now);
            ilSession::set("awrn_nr_users", $cnt);
            ilSession::set("awrn_nr_husers", $hcnt);
        } else {
            $cnt = (int) ilSession::get("awrn_nr_users");
            $hcnt = (int) ilSession::get("awrn_nr_husers");
        }

        if ($hcnt > 0 || $cnt > 0) {
            /*
            $tpl->setCurrentBlock("status_text");
            $tpl->setVariable("STATUS_TXT", $cnt);
            if ($cnt == 0)
            {
                $tpl->setVariable("HIDDEN", "ilAwrnBadgeHidden");
            }
            $tpl->parseCurrentBlock();
            $tpl->setCurrentBlock("h_status_text");
            $tpl->setVariable("H_STATUS_TXT", $hcnt);
            if ($hcnt == 0)
            {
                $tpl->setVariable("H_HIDDEN", "ilAwrnBadgeHidden");
            }
            $tpl->parseCurrentBlock();
            $tpl->setVariable("HSP", "&nbsp;");*/

            $f = $this->ui->factory();
            $renderer = $this->ui->renderer();

            $glyph = $f->glyph()->user("#");
            if ($cnt > 0) {
                $glyph = $glyph->withCounter($f->counter()->status((int) $cnt));
            }
            if ($hcnt > 0) {
                $glyph = $glyph->withCounter($f->counter()->novelty((int) $hcnt));
            }
            $glyph_html = $renderer->render($glyph);
            $tpl->setVariable("GLYPH", $glyph_html);



            $tpl->setVariable("LOADER", ilUtil::getImagePath("loader.svg"));

            return $tpl->get();
        }

        return "";
    }
    
    /**
     * Get awareness list (ajax)
     */
    public function getAwarenessList()
    {
        $ilUser = $this->user;

        $filter = $_GET["filter"];

        $tpl = new ilTemplate("tpl.awareness_list.html", true, true, "Services/Awareness");

        include_once("./Services/Awareness/classes/class.ilAwarenessAct.php");
        $act = ilAwarenessAct::getInstance($ilUser->getId());
        $act->setRefId($this->ref_id);

        $ad = $act->getAwarenessData($filter);

        // update counter
        $now = time();
        $cnt = explode(":", $ad["cnt"]);
        $hcnt = $cnt[1];
        $cnt = $cnt[0];
        ilSession::set("awrn_last_update", $now);
        ilSession::set("awrn_nr_users", $cnt);
        ilSession::set("awrn_nr_husers", $hcnt);


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
                        $tpl->setVariable("DATA_VAL", ilUtil::prepareFormOutput($v));
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
                $tpl->setVariable("UNAME", "&nbsp;");
            }
            $tpl->setVariable("UACCOUNT", $u->login);

            $tpl->setVariable("USERIMAGE", $u->img);
            $tpl->setVariable("CNT", $ucnt);
            $tpl->parseCurrentBlock();
            $tpl->setCurrentBlock("item");
            $tpl->parseCurrentBlock();
        }

        include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
        $tpl->setCurrentBlock("filter");
        $tpl->setVariable("GL_FILTER", ilGlyphGUI::get(ilGlyphGUI::FILTER));
        $tpl->parseCurrentBlock();

        echo json_encode(array("html" => $tpl->get(),
            "filter_val" => ilUtil::prepareFormOutput($filter),
            "cnt" => $ad["cnt"]));
        exit;
    }
}
