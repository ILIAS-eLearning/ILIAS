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
 * News on PD
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilPDNewsGUI:
 */
class ilPDNewsGUI
{
    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilHelpGUI $help;
    protected ilObjUser $user;
    protected ilFavouritesManager $fav_manager;
    protected StandardGUIRequest $std_request;

    public function __construct()
    {
        global $DIC;

        $this->help = $DIC["ilHelp"];
        $this->user = $DIC->user();
        $tpl = $DIC->ui()->mainTemplate();
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $ilHelp = $DIC["ilHelp"];

        $ilHelp->setScreenIdComponent("news");

        $this->std_request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        // initiate variables
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;

        $lng->loadLanguageModule("news");

        $this->ctrl->saveParameter($this, "news_ref_id");
        $this->fav_manager = new ilFavouritesManager();
    }

    public function executeCommand(): bool
    {
        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            default:
                $cmd = $this->ctrl->getCmd("view");
                $this->displayHeader();
                $this->$cmd();
                break;
        }
        $this->tpl->printToStdout();
        return true;
    }

    public function displayHeader(): void
    {
        $this->tpl->setTitle($this->lng->txt("news"));
    }

    public function view(): void
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $tpl = $this->tpl;

        $ref_ids = [];
        $obj_ids = [];
        $pd_items = $this->fav_manager->getFavouritesOfUser($ilUser->getId());
        foreach ($pd_items as $item) {
            $ref_ids[] = (int) $item["ref_id"];
            $obj_ids[] = (int) $item["obj_id"];
        }

        $sel_ref_id = ($this->std_request->getNewsRefId() > 0)
            ? $this->std_request->getNewsRefId()
            : $ilUser->getPref("news_sel_ref_id");

        $per = (ilSession::get("news_pd_news_per") != "")
            ? ilSession::get("news_pd_news_per")
            : ilNewsItem::_lookupUserPDPeriod($ilUser->getId());
        $news_obj_ids = ilNewsItem::filterObjIdsPerNews($obj_ids, $per);

        // related objects (contexts) of news
        $contexts[0] = $lng->txt("news_all_items");

        $conts = [];
        $sel_has_news = false;
        foreach ($ref_ids as $ref_id) {
            $obj_id = ilObject::_lookupObjId($ref_id);
            $title = ilObject::_lookupTitle($obj_id);

            $conts[$ref_id] = $title;
            if ((int) $sel_ref_id === $ref_id) {
                $sel_has_news = true;
            }
        }

        $cnt = [];
        $nitem = new ilNewsItem();
        $news_items = ilNewsItem::_getNewsItemsOfUser(
            $ilUser->getId(),
            false,
            true,
            $per,
            $cnt
        );

        // reset selected news ref id, if no news are given for id
        if (!$sel_has_news) {
            $sel_ref_id = "";
        }
        asort($conts);
        foreach ($conts as $ref_id => $title) {
            $contexts[$ref_id] = $title . " (" . (int) $cnt[$ref_id] . ")";
        }


        if ($sel_ref_id > 0) {
            $obj_id = ilObject::_lookupObjId((int) $sel_ref_id);
            $obj_type = ilObject::_lookupType($obj_id);
            $nitem->setContextObjId($obj_id);
            $nitem->setContextObjType($obj_type);
            $news_items = $nitem->getNewsForRefId(
                $sel_ref_id,
                false,
                false,
                $per,
                true
            );
        }

        $pd_news_table = new ilPDNewsTableGUI($this, "view", $contexts, $sel_ref_id);
        $pd_news_table->setData($news_items);
        $pd_news_table->setNoEntriesText($lng->txt("news_no_news_items"));

        $tpl->setContent($pd_news_table->getHTML());
    }

    public function applyFilter(): void
    {
        $ilUser = $this->user;

        $news_ref_id = $this->std_request->getNewsRefId();
        $news_per = $this->std_request->getNewsPer();

        $this->ctrl->setParameter($this, "news_ref_id", $news_ref_id);
        $ilUser->writePref("news_sel_ref_id", (string) $news_ref_id);
        if ($news_per > 0) {
            ilSession::set("news_pd_news_per", $news_per);
        }
        $this->ctrl->redirect($this, "view");
    }

    public function resetFilter(): void
    {
        $ilUser = $this->user;
        $this->ctrl->setParameter($this, "news_ref_id", 0);
        $ilUser->writePref("news_sel_ref_id", '0');
        ilSession::clear("news_pd_news_per");
        $this->ctrl->redirect($this, "view");
    }
}
