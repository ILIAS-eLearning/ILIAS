<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumDraftsDerivedTaskProvider
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilForumDraftsDerivedTaskProvider implements \ilDerivedTaskProvider
{
	/** @var ilTaskService */
	protected $taskService;

	/** @var \ilAccess */
	protected $accessHandler;

	/** @var \ilSetting */
	protected $settings;

	/**
	 * ilForumDraftsDerivedTaskProvider constructor.
	 * @param ilTaskService $taskService
	 * @param ilAccessHandler $accessHandler
	 * @param ilSetting $settings
	 */
	public function __construct(ilTaskService $taskService, \ilAccessHandler $accessHandler, \ilSetting $settings)
	{
		$this->taskService = $taskService;
		$this->accessHandler = $accessHandler;
		$this->settings = $settings;
	}

	/**
	 * @inheritDoc
	 */
	public function getTasks(int $user_id): array
	{
		$tasks = [];

		$drafts = \ilForumPostDraft::getDraftInstancesByUserId($user_id);
		foreach ($drafts as $draft) {
			$objId = ilForum::_lookupObjIdForForumId($draft->getForumId());
			$refId = $this->getFirstRefIdWithPermission('read', $objId, $user_id);

			if (0 === $refId) {
				continue;
			}

			$task = $this->taskService->derived()->factory()->task(
				$draft->getPostSubject(),
				$refId,
				0,
				strtotime($draft->getPostDate())
			);

			$tasks[] = $task;
		}

		return $tasks;
	}

	/**
	 * @param string $operation
	 * @param int $objId
	 * @param int $userId
	 * @return int
	 */
	protected function getFirstRefIdWithPermission(string $operation, int $objId, int $userId): int
	{
		foreach (\ilObject::_getAllReferences($objId) as $refId) {
			if ($this->accessHandler->checkAccessOfUser($userId, $operation, '', $refId)) {
				return $refId;
			}
		}

		return 0;
	}

	/**
	 * @inheritDoc
	 */
	public function isActive(): bool
	{
		return (bool)$this->settings->get('save_post_drafts', false);
	}
}