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

declare(strict_types=1);

use ILIAS\User\Profile\PublicProfileGUI;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

/**
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @ilCtrl_Calls ilLuceneUserSearchGUI: ILIAS\User\Profile\PublicProfileGUI
 * @ilCtrl_IsCalledBy ilLuceneUserSearchGUI: ilSearchControllerGUI
 */
class ilLuceneUserSearchGUI
{
    protected ilUserSearchCache $search_cache;

    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ilObjUser $user;
    protected GlobalHttpState $http;
    protected Refinery $refinery;
    protected ilTabsGUI $tabs;
    protected ilHelpGUI $help;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->tabs = $DIC->tabs();
        $this->help = $DIC->help();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('search');
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->initUserSearchCache();
    }

    /**
     * Execute Command
     */
    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();
        switch ($next_class) {
            case strtolower(PublicProfileGUI::class):

                $user_id = 0;
                if ($this->http->wrapper()->query()->has('user_id')) {
                    $user_id = $this->http->wrapper()->query()->retrieve(
                        'user_id',
                        $this->refinery->kindlyTo()->int()
                    );
                }
                $profile = new PublicProfileGUI($user_id);
                $profile->setBackUrl($this->ctrl->getLinkTarget($this, 'showSavedResults'));
                $ret = $this->ctrl->forwardCommand($profile);
                $this->tpl->setContent($ret);
                break;


            default:
                if (!$cmd) {
                    $cmd = "showSavedResults";
                }
                $this->$cmd();
                break;
        }
    }

    protected function prepareOutput(): void
    {
        $this->tpl->loadStandardTemplate();

        $this->tpl->setTitleIcon(
            ilObject::_getIcon(0, "big", "src"),
            ""
        );
        $this->tpl->setTitle($this->lng->txt("search"));

        $this->getTabs();
    }

    protected function getTabs(): void
    {
        $this->help->setScreenIdComponent('src_luc');

        $this->tabs->addTarget('search', $this->ctrl->getLinkTargetByClass(ilSearchGUI::class));

        if (ilSearchSettings::getInstance()->isLuceneUserSearchEnabled()) {
            $this->tabs->addTarget('search_user', $this->ctrl->getLinkTargetByClass(ilLuceneUserSearchGUI::class));
        }

        $this->tabs->setTabActive('search_user');
    }


    /**
     * Search from main menu
     */
    protected function remoteSearch(): void
    {
        $root_id = 0;
        if ($this->http->wrapper()->post()->has('root_id')) {
            $root_id = $this->http->wrapper()->post()->retrieve(
                'root_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $queryString = '';
        if ($this->http->wrapper()->post()->has('queryString')) {
            $queryString = $this->http->wrapper()->post()->retrieve(
                'queryString',
                $this->refinery->kindlyTo()->string()
            );
        }
        $this->search_cache->setRoot($root_id);
        $this->search_cache->setQuery($queryString);
        $this->search_cache->save();
        $this->search();
    }

    protected function showSavedResults(): void
    {
        if (strlen($this->search_cache->getQuery())) {
            $this->performSearch();
            return;
        }

        $this->showSearchForm();
    }

    /**
     * Search (button pressed)
     */
    protected function search(): void
    {
        $this->search_cache->deleteCachedEntries();
        $this->performSearch();
    }

    /**
     * Perform search
     */
    protected function performSearch(): void
    {
        $qp = new ilLuceneQueryParser($this->search_cache->getQuery());
        $qp->parse();
        $searcher = ilLuceneSearcher::getInstance($qp);
        $searcher->setType(ilLuceneSearcher::TYPE_USER);
        $searcher->search();

        $this->showSearchForm();

        $user_table = new ilRepositoryUserResultTableGUI(
            $this,
            'performSearch',
            false,
            ilRepositoryUserResultTableGUI::TYPE_GLOBAL_SEARCH
        );
        $user_table->setLuceneResult($searcher->getResult());
        $user_table->parseUserIds($searcher->getResult()->getCandidates());

        $this->tpl->setVariable('SEARCH_RESULTS', $user_table->getHTML());
    }

    protected function initUserSearchCache(): void
    {
        $this->search_cache = ilUserSearchCache::_getInstance($this->user->getId());
        $this->search_cache->switchSearchType(ilUserSearchCache::LUCENE_USER_SEARCH);

        if ($this->http->wrapper()->post()->has('term')) {
            $query = $this->http->wrapper()->post()->retrieve(
                'term',
                $this->refinery->kindlyTo()->string()
            );
            $this->search_cache->setQuery($query);
            $this->search_cache->setItemFilter([]);
            $this->search_cache->setMimeFilter([]);
            $this->search_cache->save();
        }
    }

    protected function showSearchForm()
    {
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.lucene_usr_search.html', 'components/ILIAS/Search');
        $this->tpl->addJavascript("assets/js/Search.js");

        $this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this, "performSearch"));
        $this->tpl->setVariable("TERM", ilLegacyFormElementsUtil::prepareFormOutput($this->search_cache->getQuery()));
        $this->tpl->setVariable("SEARCH_LABEL", $this->lng->txt("search"));
        $btn = ilSubmitButton::getInstance();
        $btn->setCommand("performSearch");
        $btn->setCaption("search");
        $this->tpl->setVariable("SUBMIT_BTN", $btn->render());
    }
}
