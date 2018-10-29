<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateMigrationValidator
{
	/**
	 * @var ilSetting
	 */
	private $certificateSettings;

	/**
	 * @param ilSetting $certificateSettings
	 */
	public function __construct(ilSetting $certificateSettings)
	{
		$this->certificateSettings = $certificateSettings;
	}

	/**
	 * @param ilObjUser $user
	 * @return bool
	 */
	public function isMigrationAvailable(ilObjUser $user) : bool
	{
		$userCreationDate = $user->getCreateDate();

		$showMigrationBox = false;
		if (null !== $userCreationDate) {
			$userCreatedTimestamp = strtotime($userCreationDate);
			$introducedTimestamp = $this->certificateSettings->get('persisting_cers_introduced_ts', 0);

			if ($userCreatedTimestamp < $introducedTimestamp) {
				$showMigrationBox = true;
			}
		}

		return $showMigrationBox;
	}
}
