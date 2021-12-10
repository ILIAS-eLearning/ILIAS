<?php
declare(strict_types = 1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Survey\Mode;

use ILIAS\Survey\InternalDomainService;
use ILIAS\Survey\InternalUIService;
use ILIAS\Survey\InternalService;

/**
 * Interface for modes
 * @author Alexander Killing <killing@leifos.de>
 */
interface ModeProvider
{
    public function setInternalService(InternalService $service);

    public function getId() : int;

    public function getFeatureConfig() : FeatureConfig;

    public function getUIModifier() : UIModifier;
}
