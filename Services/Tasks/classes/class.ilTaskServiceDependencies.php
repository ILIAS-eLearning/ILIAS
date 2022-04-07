<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use ILIAS\DI\UIServices;

/**
 * Task service dependencies
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTaskServiceDependencies
{
    protected ilLanguage $lng;

    protected UIServices $ui;

    protected ilObjUser $user;

    protected ilAccessHandler $access;

    protected ilDerivedTaskProviderMasterFactory $derived_task_provider_master_factory;

    /**
     * Constructor
     */
    public function __construct(
        ilObjUser $user,
        ilLanguage $lng,
        UIServices $ui,
        ilAccessHandler $access,
        ilDerivedTaskProviderMasterFactory $derived_task_provider_master_factory
    ) {
        $this->lng = $lng;
        $this->ui = $ui;
        $this->user = $user;
        $this->access = $access;
        $this->derived_task_provider_master_factory = $derived_task_provider_master_factory;
    }

    /**
     * Get derived task provider master factory
     */
    public function getDerivedTaskProviderMasterFactory() : ilDerivedTaskProviderMasterFactory
    {
        return $this->derived_task_provider_master_factory;
    }

    /**
     * Get language object
     */
    public function language() : ilLanguage
    {
        return $this->lng;
    }

    /**
     * Get current user
     */
    public function user() : ilObjUser
    {
        return $this->user;
    }

    /**
     * Get ui service
     */
    public function ui() : UIServices
    {
        return $this->ui;
    }

    /**
     * Get access
     */
    protected function getAccess() : ilAccessHandler
    {
        return $this->access;
    }
}
