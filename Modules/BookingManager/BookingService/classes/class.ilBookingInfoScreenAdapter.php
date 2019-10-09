<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Embeds booking information into info screen
 *
 * @author killing@leifos.de
 * @ingroup ModulesBookingManager
 */
class ilBookingInfoScreenAdapter
{
	/**
	 * @var ilInfoScreenGUI
	 */
	protected $info_screen_gui;

	/**
	 * @var int|null
	 */
	protected $context_obj_id;

	/**
	 * @var ilObjUseBookDBRepository
	 */
	protected $use_book_repo;

	/**
	 * Constructor
	 * @param ilInfoScreenGUI $info_screen_gui
	 */
	public function __construct(ilInfoScreenGUI $info_screen_gui)
	{
		global $DIC;
		$this->info_screen_gui = $info_screen_gui;
		$this->context_obj_id = $this->info_screen_gui->getContextObjId();

		$this->use_book_repo = new ilObjUseBookDBRepository($DIC->database());
	}

	/**
	 * Get pool ids
	 *
	 * @return int[]
	 */
	protected function getPoolIds()
	{
		$pool_ids = array_map( function($ref_id) {
			return ilObject::_lookupObjId($ref_id);
		}, $this->use_book_repo->getUsedBookingPools($this->context_obj_id));
		return $pool_ids;
	}

	/**
	 * Get list
	 * @return array[]
	 */
	protected function getList(): array
	{
		$filter = ["context_obj_ids" => [$this->context_obj_id]];
		$filter['past'] = true;
		$filter['status'] = -ilBookingReservation::STATUS_CANCELLED;
		$f = new ilBookingReservationDBRepositoryFactory();
		$repo = $f->getRepo();
		$list = $repo->getListByDate(true, null, $filter, $this->getPoolIds());
		$list = ilUtil::sortArray($list, "slot", "asc", true);
		$list = ilUtil::stableSortArray($list, "date", "asc", true);
		$list = ilUtil::stableSortArray($list, "pool_id", "asc", true);
		return $list;
	}


	/**
	 * Add info
	 */
	public function add()
	{
		$info = $this->info_screen_gui;
		$current_pool_id = 0;

		foreach ($this->getList() as $item)
		{
			// headings (pool title)
			if ($current_pool_id != $item["pool_id"])
			{
				$info->addSection(ilObject::_lookupTitle($item["pool_id"]));
			}
			// booking object
			$info->addProperty($item["title"]." (".$item["counter"].")",
				ilDatePresentation::formatDate(new ilDate($item["date"], IL_CAL_DATE)).", ".$item["slot"]);
			$current_pool_id = $item["pool_id"];
		}
	}
}