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

declare(strict_types=1);

/**
 * @ilCtrl_isCalledBy ilObjMapsGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjMapsGUI: ilPermissionGUI
 */
class ilObjMapsGUI extends ilObjectGUI
{
    private readonly ilRbacSystem $rbacsystem;

    public function __construct($data, int $id = 0, bool $call_by_reference = true, bool $prepare_output = true)
    {
        parent::__construct($data, $id, $call_by_reference, $prepare_output);
        global $DIC;
        $this->rbacsystem = $DIC->rbac()->system();
    }

    public function executeCommand(): void
    {
        if (!$this->rbacsystem->checkAccess('read', $this->object->getRefId())) {
            $this->ilias->raiseError(
                $this->lng->txt('permission_denied'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        $this->lng->loadLanguageModule('maps');
        $this->tpl->setTitle($this->lng->txt('obj_maps'));
        $this->tpl->setTitleIcon(ilObject::_getIcon($this->object->getId()));
        $this->initTabs();
        switch ($this->ctrl->getNextClass()) {
            case strtolower(ilPermissionGUI::class):
                $this->ctrl->forwardCommand(new ilPermissionGUI($this));
                $this->tabs_gui->activateTab('permissions');
                break;
            default:
                $cmd = $this->ctrl->getCmd('view');
                if (in_array($cmd, ['view', 'save'], true)) {
                    $this->tabs_gui->activateTab('view');
                    $this->$cmd();
                }
                break;
        }
    }

    public function view(): void
    {
        $this->tpl->setContent($this->buildForm()->getHTML());
    }

    private function initTabs(): void
    {
        $this->tabs_gui->addTab(
            'view',
            $this->lng->txt('view'),
            $this->ctrl->getLinkTarget($this, 'view'),
        );

        $this->tabs_gui->addTab(
            'permissions',
            $this->lng->txt('perm_settings'),
            $this->ctrl->getLinkTargetByClass([self::class, ilPermissionGUI::class], 'perm')
        );
    }

    private function buildForm(): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $std_latitude = (float) ilMapUtil::getStdLatitude();
        $std_longitude = (float) ilMapUtil::getStdLongitude();
        $std_zoom = ilMapUtil::getStdZoom();
        $type = ilMapUtil::getType();
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt('maps_settings'));

        // Enable Maps
        $enable = new ilCheckboxInputGUI($lng->txt('maps_enable_maps'), 'enable');
        $enable->setChecked(ilMapUtil::isActivated());
        $enable->setInfo($lng->txt('maps_enable_maps_info'));
        $form->addItem($enable);

        // Select type
        $types = new ilSelectInputGUI($lng->txt('maps_map_type'), 'type');
        $types->setOptions(ilMapUtil::getAvailableMapTypes());
        $types->setValue($type);
        $form->addItem($types);

        // map data server property
        if ($type === 'openlayers') {
            $tile = new ilTextInputGUI($lng->txt('maps_tile_server'), 'tile');
            $tile->setValue(ilMapUtil::getStdTileServers());
            $tile->setInfo(sprintf($lng->txt('maps_custom_tile_server_info'), ilMapUtil::DEFAULT_TILE));
            $geolocation = new ilTextInputGUI($lng->txt('maps_geolocation_server'), 'geolocation');
            $geolocation->setValue(ilMapUtil::getStdGeolocationServer());
            $geolocation->setInfo($lng->txt('maps_custom_geolocation_server_info'));

            $form->addItem($tile);
            $form->addItem($geolocation);
        } else {
            // api key for google
            $key = new ilTextInputGUI('Google API Key', 'api_key');
            $key->setMaxLength(200);
            $key->setValue(ilMapUtil::getApiKey());
            $form->addItem($key);
        }

        // location property
        $loc_prop = new ilLocationInputGUI(
            $lng->txt('maps_std_location'),
            'std_location'
        );

        $loc_prop->setLatitude($std_latitude);
        $loc_prop->setLongitude($std_longitude);
        $loc_prop->setZoom((int) $std_zoom);
        $form->addItem($loc_prop);

        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $form->addCommandButton('save', $lng->txt('save'));
            $form->addCommandButton('view', $lng->txt('cancel'));
        }

        return $form;
    }

    private function save(): void
    {
        $form = $this->buildForm();
        if ($form->checkInput()) {
            if ($form->getInput('type') === 'openlayers' && 'openlayers' === ilMapUtil::getType()) {
                ilMapUtil::setStdTileServers($form->getInput('tile'));
                ilMapUtil::setStdGeolocationServer(
                    $form->getInput('geolocation')
                );
            } else {
                ilMapUtil::setApiKey($form->getInput('api_key'));
            }

            ilMapUtil::setActivated($form->getInput('enable') === '1');
            ilMapUtil::setType($form->getInput('type'));
            $location = $form->getInput('std_location');
            ilMapUtil::setStdLatitude((string) $location['latitude']);
            ilMapUtil::setStdLongitude((string) $location['longitude']);
            ilMapUtil::setStdZoom((string) $location['zoom']);
        }
        $this->ctrl->redirect($this, 'view');
    }
}
