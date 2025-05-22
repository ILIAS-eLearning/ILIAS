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

namespace ILIAS\MetaData\OERHarvester\Services;

use ILIAS\DI\Container as GlobalContainer;
use ILIAS\MetaData\OERHarvester\Settings\Settings;
use ILIAS\MetaData\OERHarvester\Settings\SettingsInterface;
use ILIAS\MetaData\OERHarvester\ResourceStatus\RepositoryInterface as StatusRepository;
use ILIAS\MetaData\OERHarvester\ResourceStatus\DatabaseRepository;

class Services
{
    protected SettingsInterface $settings;
    protected StatusRepository $status_repository;

    protected GlobalContainer $dic;

    public function __construct(GlobalContainer $dic)
    {
        $this->dic = $dic;
    }

    public function settings(): SettingsInterface
    {
        if (isset($this->settings)) {
            return $this->settings;
        }
        return $this->settings = new Settings();
    }

    public function statusRepository(): StatusRepository
    {
        if (isset($this->status_repository)) {
            return $this->status_repository;
        }
        return $this->status_repository = new DatabaseRepository($this->dic->database());
    }
}
