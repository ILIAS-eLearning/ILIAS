<?php

declare(strict_types=1);

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

namespace ILIAS\Survey\Mode;

use ILIAS\Survey\InternalService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
trait ModeProviderBase
{
    protected ?int $id = null;
    protected FeatureConfig $feature_config;
    protected InternalService $service;
    protected UIModifier $ui_modifier;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFeatureConfig(): FeatureConfig
    {
        return $this->feature_config;
    }

    public function getUIModifier(): UIModifier
    {
        $mod = $this->ui_modifier;
        $mod->setInternalService($this->service);
        return $mod;
    }

    public function setInternalService(InternalService $service): void
    {
        $this->service = $service;
    }
}
