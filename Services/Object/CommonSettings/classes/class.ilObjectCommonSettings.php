<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Common settings for objects
 *
 * @author @leifos.de
 * @ingroup
 */
class ilObjectCommonSettings implements ilObjectCommonSettingsInterface
{
    /**
     * @var ilObjectService
     */
    protected $service;

    /**
     * Constructor
     */
    public function __construct(ilObjectService $service)
    {
        $this->service = $service;
    }

    /**
     * @inheritdoc
     */
    public function tileImage() : ilObjectTileImageFactory
    {
        return new ilObjectTileImageFactory($this->service);
    }

    /**
     * @inheritdoc
     */
    public function legacyForm(ilPropertyFormGUI $form, ilObject $object) : ilObjectCommonSettingFormAdapter
    {
        return new ilObjectCommonSettingFormAdapter($this->service, $object, $form);
    }
}
