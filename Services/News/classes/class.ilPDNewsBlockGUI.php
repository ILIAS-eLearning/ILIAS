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

use ILIAS\News\StandardGUIRequest;

/**
 * BlockGUI class for block NewsForContext
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_IsCalledBy ilPDNewsBlockGUI: ilColumnGUI
 */
class ilPDNewsBlockGUI extends ilNewsForContextBlockGUI
{
    public static string $block_type = "pdnews";
    protected bool $cache_hit = false;
    protected ilNewsCache $acache;
    protected bool $dynamic = false;
    protected bool $acc_results = false;
    protected StandardGUIRequest $std_request;

    public function __construct()
    {
        global $DIC;

        parent::__construct();

        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $this->obj_definition = $DIC["objDefinition"];

        // NOT ilNewsForContextBlockGUI::__construct() !
        ilBlockGUI::__construct();
        
        $lng->loadLanguageModule("news");
        $this->setLimit(5);

        $this->dynamic = false;

        $this->std_request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $this->acache = new ilNewsCache();
        $cres = unserialize($this->acache->getEntry($this->user->getId() . ":0"), ["allowed_classes" => false]);
        $this->cache_hit = false;
        if (is_array($cres) && $this->acache->getLastAccessStatus() === "hit") {
            self::$st_data = ilNewsItem::prepareNewsDataFromCache($cres);
            $this->cache_hit = true;
        }

        if (!$this->cache_hit && $this->getDynamic()) {
            $this->dynamic = true;
            $data = [];
        } else {
            // do not ask two times for the data (e.g. if user displays a
            // single item on the personal desktop and the news block is
            // displayed at the same time)
            if (empty(self::$st_data)) {
                self::$st_data = $this->getNewsData();
            }
            $data = self::$st_data;
        }

        $this->setTitle($lng->txt("news_internal_news"));
        $this->setRowTemplate("tpl.block_row_news_for_context.html", "Services/News");
        
        $this->setData($data);
        
        $this->handleView();
        
        // reset access check results
        $ilAccess->setResults((array) $this->acc_results);

        $this->setPresentation(self::PRES_SEC_LIST);
    }
    
    public function getNewsData() : array
    {
        $ilUser = $this->user;

        $this->acache = new ilNewsCache();
        
        $per = ilNewsItem::_lookupUserPDPeriod($ilUser->getId());
        $data = ilNewsItem::_getNewsItemsOfUser(
            $ilUser->getId(),
            false,
            false,
            $per
        );

        $this->acache->storeEntry(
            $ilUser->getId() . ":0",
            serialize($data)
        );

        return $data;
    }

    public function getBlockType() : string
    {
        return self::$block_type;
    }

    public static function getScreenMode() : string
    {
        global $DIC;

        $cmd = $DIC->ctrl()->getCmd();

        switch ($cmd) {
            case "showNews":
            case "showFeedUrl":
            case "editSettings":
            case "changeFeedSettings":
                return IL_SCREEN_CENTER;
            
            default:
                return IL_SCREEN_SIDE;
        }
    }

    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass();
        $cmd = $ilCtrl->getCmd("getHTML");

