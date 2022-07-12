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
 * TableGUI class for
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTrashTableGUI extends ilTable2GUI
{
    protected const TABLE_BASE_ID = 'adm_trash_table';

    protected ilAccessHandler $access;
    protected ilObjectDefinition $obj_definition;
    private int $ref_id = 0;
    private array $current_filter = [];

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        int $ref_id
    ) {
        global $DIC;

        $this->access = $DIC->access();
        $this->obj_definition = $DIC["objDefinition"];
        $this->ref_id = $ref_id;

        $this->setId(self::TABLE_BASE_ID);
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->lng->loadLanguageModule('rep');
    }

    /**
     * Init table
     */
    public function init() : void
    {
        $this->setTitle(
            $this->lng->txt('rep_trash_table_title') . ' "' .
            ilObject::_lookupTitle(ilObject::_lookupObjId($this->ref_id)) . '" '
        );

        $this->addColumn('', '', '1', true);
        $this->addColumn($this->lng->txt('type'), 'type');
        $this->addColumn($this->lng->txt('title'), 'title');
        $this->addColumn($this->lng->txt('rep_trash_table_col_deleted_by'), 'deleted_by');
        $this->addColumn($this->lng->txt('rep_trash_table_col_deleted_on'), 'deleted');
        $this->addColumn($this->lng->txt('rep_trash_table_col_num_subs'), '');

        $this->setDefaultOrderField('title');
        $this->setDefaultOrderDirection('asc');

        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);

        $this->setEnableHeader(true);
        $this->enable('sort');
        $this->setEnableTitle(true);
        $this->setEnableNumInfo(true);
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));

        $this->setRowTemplate(
            'tpl.trash_list_row.html',
            'Services/Repository/Trash'
        );
        $this->setSelectAllCheckbox('trash_id');

        $this->addMultiCommand('undelete', $this->lng->txt('btn_undelete_origin_location'));
        $this->addMultiCommand('restoreToNewLocation', $this->lng->txt('btn_undelete_new_location'));
        $this->addMultiCommand('confirmRemoveFromSystem', $this->lng->txt('btn_remove_system'));

        $this->initFilter();
    }

    public function initFilter() : void
    {
        $this->setDefaultFilterVisiblity(true);


        $type = $this->addFilterItemByMetaType(
            'type',
            ilTable2GUI::FILTER_SELECT,
            false,
            $this->lng->txt('type')
        );
        $type->setOptions($this->prepareTypeFilterTypes());
        $this->current_filter['type'] = $type->getValue();

        $title = $this->addFilterItemByMetaType(
            'title',
            ilTable2GUI::FILTER_TEXT,
            false,
            $this->lng->txt('title')
        );
        $this->current_filter['title'] = $title->getValue();

        $deleted_by = $this->addFilterItemByMetaType(
            'deleted_by',
            ilTable2GUI::FILTER_TEXT,
            false,
            $this->lng->txt('rep_trash_table_col_deleted_by')
        );
        $this->current_filter['deleted_by'] = $deleted_by->getValue();

        $deleted = $this->addFilterItemByMetaType(
            'deleted',
            ilTable2GUI::FILTER_DATE_RANGE,
            false,
            $this->lng->txt('rep_trash_table_col_deleted_on')
        );
        $this->current_filter['deleted'] = $deleted->getValue();
    }

    public function parse() : void
    {
        $this->determineOffsetAndOrder();

        $max_trash_entries = 0;

        $trash_tree_reader = new ilTreeTrashQueries();
        $items = $trash_tree_reader->getTrashNodeForContainer(
            $this->ref_id,
            $this->current_filter,
            $max_trash_entries,
            $this->getOrderField(),
            $this->getOrderDirection(),
            $this->getLimit(),
            $this->getOffset()
        );

        $this->setMaxCount($max_trash_entries);

        $rows = [];
        foreach ($items as $item) {
            $row['id'] = $item->getRefId();
            $row['obj_id'] = $item->getObjId();
            $row['type'] = $item->getType();
            $row['title'] = $item->getTitle();
            $row['description'] = $item->getDescription();
            $row['deleted_by_id'] = $item->getDeletedBy();
            $row['deleted_by'] = $this->lng->txt('rep_trash_deleted_by_unknown');
            if ($login = ilObjUser::_lookupLogin($row['deleted_by_id'])) {
                $row['deleted_by'] = $login;
            }
            $row['deleted'] = $item->getDeleted();
            $row['num_subs'] = $trash_tree_reader->getNumberOfTrashedNodesForTrashedContainer($item->getRefId());

            $rows[] = $row;
        }


        $this->setData($rows);
    }

    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable('ID', $a_set['id']);
        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        if (trim($a_set['description']) !== '') {
            $this->tpl->setCurrentBlock('with_desc');
            $this->tpl->setVariable('VAL_DESC', $a_set['description']);
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setCurrentBlock('with_path');
        $path = new ilPathGUI();
        $path->enableTextOnly(false);
        $this->tpl->setVariable('PATH', $path->getPath($this->ref_id, $a_set['id']));
        $this->tpl->parseCurrentBlock();

        $img = ilObject::_getIcon(
            (int) $a_set['obj_id'],
            'small',
            $a_set['type']
        );
        if ($img !== '') {
            $alt = ($this->obj_definition->isPlugin($a_set['type']))
                ? $this->lng->txt('icon') . ' ' . ilObjectPlugin::lookupTxtById($a_set['type'], 'obj_' . $a_set['type'])
                : $this->lng->txt('icon') . ' ' . $this->lng->txt('obj_' . $a_set['type'])
            ;
            $this->tpl->setVariable('IMG_PATH', $img);
            $this->tpl->setVariable('IMG_ALT', $alt);
        }

        $this->tpl->setVariable('VAL_DELETED_BY', $a_set['deleted_by']);

        $dt = new ilDateTime($a_set['deleted'], IL_CAL_DATETIME);
        $this->tpl->setVariable('VAL_DELETED_ON', ilDatePresentation::formatDate($dt));
        $this->tpl->setVariable('VAL_SUBS', (string) (int) $a_set['num_subs']);
    }

    protected function prepareTypeFilterTypes() : array
    {
        $trash = new ilTreeTrashQueries();
        $subs = $trash->getTrashedNodeTypesForContainer($this->ref_id);


        $options = [];
        foreach ($subs as $type) {
            if ($type === 'rolf') {
                continue;
            }
            if ($type === 'root') {
                continue;
            }

            if (!$this->obj_definition->isRBACObject($type)) {
                continue;
            }
            $options[$type] = $this->lng->txt('objs_' . $type);
        }
        asort($options, SORT_LOCALE_STRING);
        array_unshift($options, $this->lng->txt('select_one'));
        return $options;
    }
}
