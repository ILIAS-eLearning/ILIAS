<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * News service dependencies
 *
 * @author killing@leifos.de
 * @ingroup ServiceNews
 */
class ilNewsServiceDependencies
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilNewsObjectAdapterInterface
     */
    protected $obj_adapter;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * Constructor
     * @param ilLanguage $lng
     */
    public function __construct(ilLanguage $lng, ilSetting $settings, ilObjUser $user, ilNewsObjectAdapterInterface $obj_adapter)
    {
        $this->lng = $lng;
        $this->settings = $settings;
        $this->user = $user;
        $this->obj_adapter = $obj_adapter;
    }

    /**
     * Get object adapter
     *
     * @return ilNewsObjectAdapterInterface
     */
    public function obj()
    {
        return $this->obj_adapter;
    }


    /**
     * Get language object
     *
     * @return ilLanguage
     */
    public function language() : ilLanguage
    {
        return $this->lng;
    }

    /**
     * Get settings object
     *
     * @return ilSetting
     */
    public function settings() : ilSetting
    {
        return $this->settings;
    }

    /**
     * Get current user
     *
     * @return ilObjUser
     */
    public function user() : ilObjUser
    {
        return $this->user;
    }
}
