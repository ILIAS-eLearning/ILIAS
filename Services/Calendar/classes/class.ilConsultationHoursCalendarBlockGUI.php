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

use ILIAS\UI\Component\Item\Item;

/**
 * @ilCtrl_IsCalledBy ilConsultationHoursCalendarBlockGUI: ilColumnGUI
 */
class ilConsultationHoursCalendarBlockGUI extends ilBlockGUI
{
    protected $new_rendering = true;
    protected $consultation_hour_links;

    public function __construct()
    {
        parent::__construct();

        $this->lng->loadLanguageModule("dateplaner");

        $this->setBlockId('ch_' . $this->ctrl->getContextObjId());
        $this->setLimit(5);
        $this->setEnableNumInfo(false);
        $this->setTitle($this->lng->txt('consultation_hours_block_title'));
        $this->setPresentation(self::PRES_SEC_LIST);
    }

    public function getBlockType() : string
    {
        return 'chcal';
    }

    protected function isRepositoryObject() : bool
    {
        return false;
    }

    /**
     * Get target gui class path (for presenting the calendar)
     */
    public function getTargetGUIClassPath() : array
    {
        $target_class = [];
        if (!$this->getRepositoryMode()) {
            $target_class = ["ildashboardgui", "ilcalendarpresentationgui"];
        } else {
            switch (ilObject::_lookupType((int) $_GET["ref_id"], true)) {
                case "crs":
                    $target_class = ["ilobjcoursegui", "ilcalendarpresentationgui"];
                    break;

                case "grp":
                    $target_class = ["ilobjgroupgui", "ilcalendarpresentationgui"];
                    break;
            }
        }
        return $target_class;
    }

    public function getData() : array
    {
        if (isset($this->consultation_hour_links)) {
            return $this->consultation_hour_links;
        }
        return $this->consultation_hour_links = \ilConsultationHourUtils::getConsultationHourLinksForRepositoryObject(
            (int) $_GET['ref_id'],
            $this->user->getId(),
            $this->getTargetGUIClassPath()
        );
    }

    protected function getListItemForData(array $data) : Item
    {
        $button = $this->ui->factory()->button()->shy(
            $data['txt'] ?? '',
            $data['link'] ?? ''
        );
        return $this->ui->factory()->item()->standard($button);
    }

    public function getHTMLNew() : string
    {
        if (empty($this->getData())) {
            return '';
        }
        return parent::getHTMLNew();
    }
}
