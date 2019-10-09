<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * User settings for workspace folders
 *
 * @author killing@leifos.de
 */
class ilWorkspaceFolderUserSettings
{
	/**
	 * @var ilTree
	 */
	protected $tree;

	/**
	 * @var int
	 */
	protected $user_id;

	/**
	 * @var ilWorkspaceFolderUserSettingsRepository
	 */
	protected $repo;

	/**
	 * Constructor
	 */
	public function __construct(int $user_id, ilWorkspaceFolderUserSettingsRepository $repo, ilWorkspaceTree $tree = null)
	{
		$this->repo = $repo;
		$this->user_id = $user_id;
		$this->tree = ($tree != null)
			? $tree
			: new ilWorkspaceTree($user_id);
	}

	/**
	 * Get Sortation of workspace folder
	 * @param int $wfld_id folder object id
	 * @return int
	 */
	public function getSortation(int $wfld_id): int
	{
		$sort = $this->repo->getSortation($wfld_id);
		if ($sort > 0) {
			return $sort;
		}
		if (ilObject::_lookupType($wfld_id) == "wfld") {
			return ilWorkspaceFolderSorting::SORT_DERIVED;
		}
		return ilWorkspaceFolderSorting::SORT_ALPHABETICAL_ASC;
	}

	/**
	 * Update sortation for workspace folder
	 * @param int $wfld_id folder object id
	 * @param int $sortation
	 */
	public function updateSortation(int $wfld_id, int $sortation)
	{
		$this->repo->updateSortation($wfld_id, $sortation);
	}

	/**
	 * Get effective sortation for a workspace folder (next upper
	 * context that has sortation > 0)
	 * @param int $wfld_wsp_id
	 * @return int|mixed
	 */
	public function getEffectiveSortation(int $wfld_wsp_id)
	{
		$tree = $this->tree;

		// get path
		$path = $tree->getPathId($wfld_wsp_id);
		// get object ids of path
		$obj_ids = array_map(function($wsp_id) use ($tree) {
			return $tree->lookupObjectId($wsp_id);
		}, $path);

		// get sortations for object ids
		$sortations = $this->repo->getSortationMultiple($obj_ids);

		// search bottom to top first one with sortation > 0
		foreach (array_reverse($obj_ids) as $id)
		{
			if ($sortations[$id] > 0)
			{
				return $sortations[$id];
			}
		}
		return ilWorkspaceFolderSorting::SORT_ALPHABETICAL_ASC;
	}

}