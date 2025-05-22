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

use ILIAS\DI\UIServices;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\Refinery\Factory as Refinery;

/**
* TableGUI class for search results
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @ingroup ServicesSearch
*/
class ilSearchResultTableGUI extends ilTable2GUI
{
    use ilSearchResultTableHelper;

    protected ilObjUser $user;
    protected ilSearchResultPresentation $presenter;
    protected ilObjectDefinition $objDefinition;
    protected UIServices $ui;
    protected HTTP $http;
    protected Refinery $refinery;

    /**
    * Constructor
    */
    public function __construct($a_parent_obj, $a_parent_cmd, ilSearchResultPresentation $a_presenter)
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->presenter = $a_presenter;
        $this->objDefinition = $DIC['objDefinition'];
        $this->ui = $DIC->ui();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->setId("ilSearchResultsTable");

        $this->setId('search_' . $this->user->getId());
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setTitle($this->lng->txt("search_results"));
        $this->setLimit(999);
        $this->addColumn($this->lng->txt("type"), "", "1");
        $this->addColumn($this->lng->txt("search_title_description"), "");

        $all_cols = $this->getSelectableColumns();
        foreach ($this->getSelectedColumns() as $col) {
            $this->addColumn($all_cols[$col]['txt'], "", '50px');
        }


        $this->addColumn($this->lng->txt("actions"), "", "10px");

        $this->setEnableHeader(true);
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.search_result_row.html", "components/ILIAS/Search");
        $this->setEnableTitle(true);
        $this->setEnableNumInfo(false);
        $this->setShowRowsSelector(false);
        $this->setExternalSorting(true);
    }

    /**
     * Get selectable columns
     * @return array
     */
    public function getSelectableColumns(): array
    {
        return array('create_date' =>
                        array(
                            'txt' => $this->lng->txt('create_date'),
                            'default' => false
            )
        );
    }


    /**
    * Fill table row
    */
    protected function fillRow(array $a_set): void
    {
        $obj_id = $a_set["obj_id"];
        $ref_id = $a_set["ref_id"];
        $type = $a_set['type'];
        $title = $a_set['title'];
        $description = $a_set['description'];

        if (!$type || $this->objDefinition->isSideBlock($type)) {
            return;
        }

        $item_list_gui = ilLuceneSearchObjectListGUIFactory::factory($type);
        $item_list_gui->initItem($ref_id, $obj_id, $type, $title, $description);
        $item_list_gui->setContainerObject($this->parent_obj);
        $item_list_gui->setSearchFragment($this->presenter->lookupContent($obj_id, 0));
        $item_list_gui->setSeparateCommands(true);

        ilObjectActivation::addListGUIActivationProperty($item_list_gui, $a_set);

        $this->presenter->appendAdditionalInformation($item_list_gui, $ref_id, $obj_id, $type);

        $this->tpl->setVariable("ACTION_HTML", $item_list_gui->getCommandsHTML($title));

        if ($html = $item_list_gui->getListItemHTML($ref_id, $obj_id, '#SRC_HIGHLIGHT_TITLE', '#SRC_HIGHLIGHT_DESC')) {
            // replace highlighted title/description
            $html = str_replace(
                [
                    '#SRC_HIGHLIGHT_TITLE',
                    '#SRC_HIGHLIGHT_DESC'
                ],
                [
                    $title,
                    strip_tags(
                        $description,
                        ['span']
                    )
                ],
                $html
            );
            $item_html[$ref_id]['html'] = $html;
            $item_html[$ref_id]['type'] = $type;
        }

        $this->tpl->setVariable("ITEM_HTML", $html);

        foreach ($this->getSelectedColumns() as $field) {
            switch ($field) {
                case 'create_date':
                    $this->tpl->setCurrentBlock('creation');
                    $this->tpl->setVariable('CREATION_DATE', ilDatePresentation::formatDate(new ilDateTime($a_set['create_date'], IL_CAL_DATETIME)));
                    $this->tpl->parseCurrentBlock();
            }
        }



        if (!$this->objDefinition->isPlugin($type)) {
            $type_txt = $this->lng->txt('icon') . ' ' . $this->lng->txt('obj_' . $type);
            $icon = ilObject::_getIcon((int) $obj_id, 'small', $type);
        } else {
            $type_txt = ilObjectPlugin::lookupTxtById($type, "obj_" . $type);
            $icon = ilObject::_getIcon((int) $obj_id, 'small', $type);
        }

        $this->tpl->setVariable(
            "TYPE_IMG",
            ilUtil::img(
                $icon,
                $type_txt,
                '',
                '',
                0,
                '',
                'ilIcon'
            )
        );
    }

    public function getHTML(): string
    {
        $view_control = $this->ui->renderer()->render($this->buildSortationViewControl());
        return $view_control . parent::getHTML();
    }

    public function setDataAndApplySortation(array $set): void
    {
        if (in_array('create_date', $this->getSelectedColumns())) {
            foreach ($set as $key => $row) {
                $set[$key]['create_date'] = ilObject::_lookupCreationDate($row['obj_id']);
            }
        }

        switch ($this->getCurrentSortation()) {
            case 'relevance':
                usort($set, function ($a, $b) {
                    return $b['relevance'] <=> $a['relevance'];
                });
                break;

            case 'title_desc':
                usort($set, function ($a, $b) {
                    return [$b['title'], $b['relevance'] ?? ''] <=> [$a['title'], $a['relevance'] ?? ''];
                });
                break;

            case 'title_asc':
                usort($set, function ($a, $b) {
                    return [$a['title'], $b['relevance'] ?? ''] <=> [$b['title'], $a['relevance'] ?? ''];
                });
                break;

            case 'creation_date_desc':
                usort($set, function ($a, $b) {
                    $a_date = \DateTime::createFromFormat('m-d-Y H:i:s', $a['create_date']);
                    $b_date = \DateTime::createFromFormat('m-d-Y H:i:s', $b['create_date']);
                    return [$b_date, $b['relevance'] ?? ''] <=> [$a_date, $a['relevance'] ?? ''];
                });
                break;

            case 'creation_date_asc':
                usort($set, function ($a, $b) {
                    $a_date = \DateTime::createFromFormat('Y-m-d H:i:s', $a['create_date']);
                    $b_date = \DateTime::createFromFormat('Y-m-d H:i:s', $b['create_date']);
                    return [$a_date, $b['relevance'] ?? ''] <=> [$b_date, $a['relevance'] ?? ''];
                });
                break;
        };
        parent::setData($set);
    }

    /**
     * Returns key => label
     */
    protected function getPossibleSortations(): array
    {
        $sorts = [
            'relevance' => $this->lng->txt('search_sort_relevance'),
            'title_asc' => $this->lng->txt('search_sort_title_asc'),
            'title_desc' => $this->lng->txt('search_sort_title_desc')
        ];
        if (in_array('create_date', $this->getSelectedColumns())) {
            $sorts = array_merge($sorts, [
                'creation_date_desc' => $this->lng->txt('search_sort_creation_date_desc'),
                'creation_date_asc' => $this->lng->txt('search_sort_creation_date_asc')
            ]);
        }
        return $sorts;
    }

    protected function getDefaultSortation(): string
    {
        return $this->enabledRelevance() ? 'relevance' : 'title_asc';
    }

    protected function enabledRelevance(): bool
    {
        return ilSearchSettings::getInstance()->enabledLucene();
    }
}
