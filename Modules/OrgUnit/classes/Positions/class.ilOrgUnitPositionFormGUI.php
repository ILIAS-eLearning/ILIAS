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
 * Class ilOrgUnitPositionFormGUI
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPositionFormGUI extends \ilPropertyFormGUI
{
    public const F_AUTHORITIES = "authorities";
    protected \ilOrgUnitPosition $object;
    public const F_TITLE = 'title';
    public const F_DESCRIPTION = 'description';
    protected \ilOrgUnitPositionDBRepository $positionRepo;

    protected BaseCommands $parent_gui;
    protected \ILIAS\DI\Container $DIC;
    protected \ilLanguage $lng;
    protected \ilCtrl $ctrl;

    public function __construct(BaseCommands $parent_gui, \ilOrgUnitPosition $object)
    {
        global $DIC;

        $dic = ilOrgUnitLocalDIC::dic();
        $this->positionRepo = $dic["repo.Positions"];

        $this->parent_gui = $parent_gui;
        $this->object = $object;
        $this->lng = $DIC->language();

        $this->ctrl = $DIC->ctrl();
        $this->ctrl->saveParameter($parent_gui, 'arid');
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->initFormElements();
        $this->initButtons();
        $this->setTarget('_top');

        parent::__construct();
    }

    protected function initFormElements(): void
    {
        global $DIC;
        $lng = $DIC->language();

        $te = new ilTextInputGUI($lng->txt(self::F_TITLE), self::F_TITLE);
        $te->setRequired(true);
        $this->addItem($te);

        $te = new ilTextAreaInputGUI($lng->txt(self::F_DESCRIPTION), self::F_DESCRIPTION);
        $this->addItem($te);

        $m = new ilOrgUnitGenericMultiInputGUI($lng->txt(self::F_AUTHORITIES), self::F_AUTHORITIES);
        $m->setShowLabel(true);
        $m->setRenderOneForEmptyValue(false);
        $m->setMulti(true);

        $id = new ilHiddenInputGUI(ilOrgUnitGenericMultiInputGUI::MULTI_FIELD_ID);
        $m->addInput($id);

        $over = new ilSelectInputGUI($lng->txt('over'), ilOrgUnitGenericMultiInputGUI::MULTI_FIELD_OVER);
        $over_options = [];
        $over_options[ilOrgUnitAuthority::OVER_EVERYONE] = $lng->txt('over_'
            . ilOrgUnitAuthority::OVER_EVERYONE);
        $over_options += $this->positionRepo->getArray('id', 'title');
        $over->setOptions($over_options);
        $m->addInput($over);

        $available_scopes = [];
        foreach (ilOrgUnitAuthority::getScopes() as $scope) {
            $txt = $lng->txt('scope_' . $scope);
            $available_scopes[$scope] = $txt;
        }

        $scopes = new ilSelectInputGUI($lng->txt('scope'), ilOrgUnitGenericMultiInputGUI::MULTI_FIELD_SCOPE);
        $scopes->setOptions($available_scopes);
        $m->addInput($scopes);

        $this->addItem($m);
    }

    private function initButtons(): void
    {
        if (!$this->object->getId()) {
            $this->setTitle($this->txt('create'));
            $this->addCommandButton(BaseCommands::CMD_CREATE, $this->txt(BaseCommands::CMD_CREATE));
            $this->addCommandButton(BaseCommands::CMD_CANCEL, $this->txt(BaseCommands::CMD_CANCEL));
        } else {
            $this->setTitle($this->txt('update'));
            $this->addCommandButton(BaseCommands::CMD_UPDATE, $this->txt(BaseCommands::CMD_UPDATE));
            $this->addCommandButton(BaseCommands::CMD_CANCEL, $this->txt(BaseCommands::CMD_CANCEL));
        }
    }

    public function fillForm(): void
    {
        $array = [
            self::F_TITLE => $this->object->getTitle(),
            self::F_DESCRIPTION => $this->object->getDescription(),
            self::F_AUTHORITIES => $this->object->getAuthoritiesAsArray()
        ];
        $this->setValuesByArray($array);
    }

    public function fillObject(): bool
    {
        if (!$this->checkInput()) {
            return false;
        }

        $authorities = ($this->getInput(self::F_AUTHORITIES) != '')
            ? (array) $this->getInput(self::F_AUTHORITIES)
            : [];

        if (count($authorities) == 0) {
            $this->object = $this->object
                ->withTitle($this->getInput(self::F_TITLE))
                ->withDescription($this->getInput(self::F_DESCRIPTION))
                ->withAuthorities([]);
            return true;
        }

        /**
         * @var ilOrgUnitAuthority[] $new_authorities
         */
        $new_authorities = [];
        foreach ($authorities as $authority) {
            $id = ($authority["id"] == '') ? null : (int) $authority["id"];

            $new_authorities[] = $this->positionRepo->getAuthority($id)
                ->withPositionId($this->object->getId())
                ->withScope((int) $authority["scope"])
                ->withOver((int) $authority["over"]);
        }
        $this->object = $this->object
            ->withTitle($this->getInput(self::F_TITLE))
            ->withDescription($this->getInput(self::F_DESCRIPTION))
            ->withAuthorities($new_authorities);
        return true;
    }

    public function saveObject(): bool
    {
        if ($this->fillObject() === false) {
            return false;
        }

        $this->object = $this->positionRepo->store($this->object);
        return true;
    }

    private function txt(string $key): string
    {
        return $this->lng->txt($key);
    }

    private function infoTxt(string $key): string
    {
        return $this->lng->txt($key . '_info');
    }
}
