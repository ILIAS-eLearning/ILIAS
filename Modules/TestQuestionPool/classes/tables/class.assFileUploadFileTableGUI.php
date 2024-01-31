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
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @author Björn Heyser <bheyser@databay.de>
*/
class assFileUploadFileTableGUI extends ilTable2GUI
{
    // hey: prevPassSolutions - support file reuse with table
    private \ILIAS\ResourceStorage\Services $irss;
    protected $postVar = '';
    // hey.

    public function __construct($a_parent_obj, $a_parent_cmd, $formname = 'test_output')
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->irss = $DIC->resourceStorage();

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setFormName($formname);
        $this->setStyle('table', 'std');
        $this->addColumn('', 'f', '1%');
        $this->addColumn($this->lng->txt('filename'), 'filename', '70%');
        $this->addColumn($this->lng->txt('date'), 'date', '29%');
        $this->setDisplayAsBlock(true);
        $this->setPrefix('deletefiles');
        $this->setSelectAllCheckbox('deletefiles');

        $this->setRowTemplate("tpl.il_as_qpl_fileupload_file_row.html", "Modules/TestQuestionPool");

        $this->disable('sort');
        $this->disable('linkbar');
        $this->enable('header');
    }

    protected function hasPostVar(): bool
    {
        return (bool) strlen($this->getPostVar());
    }

    public function getPostVar(): string
    {
        return $this->postVar;
    }

    public function setPostVar(string $postVar): void
    {
        $this->postVar = $postVar;
    }

    public function initCommand(string $lang_var, string $cmd, string $post_var): void
    {
        if (count($this->getData())) {
            $this->enable('select_all');

            $this->setSelectAllCheckbox($post_var);
            $this->setPrefix($post_var);
            $this->setPostVar($post_var);

            $on_click = "return (function(e){ e.name += '[{$cmd}]';})(this);";
            $this->addCommandButton($this->parent_cmd, $this->lng->txt($lang_var), $on_click);
        }
    }

    public function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('VAL_ID', $a_set['solution_id']);
        $this->tpl->setVariable('VAL_FILE', $this->buildFileItemContent($a_set));

        if ($this->hasPostVar()) {
            $this->tpl->setVariable('VAL_POSTVAR', $this->getPostVar());
        }

        ilDatePresentation::setUseRelativeDates(false);
        $this->tpl->setVariable('VAL_DATE', ilDatePresentation::formatDate(new ilDateTime($a_set["tstamp"], IL_CAL_UNIX)));
    }

    protected function buildFileItemContent(array $a_set): string
    {
        $value = $a_set['value2'];
        if($value === 'rid') {
            $rid = $this->irss->manage()->find($a_set['value1']);
            if($rid === null) {
                return ilLegacyFormElementsUtil::prepareFormOutput($value);
            }
            $value = $this->irss->manage()->getCurrentRevision($rid)->getTitle();
        }

        if (!isset($a_set['webpath']) || !strlen($a_set['webpath'])) {
            return ilLegacyFormElementsUtil::prepareFormOutput($value);
        }

        $link = "<a href='{$a_set['webpath']}{$a_set['value1']}' download target='_blank'>";
        $link .= ilLegacyFormElementsUtil::prepareFormOutput($value) . '</a>';

        return $link;
    }
}
