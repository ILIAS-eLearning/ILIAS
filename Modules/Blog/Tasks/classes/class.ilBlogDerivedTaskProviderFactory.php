<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBlogDerivedTaskProviderFactory
 * @author Thomas Famula <famula@leifos.de>
 */
class ilBlogDerivedTaskProviderFactory implements ilDerivedTaskProviderFactory
{
	/** @var ilTaskService */
	protected $taskService;

	/** @var \ilAccess */
	protected $accessHandler;

	/** @var \ilLanguage */
	protected $lng;

	/**
	 * ilBlogDerivedTaskProviderFactory constructor.
	 * @param ilTaskService $taskService
	 * @param \ilAccess|null $accessHandler
	 * @param \ilLanguage|null $lng
	 */
	public function __construct(
		ilTaskService $taskService,
		\ilAccess $accessHandler = null,
		\ilLanguage $lng = null
	) {
		global $DIC;

		$this->taskService = $taskService;

		$this->accessHandler = is_null($accessHandler)
			? $DIC->access()
			: $accessHandler;

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
			new ilBlogDraftsDerivedTaskProvider(
				$this->taskService,
				$this->accessHandler,
				$this->lng
			)
		];
	}
}