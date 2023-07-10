<?php

declare(strict_types=1);

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
 * Presentation of search results
 *
 * Used for object cloning
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilObjectCopySearchResultTableGUI extends ilTable2GUI
{
    protected ilObjUser $user;
    protected ilObjectDefinition $obj_definition;
    protected string $type;
    protected ?int $selected_reference = null;

    public function __construct(?object $parent_class, string $parent_cmd, string $type)
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->obj_definition = $DIC["objDefinition"];

        $this->setId('obj_copy_' . $type);
        parent::__construct($parent_class, $parent_cmd);
        $this->type = $type;

        if (!$this->obj_definition->isPlugin($this->type)) {
            $title = $this->lng->txt('obj_' . $this->type . '_duplicate');
        } else {
            $plugin = ilObjectPlugin::getPluginObjectByType($this->type);
            $title = $plugin->txt('obj_' . $this->type . '_duplicate');
        }

        $this->setTitle($title);
        $this->user->getPref('search_max_hits');

        $this->addColumn($this->lng->txt('search_title_description'), 'title', '99%');

        $this->setEnableHeader(true);
        $this->setRowTemplate("tpl.obj_copy_search_result_row.html", "Services/Object");
        $this->setEnableTitle(true);
        $this->setEnableNumInfo(true);
        $this->setDefaultOrderField('title');
        $this->setShowRowsSelector(true);

        if ($this->obj_definition->isContainer($this->type)) {
            $this->addCommandButton('saveSource', $this->lng->txt('btn_next'));
        } else {
            $this->addCommandButton('saveSource', $title);
        }

        $this->addCommandButton('cancel', $this->lng->txt('cancel'));
    }

    public function setSelectedReference(int $selected_reference): void
    {
        $this->selected_reference = $selected_reference;
    }

    public function getSelectedReference(): ?int
    {
        return $this->selected_reference;
    }

    public function parseSearchResults(array $res): void
    {
        $rows = [];
        foreach ($res as $obj_id => $references) {
            $r['title'] = ilObject::_lookupTitle($obj_id);
            $r['desc'] = ilObject::_lookupDescription($obj_id);
            $r['obj_id'] = $obj_id;
            $r['refs'] = $references;

            $rows[] = $r;
        }

        $this->setData($rows);
    }

    protected function fillRow(array $set): void
    {
        $this->tpl->setVariable('VAL_TITLE', $set['title']);
        if (strlen($set['desc'])) {
            $this->tpl->setVariable('VAL_DESC', $set['desc']);
        }
        $this->tpl->setVariable('TXT_PATHES', $this->lng->txt('pathes'));

        foreach ((array) $set['refs'] as $reference) {
            $path = new ilPathGUI();

            $this->tpl->setCurrentBlock('path');
            $this->tpl->setVariable('VAL_ID', $reference);
            $this->tpl->setVariable('VAL_PATH', $path->getPath(ROOT_FOLDER_ID, (int) $reference));

            if ($reference == $this->getSelectedReference()) {
                $this->tpl->setVariable('VAL_CHECKED', 'checked="checked"');
            }

            $this->tpl->parseCurrentBlock();
        }
    }
}
