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
 ********************************************************************
 */

declare(strict_types=1);
use ILIAS\Modules\OrgUnit\ARHelper\BaseCommands;

/**
 * Class ilOrgUnitPositionTableGUI
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPositionTableGUI extends ilTable2GUI
{
    protected \ILIAS\DI\Container $DIC;
    protected array $columns = [
            'title',
            'description',
            'authorities',
        ];
    protected \ilOrgUnitPositionDBRepository $positionRepo;

    /**
     * ilOrgUnitPositionTableGUI constructor.
     * @param \ILIAS\Modules\OrgUnit\ARHelper\BaseCommands $parent_obj
     * @param string                                       $parent_cmd
     */
    public function __construct(BaseCommands $parent_obj, $parent_cmd)
    {
        $this->DIC = $GLOBALS["DIC"];

        $dic = ilOrgUnitLocalDIC::dic();
        $this->positionRepo = $dic["repo.Positions"];

        $this->setPrefix('orgu_types_table');
        $this->setId('orgu_types_table');
        parent::__construct($parent_obj, $parent_cmd);
        $this->setRowTemplate('tpl.position_row.html', 'Modules/OrgUnit');
        $this->initColumns();
        $this->addColumn($this->DIC->language()->txt('action'), '', '100px', false, 'text-right');
        $this->buildData();
        $this->setFormAction($this->DIC->ctrl()->getFormAction($this->parent_obj));
    }

    /**
     * Pass data to row template
     * @param array $a_set
     */
    public function fillRow(array $a_set): void
    {
        /**
         * @var $position ilOrgUnitPosition
         */
        $position = $this->positionRepo->getSingle($a_set["id"], 'id');

        $this->tpl->setVariable('TITLE', $position->getTitle());
        $this->tpl->setVariable('DESCRIPTION', $position->getDescription());
        $this->tpl->setVariable('AUTHORITIES', implode("<br>", $this->getAuthorityDescription($position->getAuthorities())));

        $this->DIC->ctrl()
                  ->setParameterByClass(ilOrgUnitPositionGUI::class, BaseCommands::AR_ID, $a_set['id']);
        $selection = new ilAdvancedSelectionListGUI();
        $selection->setListTitle($this->DIC->language()->txt('actions'));
        $selection->setId(BaseCommands::AR_ID . $a_set['id']);
        $selection->addItem($this->DIC->language()->txt('edit'), 'edit', $this->DIC->ctrl()
                                                                                   ->getLinkTargetByClass(
                                                                                       ilOrgUnitPositionGUI::class,
                                                                                       ilOrgUnitPositionGUI::CMD_EDIT
                                                                                   ));
        if (!$position->isCorePosition()) {
            $selection->addItem($this->DIC->language()->txt('delete'), 'delete', $this->DIC->ctrl()
                                                                                           ->getLinkTargetByClass(
                                                                                               ilOrgUnitPositionGUI::class,
                                                                                               ilOrgUnitPositionGUI::CMD_CONFIRM_DELETION
                                                                                           ));
        }

        $this->tpl->setVariable('ACTIONS', $selection->getHTML());
    }

    private function initColumns(): void
    {
        foreach ($this->columns as $column) {
            $this->addColumn($this->DIC->language()->txt($column), $column);
        }
    }

    /**
     * Build and set data for table.
     */
    private function buildData(): void
    {
        $this->setData($this->positionRepo->getArray());
    }

    /**
     * Returns descriptions for authorities as an array of strings
     *
     * @param ilOrgUnitAuthority[] $authorities
     */
    private function getAuthorityDescription(array $authorities): array
    {
        $lang = $this->DIC->language();
        $lang->loadLanguageModule('orgu');
        $lang_keys = array(
            'in',
            'over',
            'scope_' . ilOrgUnitAuthority::SCOPE_SAME_ORGU,
            'scope_' . ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS,
            'over_' . ilOrgUnitAuthority::OVER_EVERYONE,
        );
        $t = [];
        foreach ($lang_keys as $key) {
            $t[$key] = $lang->txt($key);
        }

        $authority_description =[];
        foreach ($authorities as $authority) {
            switch ($authority->getOver()) {
                case ilOrgUnitAuthority::OVER_EVERYONE:
                    $over_txt = $t["over_" . $authority->getOver()];
                    break;
                default:
                    $over_txt = $this->positionRepo
                        ->getSingle($authority->getOver(), 'id')
                        ->getTitle();
                    break;
            }

            $authority_description[] = " " . $t["over"] . " " . $over_txt . " " . $t["in"] . " " . $t["scope_" . $authority->getScope()];
        }

        return $authority_description;
    }
}
