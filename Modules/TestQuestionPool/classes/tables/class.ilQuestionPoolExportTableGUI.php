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
 * Class ilQuestionPoolExportTableGUI
 * @author Michael Jansen <mjansen@databay.de>
 * @ingroup ModulesTest
 */
class ilQuestionPoolExportTableGUI extends ilExportTableGUI
{
    private \ILIAS\UI\Factory $ui_factory;
    private \ILIAS\UI\Renderer $ui_renderer;

    public function __construct($a_parent_obj, $a_parent_cmd, $a_exp_obj)
    {
        global $DIC;
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();

        parent::__construct($a_parent_obj, $a_parent_cmd, $a_exp_obj);

        // NOT REQUIRED ANYMORE, PROBLEM NOW FIXED IN THE ROOT
        // KEEP CODE, JF OPINIONS / ROOT FIXINGS CAN CHANGE
        //$this->addCustomColumn($this->lng->txt('actions'), $this, 'formatActionsList');
    }

    /**
     * @param string $type
     * @param string $filename
     */
    protected function formatActionsList($type, $filename): string
    {
        /**
         * @var $ilCtrl ilCtrl
         */
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $ilCtrl->setParameter($this->getParentObject(), 'file', $type . ':' . $filename);
        $actions = [];
        $action = $this->ui_factory->link()->standard($this->lng->txt('download'), $ilCtrl->getLinkTarget($this->getParentObject(), 'download'));
        $ilCtrl->setParameter($this->getParentObject(), 'file', '');
        $dropdown = $this->ui_factory->dropdown()->standard($action)->withLabel($this->lng->txt('actions'));
        return $this->ui_renderer->render($dropdown);

    }

    /**
     * @inheritdoc
     */
    public function numericOrdering(string $a_field): bool
    {
        if (in_array($a_field, array('size', 'date'))) {
            return true;
        }

        return false;
    }

    /***
     *
     */
    protected function initMultiCommands(): void
    {
        $this->addMultiCommand('confirmDeletion', $this->lng->txt('delete'));
    }
}
