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

/**
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * @ingroup ModulesTest
 */
class ilAssessmentFolderLogAdministrationTableGUI extends ilTable2GUI
{
    public function __construct(
        ilObjAssessmentFolderGUI $parent_obj,
        string $parent_cmd,
        bool $write_access = false
    ) {
        parent::__construct($parent_obj, $parent_cmd);

        $this->setFormName('showlog');
        $this->setStyle('table', 'fullwidth');

        $this->addColumn('', '', '1%', true);
        $this->addColumn($this->lng->txt("title"), 'title', '50%');
        $this->addColumn($this->lng->txt("ass_log_count_datasets"), 'nr', '15%');
        $this->addColumn($this->lng->txt("ass_location"), '', '30%');

        $this->setRowTemplate("tpl.il_as_tst_assessment_log_administration_row.html", "Modules/Test");

        $this->setFormAction($this->ctrl->getFormAction($parent_obj, $parent_cmd));

        if ($write_access) {
            $this->addMultiCommand('deleteLog', $this->lng->txt('ass_log_delete_entries'));
            $this->setSelectAllCheckbox('chb_test');
            $this->enable('select_all');
        }

        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");

        $this->setPrefix('chb_test');

        $this->enable('header');
        $this->enable('sort');
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable("TITLE", ilLegacyFormElementsUtil::prepareFormOutput($a_set['title']));
        $this->tpl->setVariable("NR", $a_set['nr']);
        $this->tpl->setVariable("TEST_ID", $a_set['id']);
        $this->tpl->setVariable("LOCATION_HREF", $a_set['location_href']);
        $this->tpl->setVariable("LOCATION_TXT", $a_set['location_txt']);
    }

    public function numericOrdering(string $a_field): bool
    {
        return 'nr' === $a_field;
    }
}
