<?php declare(strict_types = 1);

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

namespace ILIAS\Survey\Settings;

use ILIAS\Survey\InternalGUIService;
use ILIAS\Survey\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class UIFactory
{
    protected InternalGUIService $ui_service;
    protected SettingsFormGUI $settings_form_gui;
    protected \ilObjectServiceInterface $object_service;
    protected InternalDomainService $domain_service;

    public function __construct(
        InternalGUIService $ui_service,
        \ilObjectServiceInterface $object_service,
        \ilObjSurvey $survey,
        InternalDomainService $domain_service
    ) {
        $this->ui_service = $ui_service;
        $this->object_service = $object_service;
        $this->domain_service = $domain_service;

        $mode_ui_modifier = $ui_service->modeUIModifier($survey->getMode());
        $this->settings_form_gui = new SettingsFormGUI(
            $ui_service,
            $this->domain_service,
            $object_service,
            $survey,
            $mode_ui_modifier
        );
    }

    public function form(string $target_class) : \ilPropertyFormGUI
    {
        return $this->settings_form_gui->getForm($target_class);
    }

    public function checkForm(\ilPropertyFormGUI $form) : bool
    {
        return $this->settings_form_gui->checkForm($form);
    }

    public function saveForm(\ilPropertyFormGUI $form) : void
    {
        $this->settings_form_gui->saveForm($form);
    }
}
