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

/**
 * Learning history providers factory
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLearningHistoryProviderFactory
{
    protected ilLearningHistoryService $service;

    protected static array $providers = array(
        ilTrackingLearningHistoryProvider::class,
        ilBadgeLearningHistoryProvider::class,
        ilCourseLearningHistoryProvider::class,
        ilFirstLoginLearningHistoryProvider::class,
        ilCertificateLearningHistoryProvider::class,
        ilSkillLearningHistoryProvider::class
    );

    public function __construct(ilLearningHistoryService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all learning history providers
     * @param bool $active_only get only active providers
     * @param ?int $user_id get instances for user with user id
     * @return ilLearningHistoryProviderInterface[]
     */
    public function getAllProviders(
        bool $active_only = false,
        int $user_id = 0
    ) : array {
        $providers = array();

        if ($user_id === 0) {
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
