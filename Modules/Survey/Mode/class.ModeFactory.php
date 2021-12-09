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
class ModeFactory
{
    protected $providers;

    /**
     * @var InternalService
     */
    protected $service;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->providers = [
            new Standard\ModeProvider(),
            new Feedback360\ModeProvider(),
            new SelfEvaluation\ModeProvider(),
            new IndividualFeedback\ModeProvider()
        ];
    }

    public function setInternalService(InternalService $service)
    {
        $this->service = $service;
    }

    public function getModeById(int $id) : ModeProvider
    {
        foreach ($this->providers as $provider) {
            if ($provider->getId() == $id) {
                $provider->setInternalService($this->service);
                return $provider;
            }
        }
        throw new ModeException("Unknown mode: " . $id);
    }
}
