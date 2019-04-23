<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumDerivedTaskProviderFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilForumDerivedTaskProviderFactory implements \ilDerivedTaskProviderFactory
{
	/** @var ilTaskService */
	protected $taskService;

	/** @var \ilAccess */
	protected $accessHandler;

	/** @var \ilSetting */
	protected $settings;

	/** @var \ilLanguage */
	protected $lng;

	/**
	 * ilForumDerivedTaskProviderFactory constructor.
	 * @param \ilTaskService $taskService
	 * @param \ilAccess|null $accessHandler
	 * @param \ilSetting|null $settings
	 * @param \ilLanguage|null $lng
	 */
	public function __construct(
		\ilTaskService $taskService,
		\ilAccess $accessHandler = null,
		\ilSetting $settings = null,
		\ilLanguage $lng = null
	) {
		global $DIC;

		$this->taskService = $taskService;
		$this->accessHandler = is_null($accessHandler)
			? $DIC->access()
			: $accessHandler;

		$this->settings = is_null($settings)
			? $DIC->settings()
			: $settings;

		$this->lng = is_null($lng)
			? $DIC->language()
			: $lng;
	}

	/**
	 * @inheritdoc
	 */
	public function getProviders(): array
	{
		return [
			new \ilForumDraftsDerivedTaskProvider(
				$this->taskService,
				$this->accessHandler,
				$this->lng,
				$this->settings
			)
		];
	}
}