<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Learning history providers factory
 *
 * @author killing@leifos.de
 * @ingroup ServicesLearningHistory
 */
class ilLearningHistoryProviderFactory
{
    /**
     * @var ilLearningHistoryService
     */
    protected $service;

    /**
     * @var array
     */
    protected static $providers = array(
        ilTrackingLearningHistoryProvider::class,
        ilBadgeLearningHistoryProvider::class,
        ilCourseLearningHistoryProvider::class,
        ilFirstLoginLearningHistoryProvider::class,
        ilCertificateLearningHistoryProvider::class,
        ilSkillLearningHistoryProvider::class
    );

    /**
     * Constructor
     */
    public function __construct($service)
    {
        $this->service = $service;
    }

    /**
     * Get all learning history providers
     *
     * @param bool $active_only get only active providers
     * @param int $user_id get instances for user with user id
     * @return ilLearningHistoryProviderInterface[]
     */
    public function getAllProviders($active_only = false, $user_id = null)
    {
        $providers = array();

        if ($user_id == 0) {
            $user_id = $this->service->user()->getId();
        }

        foreach (self::$providers as $provider) {
            /** @var ilLearningHistoryProviderInterface $provider */
            $providerInstance = new $provider($user_id, $this->service->factory(), $this->service->language());
            if (!$active_only || $providerInstance->isActive()) {
                $providers[] = $providerInstance;
            }
        }

        return $providers;
    }
}
