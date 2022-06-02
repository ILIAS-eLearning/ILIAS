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

use ILIAS\Modules\OrgUnit\ARHelper\BaseForm;

/**
 * Class ilOrgUnitPositionFormGUI
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPositionFormGUI extends BaseForm
{
    public const F_AUTHORITIES = "authorities";
    protected ActiveRecord $object;
    public const F_TITLE = 'title';
    public const F_DESCRIPTION = 'description';

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

        $id = new ilHiddenInputGUI('id');
        $m->addInput($id);

        $over = new ilSelectInputGUI($lng->txt('over'), 'over');
        $over_options = array();
        $over_options[ilOrgUnitAuthority::OVER_EVERYONE] = $lng->txt('over_'
            . ilOrgUnitAuthority::OVER_EVERYONE);
        $over_options += ilOrgUnitPosition::getArray('id', 'title');
        $over->setOptions($over_options);
        $m->addInput($over);

        $available_scopes = array();
        foreach (ilOrgUnitAuthority::getScopes() as $scope) {
            $txt = $lng->txt('scope_' . $scope);
            $available_scopes[$scope] = $txt;
        }

        $scopes = new ilSelectInputGUI($lng->txt('scope'), 'scope');
        $scopes->setOptions($available_scopes);
        $m->addInput($scopes);

        $this->addItem($m);
    }

    public function fillForm(): void
    {
        $array = array(
            self::F_TITLE => $this->object->getTitle(),
            self::F_DESCRIPTION => $this->object->getDescription(),
            self::F_AUTHORITIES => $this->object->getAuthoritiesAsArray(),
        );

        $this->setValuesByArray($array);
    }

    public function fillObject(): bool
    {
        if (!$this->checkInput()) {
            return false;
        }

        $this->object->setTitle($this->getInput(self::F_TITLE));
        $this->object->setDescription($this->getInput(self::F_DESCRIPTION));

        $authorities = (array) $this->getInput(self::F_AUTHORITIES);
        $ilOrgUnitAuthorities = array();
        foreach ($authorities as $authority) {
            /**
             * @var $ilOrgUnitAuthority ilOrgUnitAuthority
             */
            $id = $authority["id"];
            $ilOrgUnitAuthority = ilOrgUnitAuthority::findOrGetInstance($id);
            $ilOrgUnitAuthority->setPositionId($this->object->getId());
            $ilOrgUnitAuthority->setScope($authority["scope"]);
            $ilOrgUnitAuthority->setOver($authority["over"]);
            $ilOrgUnitAuthorities[] = $ilOrgUnitAuthority;
        }

        $this->object->setAuthorities($ilOrgUnitAuthorities);

        return true;
    }
}
