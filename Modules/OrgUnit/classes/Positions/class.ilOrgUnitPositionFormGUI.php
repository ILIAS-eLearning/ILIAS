<?php

use ILIAS\Modules\OrgUnit\ARHelper\BaseForm;

/**
 * Class ilOrgUnitPositionFormGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPositionFormGUI extends BaseForm
{
    const F_AUTHORITIES = "authorities";
    /**
     * @var \ilOrgUnitPosition
     */
    protected $object;
    const F_TITLE = 'title';
    const F_DESCRIPTION = 'description';


    protected function initFormElements()
    {
        $te = new ilTextInputGUI($this->txt(self::F_TITLE), self::F_TITLE);
        $te->setRequired(true);
        $this->addItem($te);

        $te = new ilTextAreaInputGUI($this->txt(self::F_DESCRIPTION), self::F_DESCRIPTION);
        $this->addItem($te);

        $m = new ilOrgUnitGenericMultiInputGUI($this->txt(self::F_AUTHORITIES), self::F_AUTHORITIES);
        $m->setShowLabel(true);
        $m->setRenderOneForEmptyValue(false);
        $m->setMulti(true);

        $id = new ilHiddenInputGUI('id');
        $m->addInput($id);

        $over = new ilSelectInputGUI($this->txt('over'), 'over');
        $over_options = array();
        $over_options[ilOrgUnitAuthority::OVER_EVERYONE] = $this->txt('over_'
                                                                      . ilOrgUnitAuthority::OVER_EVERYONE);
        $over_options = $over_options + ilOrgUnitPosition::getArray('id', 'title');
        $over->setOptions($over_options);
        $m->addInput($over);

        $available_scopes = array();
        foreach (ilOrgUnitAuthority::getScopes() as $scope) {
            $txt = $this->txt('scope_' . $scope);
            $available_scopes[$scope] = $txt;
        }

        $scopes = new ilSelectInputGUI($this->txt('scope'), 'scope');
        $scopes->setOptions($available_scopes);
        $m->addInput($scopes);

        $this->addItem($m);
    }


    public function fillForm()
    {
        $array = array(
            self::F_TITLE => $this->object->getTitle(),
            self::F_DESCRIPTION => $this->object->getDescription(),
            self::F_AUTHORITIES => $this->object->getAuthoritiesAsArray(),
        );

        $this->setValuesByArray($array);
    }


    /**
     * returns whether checkinput was successful or not.
     *
     * @return bool
     */
    public function fillObject()
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
