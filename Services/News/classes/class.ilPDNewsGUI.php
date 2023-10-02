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
use ILIAS\Repository\Filter\FilterAdapterGUI;

/**
 * News on PD
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilPDNewsGUI: ilNewsTimelineGUI, ilCommonActionDispatcherGUI
 */
class ilPDNewsGUI
{
    protected \ILIAS\News\Dashboard\DashboardNewsManager $dash_news_manager;
    protected \ILIAS\News\Dashboard\DashboardSessionRepository $dash_news_repo;
    protected \ILIAS\News\InternalGUIService $gui;
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

        $this->std_request = $DIC->news()
            ->internal()
            ->gui()
            ->standardRequest();

        // initiate variables
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;

        $lng->loadLanguageModule("news");

        $this->ctrl->saveParameter($this, "news_ref_id");
        $this->fav_manager = new ilFavouritesManager();
        $this->gui = $DIC->news()
            ->internal()
            ->gui();
        $this->dash_news_repo = $DIC->news()
            ->internal()
            ->repo()
            ->dashboard();
        $this->dash_news_manager = $DIC->news()
            ->internal()
            ->domain()
            ->dashboard();
    }

    public function executeCommand(): bool
    {
        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            case "ilnewstimelinegui":
                $t = $this->gui->dashboard()->getTimelineGUI();
                $this->ctrl->forwardCommand($t);
                break;

            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

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
        $this->tpl->setTitleIcon(ilUtil::getImagePath("standard/icon_nwss.svg"));
    }

    public function view(): void
    {
        $filter = $this->gui->dashboard()->getFilter();
        $this->saveFilterValues($filter);
        // we need to get the filte again, since the saving above influences
        // the drop down (number of news per object displayed in parentheses dependeds on period)
        $filter = $this->gui->dashboard()->getFilter(true);
        $t = $this->gui->dashboard()->getTimelineGUI();

        $this->tpl->setContent(
            $filter->render() .
            $this->ctrl->getHTML($t)
        );
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

    protected function saveFilterValues(FilterAdapterGUI $filter): void
    {
        $this->dash_news_manager->saveFilterData($filter->getData());
    }
}
