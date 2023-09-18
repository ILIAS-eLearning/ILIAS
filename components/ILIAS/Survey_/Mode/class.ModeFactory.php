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

use ILIAS\Survey\InternalDomainService;
use ILIAS\Survey\InternalGUIService;
use ILIAS\Survey\InternalService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ModeFactory
{
    /** @var \ILIAS\Survey\Mode\ModeProvider[] */
    protected ?array $providers = null;
    protected InternalService $service;

    public function __construct()
    {
    }

    public function setInternalService(InternalService $service): void
    {
        $this->service = $service;
    }
    protected function initProviders(): void
    {
        if (is_null($this->providers)) {
            $gui = $this->service->gui();
            $this->providers = [
                new Standard\ModeProvider($gui),
                new Feedback360\ModeProvider($gui),
                new SelfEvaluation\ModeProvider($gui),
                new IndividualFeedback\ModeProvider($gui)
            ];
        }
    }

    public function getModeById(int $id): ModeProvider
    {
        $this->initProviders();
        foreach ($this->providers as $provider) {
            if ($provider->getId() === $id) {
                $provider->setInternalService($this->service);
                return $provider;
            }
        }
        throw new ModeException("Unknown mode: " . $id);
    }
}
