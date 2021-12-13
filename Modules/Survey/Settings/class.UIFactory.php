<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Settings;

use ILIAS\Survey\InternalUIService;
use ILIAS\Survey\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class UIFactory
{
    /**
     * @var InternalUIService
     */
    protected $ui_service;

    /**
     * @var SettingsFormGUI
     */
    protected $settings_form_gui;

    /**
     * @var \ilObjectServiceInterface
     */
    protected $object_service;

    /**
     * @var InternalDomainService
     */
    protected $domain_service;

    /**
     * Constructor
     */
    public function __construct(
        InternalUIService $ui_service,
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

    /**
     * @param string $target_class
     * @return \ilPropertyFormGUI
     */
    public function form(string $target_class) : \ilPropertyFormGUI
    {
        return $this->settings_form_gui->getForm($target_class);
    }

    /**
     * @param \ilPropertyFormGUI $form
     * @return bool
     */
    public function checkForm(\ilPropertyFormGUI $form) : bool
    {
        return $this->settings_form_gui->checkForm($form);
    }

    /**
     * @param \ilPropertyFormGUI $form
     * @return bool
     */
    public function saveForm(\ilPropertyFormGUI $form) : void
    {
        $this->settings_form_gui->saveForm($form);
    }
}
