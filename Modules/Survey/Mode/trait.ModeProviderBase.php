<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Mode;

use ILIAS\Survey\InternalDomainService;
use ILIAS\Survey\InternalUIService;
use ILIAS\Survey\InternalService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
trait ModeProviderBase
{
    /**
     * @var ?int
     */
    protected $id = null;

    /**
     * @var FeatureConfig
     */
    protected $feature_config;

    /**
     * @var InternalService
     */
    protected $service;

    /**
     * @var UIModifier
     */
    protected $ui_modifier;

    public function getId() : int
    {
        return $this->id;
    }

    public function getFeatureConfig() : FeatureConfig
    {
        return $this->feature_config;
    }

    public function getUIModifier() : UIModifier
    {
        $mod = $this->ui_modifier;
        $mod->setInternalService($this->service);
        return $mod;
    }

    public function setInternalService(InternalService $service)
    {
        $this->service = $service;
    }
}
