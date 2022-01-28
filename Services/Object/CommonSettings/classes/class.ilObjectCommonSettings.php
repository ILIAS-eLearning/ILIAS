<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilObjectCommonSettings implements ilObjectCommonSettingsInterface
{
    protected ilObjectService $service;

    public function __construct(ilObjectService $service)
    {
        $this->service = $service;
    }

    public function tileImage() : ilObjectTileImageFactory
    {
        return new ilObjectTileImageFactory($this->service);
    }

    public function legacyForm(ilPropertyFormGUI $form, ilObject $object) : ilObjectCommonSettingFormAdapter
    {
        return new ilObjectCommonSettingFormAdapter($this->service, $object, $form);
    }
}
