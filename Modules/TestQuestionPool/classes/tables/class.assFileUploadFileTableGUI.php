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
* @version $Id$
*
* @ingroup ModulesGroup
*/

class assFileUploadFileTableGUI extends ilTable2GUI
{
    // hey: prevPassSolutions - support file reuse with table
    protected $postVar = '';
    // hey.

    public function __construct($a_parent_obj, $a_parent_cmd, $formname = 'test_output')
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setFormName($formname);
        $this->setStyle('table', 'std');
        $this->addColumn('', 'f', '1%');
        $this->addColumn($this->lng->txt('filename'), 'filename', '70%');
        $this->addColumn($this->lng->txt('date'), 'date', '29%');
        $this->setDisplayAsBlock(true);

        // hey: prevPassSolutions - configure table with initCommand()
        $this->setPrefix('deletefiles');
        $this->setSelectAllCheckbox('deletefiles');
        // hey.

        $this->setRowTemplate("tpl.il_as_qpl_fileupload_file_row.html", "Modules/TestQuestionPool");

        $this->disable('sort');
        $this->disable('linkbar');
        $this->enable('header');
        // hey: prevPassSolutions - configure table with initCommand()
        #$this->enable('select_all');
        // hey.
    }

    // hey: prevPassSolutions - support file reuse with table
    /**
     * @return bool
     */
    protected function hasPostVar(): bool
    {
        return (bool) strlen($this->getPostVar());
    }

    /**
     * @return string
     */
    public function getPostVar(): string
    {
        return $this->postVar;
    }

    /**
     * @param string $postVar
     */
    public function setPostVar($postVar): void
    {
        $this->postVar = $postVar;
    }
    // hey.

    // hey: prevPassSolutions - support file reuse with table
    public function initCommand(ilAssFileUploadFileTableCommandButton $commandButton, $postVar): void
    {
        if (count($this->getData())) {
            $this->enable('select_all');

            $this->setSelectAllCheckbox($postVar);
            $this->setPrefix($postVar);
            $this->setPostVar($postVar);

            $commandButton->setCommand($this->parent_cmd);
            $this->addCommandButtonInstance($commandButton);
        }
    }
    // hey.

    /**
     * fill row
     * @access public
     * @param
     * @return void
     */
    public function fillRow(array $a_set): void
    {
        global $DIC;
        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];

        $this->tpl->setVariable('VAL_ID', $a_set['solution_id']);
        // hey: prevPassSolutions - support file reuse with table
        $this->tpl->setVariable('VAL_FILE', $this->buildFileItemContent($a_set));

        if ($this->hasPostVar()) {
            $this->tpl->setVariable('VAL_POSTVAR', $this->getPostVar());
        }
        // hey.
        ilDatePresentation::setUseRelativeDates(false);
        $this->tpl->setVariable('VAL_DATE', ilDatePresentation::formatDate(new ilDateTime($a_set["tstamp"], IL_CAL_UNIX)));
    }

    // hey: prevPassSolutions - support file reuse with table
    /**
     * @param $a_set
     */
    protected function buildFileItemContent($a_set): string
    {
        if (!isset($a_set['webpath']) || !strlen($a_set['webpath'])) {
            return ilLegacyFormElementsUtil::prepareFormOutput($a_set['value2']);
        }

        $link = "<a href='{$a_set['webpath']}{$a_set['value1']}' download target='_blank'>";
        $link .= ilLegacyFormElementsUtil::prepareFormOutput($a_set['value2']) . '</a>';

        return $link;
    }
    // hey.
}
