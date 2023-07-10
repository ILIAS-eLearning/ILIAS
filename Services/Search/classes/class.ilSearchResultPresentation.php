<?php

declare(strict_types=1);

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* Presentation of search results using object list gui
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
* @ingroup ServicesSearch
*/
class ilSearchResultPresentation
{
    public const MODE_LUCENE = 1;
    public const MODE_STANDARD = 2;

    protected int $mode;

    protected ilGlobalTemplateInterface $tpl;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilAccess $access;
    protected ilTree $tree;
    protected GlobalHttpState $http;
    protected Factory $refinery;


    private array $results = [];
    private array $subitem_ids = [];
    private array $has_more_ref_ids = [];
    private array $all_references = [];
    private ?ilLuceneSearcher $searcher = null;
    private ?object $container = null;

    protected string $prev;
    protected string $next;
    protected string $html;
    protected string $thtml;

    /**
     * Constructor
     * @param object	$container container gui object
     */
    public function __construct(object $container = null, int $a_mode = self::MODE_LUCENE)
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->mode = $a_mode;
        $this->container = $container;

        $this->initReferences();

        if ($this->http->wrapper()->query()->has('details')) {
            ilSubItemListGUI::setShowDetails(
                $this->http->wrapper()->query()->retrieve(
                    'details',
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
    }

    /**
     * Get container gui
     */
    public function getContainer(): object
    {
        return $this->container;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    /**
     * Set result array
     */
    public function setResults(array $a_result_data): void
    {
        $this->results = $a_result_data;
    }

    /**
     * get results
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Set subitem ids
     * Used for like and fulltext search
     * @param array $a_subids array ($obj_id => array(page1_id,page2_id);
     * @return void
     */
    public function setSubitemIds(array $a_subids): void
    {
        $this->subitem_ids = $a_subids;
    }

    /**
     * Get subitem ids
     * @return array
     */
    public function getSubitemIds(): array
    {
        return $this->subitem_ids;
    }

    /**
     * Get subitem ids for an object
     */
    public function getSubitemIdsByObject(int $a_obj_id): array
    {
        return (isset($this->subitem_ids[$a_obj_id]) && $this->subitem_ids[$a_obj_id]) ?
            $this->subitem_ids[$a_obj_id] :
            array();
    }



    /**
     * Check if more than one reference is visible
     */
    protected function parseResultReferences(): void
    {
        foreach ($this->getResults() as $ref_id => $obj_id) {
            $this->all_references[$ref_id][] = $ref_id;
            $counter = 0;
            foreach (ilObject::_getAllReferences((int) $obj_id) as $new_ref) {
                if ($new_ref == $ref_id) {
                    continue;
                }
                if (!$this->access->checkAccess('read', '', $new_ref)) {
                    continue;
                }
                $this->all_references[$ref_id][] = $new_ref;
                ++$counter;
            }
            $this->has_more_ref_ids[$ref_id] = $counter;
        }
    }

    protected function hasMoreReferences(int $a_ref_id): bool
    {
        $references = ilSession::get('vis_references') ?? [];
        if (!isset($this->has_more_ref_ids[$a_ref_id]) or
            !$this->has_more_ref_ids[$a_ref_id] or
            array_key_exists($a_ref_id, $references)) {
            return false;
        }
        return $this->has_more_ref_ids[$a_ref_id];
    }

    protected function getAllReferences(int $a_ref_id): array
    {
        $references = ilSession::get('vis_references') ?? [];
        if (array_key_exists($a_ref_id, $references)) {
            return $this->all_references[$a_ref_id] ?: array();
        } else {
            return array($a_ref_id);
        }
    }

    /**
     * Get HTML
     * @return string HTML
     */
    public function getHTML(): string
    {
        return $this->thtml;
    }

    /**
     * set searcher
     */
    public function setSearcher(ilLuceneSearcher $a_searcher): void
    {
        $this->searcher = $a_searcher;
    }

    /**
     * Parse results
     */
    public function render(): bool
    {
        return $this->renderItemList();
    }

    /**
    * Set previous next
    */
    public function setPreviousNext(string $a_p, string $a_n): void
    {
        $this->prev = $a_p;
        $this->next = $a_n;
    }


    /**
     * Render item list
     */
    protected function renderItemList(): bool
    {
        $this->html = '';

        $this->parseResultReferences();

        $this->lng->loadLanguageModule("cntr"); // #16834

        $preloader = new ilObjectListGUIPreloader(ilObjectListGUI::CONTEXT_SEARCH);

        $set = array();
        foreach ($this->getResults() as $c_ref_id => $obj_id) {
            $c_ref_id = (int) $c_ref_id;
            $obj_id = (int) $obj_id;
            foreach ($this->getAllReferences($c_ref_id) as $ref_id) {
                if (!$this->tree->isInTree($ref_id)) {
                    continue;
                }

                $obj_type = ilObject::_lookupType($obj_id);

                $set[] = array(
                    "ref_id" => $ref_id,
                    "obj_id" => $obj_id,
                    "title" => $this->lookupTitle($obj_id, 0),
                    "title_sort" => ilObject::_lookupTitle((int) $obj_id),
                    "description" => $this->lookupDescription((int) $obj_id, 0),
                    "type" => $obj_type,
                    "relevance" => $this->getRelevance($obj_id),
                    "s_relevance" => sprintf("%03d", $this->getRelevance($obj_id)),
                    'create_date' => ilObject::_lookupCreationDate($obj_id)
                );

                $preloader->addItem($obj_id, $obj_type, $ref_id);
            }
        }

        if (!count($set)) {
            return false;
        }

        $preloader->preload();
        unset($preloader);

        $result_table = new ilSearchResultTableGUI($this->container, "showSavedResults", $this);
        $result_table->setCustomPreviousNext($this->prev, $this->next);

        $result_table->setData($set);
        $this->thtml = $result_table->getHTML();
        return true;
    }


    public function getRelevance(int $a_obj_id): float
    {
        if ($this->getMode() == self::MODE_LUCENE) {
            return $this->searcher->getResult()->getRelevance($a_obj_id);
        }
        return 0;
    }

    public function lookupTitle(int $a_obj_id, int $a_sub_id): string
    {
        if ($this->getMode() != self::MODE_LUCENE or !is_object($this->searcher->getHighlighter())) {
            return ilObject::_lookupTitle($a_obj_id);
        }
        if (strlen($title = $this->searcher->getHighlighter()->getTitle($a_obj_id, $a_sub_id))) {
            return $title;
        }
        return ilObject::_lookupTitle($a_obj_id);
    }

    public function lookupDescription(int $a_obj_id, int $a_sub_id): string
    {
        if ($this->getMode() != self::MODE_LUCENE or !is_object($this->searcher->getHighlighter())) {
            return ilObject::_lookupDescription($a_obj_id);
        }
        if (strlen($title = $this->searcher->getHighlighter()->getDescription($a_obj_id, $a_sub_id))) {
            return $title;
        }
        return ilObject::_lookupDescription($a_obj_id);
    }

    public function lookupContent(int $a_obj_id, int $a_sub_id): string
    {
        if ($this->getMode() != self::MODE_LUCENE or !is_object($this->searcher->getHighlighter())) {
            return '';
        }
        return $this->searcher->getHighlighter()->getContent($a_obj_id, $a_sub_id);
    }

    /**
     * Append path, relevance information
     */
    public function appendAdditionalInformation(
        ilObjectListGUI $item_list_gui,
        int $ref_id,
        int $obj_id,
        string $type
    ): void {
        $sub = $this->appendSubItems($item_list_gui, $ref_id, $obj_id, $type);
        $path = $this->appendPath($ref_id);
        $more = $this->appendMorePathes($ref_id);

        if (!strlen($sub) and
            !strlen($path) and
            !strlen($more)) {
            return;
        }
        $tpl = new ilTemplate('tpl.lucene_additional_information.html', true, true, 'Services/Search');
        $tpl->setVariable('SUBITEM', $sub);
        if (strlen($path)) {
            $tpl->setVariable('PATH', $path);
        }
        if (strlen($more)) {
            $tpl->setVariable('MORE_PATH', $more);
        }

        $item_list_gui->setAdditionalInformation($tpl->get());
    }


    protected function appendPath(int $a_ref_id): string
    {
        $path_gui = new ilPathGUI();
        $path_gui->enableTextOnly(false);
        $path_gui->setUseImages(false);

        $tpl = new ilTemplate('tpl.lucene_path.html', true, true, 'Services/Search');
        $tpl->setVariable('PATH_ITEM', $path_gui->getPath(ROOT_FOLDER_ID, $a_ref_id));
        return $tpl->get();
    }

    protected function appendMorePathes(int $a_ref_id): string
    {
        if ($this->getMode() != self::MODE_LUCENE) {
            return '';
        }


        if (!$num_refs = $this->hasMoreReferences($a_ref_id)) {
            return '';
        }
        $tpl = new ilTemplate('tpl.lucene_more_references.html', true, true, 'Services/Search');
        $this->ctrl->setParameter($this->getContainer(), 'refs', $a_ref_id);
        $tpl->setVariable('MORE_REFS_LINK', $this->ctrl->getLinkTarget($this->getContainer(), ''));
        $this->ctrl->clearParameters($this->getContainer());

        $tpl->setVariable('TXT_MORE_REFS', sprintf($this->lng->txt('lucene_all_occurrences'), $num_refs));
        return $tpl->get();
    }


    protected function appendSubItems(
        ilObjectListGUI $item_list_gui,
        int $ref_id,
        int $obj_id,
        string $a_type
    ): string {
        $subitem_ids = array();
        $highlighter = null;
        if ($this->getMode() == self::MODE_STANDARD) {
            $subitem_ids = $this->getSubitemIdsByObject($obj_id);
        } elseif (is_object($this->searcher->getHighlighter())) {
            $subitem_ids = $this->searcher->getHighlighter()->getSubitemIds($obj_id);
            $highlighter = $this->searcher->getHighlighter();
        }

        if (!count($subitem_ids)) {
            return '';
        }

        // Build subitem list
        $sub_list = ilLuceneSubItemListGUIFactory::getInstanceByType($a_type, $this->getContainer());
        $sub_list->setHighlighter($highlighter);
        $sub_list->init($item_list_gui, $ref_id, $subitem_ids);
        return $sub_list->getHTML();
    }

    protected function initReferences(): void
    {
        $session_references = ilSession::get('vis_references') ?? [];
        if ($this->http->wrapper()->post()->has('refs')) {
            $refs = $this->http->wrapper()->post()->retrieve(
                'refs',
                $this->refinery->kindlyTo()->int()
            );
            $session_references[$refs] = $refs;
            ilSession::set('vis_references', $session_references);
        }
    }
}
