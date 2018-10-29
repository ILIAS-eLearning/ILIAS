<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateMigrationValidator
{
	/** @var \ilObjUser */
	private $user;

	/** @var \ilSetting */
	private $certificateSettings;

	/** @var \ilCertificateMigration */
	private $migrationHelper;

	/**
	 * @param \ilObjUser $user
	 * @param \ilSetting $certificateSettings
	 * @param \ilCertificateMigration $migrationHelper
	 */
	public function __construct(\ilObjUser $user, ilSetting $certificateSettings, \ilCertificateMigration $migrationHelper)
	{
		$this->user = $user;
		$this->certificateSettings = $certificateSettings;
		$this->migrationHelper = $migrationHelper;
	}

	/**
	 * @return bool
	 */
	public function isMigrationAvailable(): bool
	{
		if (!$this->areCertificatesGloballyEnabled()) {
			return false;
		}

		if ($this->isMigrationFinishedForUser($this->user)) {
			return false;
		}

		if ($this->migrationHelper->isTaskRunning() || $this->migrationHelper->isTaskFinished()) {
			return false;
		}

		$isUserCreatedAfterFeatureIntroduction = $this->isUserCreatedAfterFeatureIntroduction($this->user);

		return $isUserCreatedAfterFeatureIntroduction;
	}

	/**
	 * @return bool
	 */
	protected function areCertificatesGloballyEnabled(): bool
	{
		$certificatesGloballyEnabled = \ilCertificate::isActive();

		return $certificatesGloballyEnabled;
	}

	/**
	 * @param ilObjUser $user
	 * @return bool
	 */
	protected function isMigrationFinishedForUser(\ilObjUser $user): bool
	{
		 $migrationFinished = $user->getPref('cert_migr_finished') == 1;

		 return $migrationFinished;
	}

	/**
	 * @param \ilObjUser $user
	 * @return bool
	 */
	protected function isUserCreatedAfterFeatureIntroduction(\ilObjUser $user): bool
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
