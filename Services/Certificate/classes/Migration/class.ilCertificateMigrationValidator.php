<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateMigrationValidator
{
    /** @var \ilSetting */
    private $certificateSettings;

    /**
     * @param \ilSetting $certificateSettings
     */
    public function __construct(\ilSetting $certificateSettings)
    {
        $this->certificateSettings = $certificateSettings;
    }

    /**
     * @param \ilObjUser $user
     * @param \ilCertificateMigration $migrationHelper
     * @return bool
     */
    public function isMigrationAvailable(\ilObjUser $user, \ilCertificateMigration $migrationHelper) : bool
    {
        if (!$this->areCertificatesGloballyEnabled()) {
            return false;
        }

        if ($this->isMigrationFinishedForUser($user)) {
            return false;
        }

        if ($migrationHelper->isTaskRunning() || $migrationHelper->isTaskFinished()) {
            return false;
        }

        $isUserCreatedAfterFeatureIntroduction = $this->isUserCreatedAfterFeatureIntroduction($user);

        return $isUserCreatedAfterFeatureIntroduction;
    }

    /**
     * @return bool
     */
    protected function areCertificatesGloballyEnabled() : bool
    {
        $certificatesGloballyEnabled = (bool) $this->certificateSettings->get('active', false);

        return $certificatesGloballyEnabled;
    }

    /**
     * @param ilObjUser $user
     * @return bool
     */
    protected function isMigrationFinishedForUser(\ilObjUser $user) : bool
    {
        $migrationFinished = $user->getPref('cert_migr_finished') == 1;

        return $migrationFinished;
    }

    /**
     * @param \ilObjUser $user
     * @return bool
     */
    protected function isUserCreatedAfterFeatureIntroduction(\ilObjUser $user) : bool
    {
        $createdBeforeFeatureIntroduction = false;

        $userCreationDate = $user->getCreateDate();
        if (null !== $userCreationDate) {
            $userCreatedTimestamp = strtotime($userCreationDate);
            $introducedTimestamp = $this->certificateSettings->get('persisting_cers_introduced_ts', 0);

            if ($userCreatedTimestamp < $introducedTimestamp) {
                $createdBeforeFeatureIntroduction = true;
            }
        }

        return $createdBeforeFeatureIntroduction;
    }
}
