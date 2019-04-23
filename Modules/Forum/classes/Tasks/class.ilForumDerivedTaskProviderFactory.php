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

	/**
	 * ilForumDerivedTaskProviderFactory constructor.
	 * @param \ilTaskService $taskService
	 */
	public function __construct(
		\ilTaskService $taskService,
		\ilAccess $accessHandler = null,
		\ilSetting $settings = null
	) {
		global $DIC;

		$this->accessHandler = is_null($accessHandler)
			? $DIC->access()
			: $accessHandler;

		$this->lng = is_null($settings)
			? $DIC->language()
			: $lng;

		$this->taskService = $taskService;
		$this->accessHandler = $accessHandler;
		$this->settings = $settings;
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
				$this->settings
			)
		];
	}
}