        switch ($next_class) {
            default:
                return $this->$cmd();
        }
    }

    public function fillDataSection() : void
    {
        if ($this->dynamic) {
            $this->setDataSection($this->getDynamicReload());
        } elseif (count($this->getData()) > 0) {
            parent::fillDataSection();
        } else {
            $this->setEnableNumInfo(false);
            $this->setDataSection($this->getOverview());
        }
    }

    public function getHTML() : string
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilUser = $this->user;

        // @todo: find another solution for this
        //$this->setFooterInfo($lng->txt("news_block_information"), true);
        
        $news_set = new ilSetting("news");
        $enable_internal_rss = $news_set->get("enable_rss_for_internal");
        $allow_shorter_periods = $news_set->get("allow_shorter_periods");
        $allow_longer_periods = $news_set->get("allow_longer_periods");
        $enable_private_feed = $news_set->get("enable_private_feed");
        // show feed url
        if ($enable_internal_rss) {
            // @todo: rss icon html ilRSSButtonGUI::get(ilRSSButtonGUI::ICON_RSS)
            $this->addBlockCommand(
                $ilCtrl->getLinkTarget($this, "showFeedUrl"),
                $lng->txt("news_get_feed_url")
            );
        }

        if ($allow_shorter_periods || $allow_longer_periods || $enable_private_feed) {
            $this->addBlockCommand(
                $ilCtrl->getLinkTarget($this, "editSettings"),
                $lng->txt("settings")
            );
        }

        $en = "";
        if ($ilUser->getPref("il_feed_js") === "n") {
            $en = $this->getJSEnabler();
        }

        return ilBlockGUI::getHTML() . $en;
    }
    

    public function showFeedUrl() : string
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $news_set = new ilSetting("news");
        
        if ($news_set->get("enable_private_feed")) {
            $tpl = new ilTemplate("tpl.show_priv_feed_url.html", true, true, "Services/News");

            $tpl->setVariable("TXT_PRIV_TITLE", $lng->txt("news_get_priv_feed_title"));
            
            // #14365
            if (ilObjUser::_getFeedPass($GLOBALS['DIC']['ilUser']->getId())) {
                $tpl->setVariable("TXT_PRIV_INFO", $lng->txt("news_get_priv_feed_info"));
                $tpl->setVariable("TXT_PRIV_FEED_URL", $lng->txt("news_feed_url"));
                $tpl->setVariable(
                    "VAL_PRIV_FEED_URL",
                    str_replace("://", "://" . $ilUser->getLogin() . ":-password-@", ILIAS_HTTP_PATH) . "/privfeed.php?client_id=" . rawurlencode(CLIENT_ID) . "&user_id=" . $ilUser->getId() .
                        "&hash=" . ilObjUser::_lookupFeedHash($ilUser->getId(), true)
                );
                $tpl->setVariable(
                    "VAL_PRIV_FEED_URL_TXT",
                    str_replace("://", "://" . $ilUser->getLogin() . ":-password-@", ILIAS_HTTP_PATH) . "/privfeed.php?client_id=" . rawurlencode(CLIENT_ID) . "&<br />user_id=" . $ilUser->getId() .
                        "&hash=" . ilObjUser::_lookupFeedHash($ilUser->getId(), true)
                );
            } else {
                $tpl->setVariable("TXT_PRIV_INFO", $lng->txt("news_inactive_private_feed_info"));
                $tpl->setVariable("EDIT_SETTINGS_URL", $ilCtrl->getLinkTarget($this, "editSettings"));
                $tpl->setVariable("EDIT_SETTINGS_TXT", $lng->txt("news_edit_news_settings"));
            }
        } else {
            $tpl = new ilTemplate("tpl.show_feed_url.html", true, true, "Services/News");
        }
        $tpl->setVariable("TXT_TITLE", $lng->txt("news_get_feed_title"));
        $tpl->setVariable("TXT_INFO", $lng->txt("news_get_feed_info"));
        $tpl->setVariable("TXT_FEED_URL", $lng->txt("news_feed_url"));
        $tpl->setVariable(
            "VAL_FEED_URL",
            ILIAS_HTTP_PATH . "/feed.php?client_id=" . rawurlencode(CLIENT_ID) . "&user_id=" . $ilUser->getId() .
                "&hash=" . ilObjUser::_lookupFeedHash($ilUser->getId(), true)
        );
        $tpl->setVariable(
            "VAL_FEED_URL_TXT",
            ILIAS_HTTP_PATH . "/feed.php?client_id=" . rawurlencode(CLIENT_ID) . "&<br />user_id=" . $ilUser->getId() .
                "&hash=" . ilObjUser::_lookupFeedHash($ilUser->getId(), true)
        );
        
        $content_block = new ilDashboardContentBlockGUI();
        $content_block->setContent($tpl->get());
        $content_block->setTitle($lng->txt("news_internal_news"));

        return $content_block->getHTML();
    }

    public function editSettings(ilPropertyFormGUI $a_private_form = null) : string
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $returnForm = "";

        $news_set = new ilSetting("news");
        $allow_shorter_periods = $news_set->get("allow_shorter_periods");
        $allow_longer_periods = $news_set->get("allow_longer_periods");
        $enable_private_feed = $news_set->get("enable_private_feed");
    
        if (!$a_private_form && ($allow_shorter_periods || $allow_longer_periods)) {
            $form = new ilPropertyFormGUI();
            $form->setFormAction($ilCtrl->getFormAction($this));
            $form->setTitle($lng->txt("news_settings"));

            $default_per = ilNewsItem::_lookupDefaultPDPeriod();
            $per = ilNewsItem::_lookupUserPDPeriod($ilUser->getId());

            $form->setTableWidth("100%");

            $per_opts = [
                2 => "2 " . $lng->txt("days"),
                3 => "3 " . $lng->txt("days"),
                5 => "5 " . $lng->txt("days"),
                7 => "1 " . $lng->txt("week"),
                14 => "2 " . $lng->txt("weeks"),
                30 => "1 " . $lng->txt("month"),
                60 => "2 " . $lng->txt("months"),
                120 => "4 " . $lng->txt("months"),
                180 => "6 " . $lng->txt("months"),
                366 => "1 " . $lng->txt("year")
            ];

            $unset = [];
            foreach ($per_opts as $k => $opt) {
                if (!$allow_shorter_periods && ($k < $default_per)) {
                    $unset[$k] = $k;
                }
                if (!$allow_longer_periods && ($k > $default_per)) {
                    $unset[$k] = $k;
                }
            }
            foreach ($unset as $k) {
                unset($per_opts[$k]);
            }

            $per_sel = new ilSelectInputGUI(
                $lng->txt("news_pd_period"),
                "news_pd_period"
            );
            $per_sel->setOptions($per_opts);
            $per_sel->setValue((string) $per);
            $form->addItem($per_sel);
        
            $form->addCommandButton("saveSettings", $lng->txt("save"));
            $form->addCommandButton("cancelSettings", $lng->txt("cancel"));
            
            $returnForm = $form->getHTML();
        }

        if ($enable_private_feed) {
            if (!$a_private_form) {
                $a_private_form = $this->initPrivateSettingsForm();
            }
            $returnForm .= ($returnForm === "")
                ? $a_private_form->getHTML()
                : "<br>" . $a_private_form->getHTML();
        }
        
        return $returnForm;
    }
    
    protected function initPrivateSettingsForm() : ilPropertyFormGUI
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilUser = $this->user;
        
        $feed_form = new ilPropertyFormGUI();
        $feed_form->setFormAction($ilCtrl->getFormAction($this));
        $feed_form->setTitle($lng->txt("priv_feed_settings"));

        $feed_form->setTableWidth("100%");

        $enable_private_feed = new ilCheckboxInputGUI($lng->txt("news_enable_private_feed"), "enable_private_feed");
        $enable_private_feed->setChecked((bool) ilObjUser::_getFeedPass($ilUser->getId()));
        $feed_form->addItem($enable_private_feed);

        $passwd = new ilPasswordInputGUI($lng->txt("password"), "desired_password");
        $passwd->setRequired(true);
        $passwd->setInfo(ilSecuritySettingsChecker::getPasswordRequirementsInfo());
        $enable_private_feed->addSubItem($passwd);

        $feed_form->addCommandButton("changeFeedSettings", $lng->txt("save"));
        $feed_form->addCommandButton("cancelSettings", $lng->txt("cancel"));
        
        return $feed_form;
    }

    public function saveSettings() : string
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        
        $news_set = new ilSetting("news");

        ilBlockSetting::_write(
            $this->getBlockType(),
            "news_pd_period",
            $this->std_request->getDashboardPeriod(),
            $ilUser->getId(),
            (int) $this->block_id
        );
        
        $cache = new ilNewsCache();
        $cache->deleteEntry($ilUser->getId() . ":0");
            
        $ilCtrl->returnToParent($this);
        return "";
    }

    public function changeFeedSettings() : string
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilUser = $this->user;
        
        $form = $this->initPrivateSettingsForm();
        if ($form->checkInput()) {
            // Deactivate private Feed - just delete the password
            if (!$form->getInput("enable_private_feed")) {
                ilObjUser::_setFeedPass($ilUser->getId(), "");
                $this->main_tpl->setOnScreenMessage('success', $lng->txt("priv_feed_disabled"), true);
                // $ilCtrl->returnToParent($this);
                $ilCtrl->redirect($this, "showFeedUrl");
            } else {
                $passwd = $form->getInput("desired_password");
                if (ilUserPasswordManager::getInstance()->verifyPassword($ilUser, $passwd)) {
                    $form->getItemByPostVar("desired_password")->setAlert($lng->txt("passwd_equals_ilpasswd"));
                    $this->main_tpl->setOnScreenMessage('failure', $lng->txt("form_input_not_valid"));
                } else {
                    ilObjUser::_setFeedPass($ilUser->getId(), $passwd);
                    $this->main_tpl->setOnScreenMessage('success', $lng->txt("saved_successfully"), true);
                    $ilCtrl->redirect($this, "showFeedUrl");
                }
            }
        }
        
        $form->setValuesByPost();
        return $this->editSettings($form);
    }
}
