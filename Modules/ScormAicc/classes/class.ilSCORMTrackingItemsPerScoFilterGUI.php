<?php declare(strict_types=1);
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
 * Class ilSCORMTrackingItemsPerScoFilterGUI
 *
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @ingroup ModulesScormAicc
 */
class ilSCORMTrackingItemsPerScoFilterGUI extends ilPropertyFormGUI
{
    private object $parent_obj;
    private string $parent_cmd;
    public ilPropertyFormGUI $form;

    public function __construct(object $a_parent_obj, string $a_parent_cmd)
    {
        $this->parent_obj = $a_parent_obj;
        $this->parent_cmd = $a_parent_cmd;
        parent::__construct();
    }

    /**
     * @throws ilCtrlException
     */
    public function parse(string $scoSelected, string $report, array $reports) : void
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $lng->loadLanguageModule("scormtrac");
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($ilCtrl->getFormAction($this->parent_obj));

        $options = array("all" => $lng->txt("all"));
        $scos = $this->parent_obj->object->getTrackedItems();

        foreach ($scos as $row) {
            $options[$row->getId()] = $row->getTitle();
        }
        $si = new ilSelectInputGUI($lng->txt("chapter"), "scoSelected");
        $si->setOptions($options);
        $si->setValue($scoSelected);
        $this->form->addItem($si);

        $options = array("choose" => $lng->txt("please_choose"));
        foreach ($reports as $value) {
            $options[$value] = $lng->txt(strtolower($value));
        }
        $si = new ilSelectInputGUI($lng->txt("report"), "report");
        $si->setOptions($options);
        $si->setValue($report);
        $this->form->addItem($si);
        $this->form->addCommandButton($this->parent_cmd, $lng->txt("apply_filter"));
    }
}
