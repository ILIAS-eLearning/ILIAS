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
 * Class ilQuestionPoolImportVerificationTableGUI
 */
class ilQuestionPoolImportVerificationTableGUI extends ilTable2GUI
{
    /**
     * @inheritdoc
     */
    public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "")
    {
        $this->setId('qpl_imp_verify');
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

        $this->setOpenFormTag(false);
        $this->setCloseFormTag(false);
        $this->disable('sort');
        $this->setLimit(PHP_INT_MAX);
        $this->setSelectAllCheckbox('ident[]');

        $this->setRowTemplate('tpl.qpl_import_verification_row.html', 'Modules/TestQuestionPool');
        $this->addMultiCommand('importVerifiedFile', $this->lng->txt("import"));
        $this->addCommandButton('cancelImport', $this->lng->txt("cancel"));

        $this->initColumns();
    }

    /**
     *
     */
    protected function initColumns(): void
    {
        $this->addColumn('', '', '1%', true);
        $this->addColumn($this->lng->txt('question_title'));
        $this->addColumn($this->lng->txt('question_type'));
    }

    /**
     * @inheritdoc
     */
    protected function fillRow(array $a_set): void
    {
        $a_set['chb'] = ilLegacyFormElementsUtil::formCheckbox(true, 'ident[]', $a_set['ident']);
        parent::fillRow($a_set);
    }
}
