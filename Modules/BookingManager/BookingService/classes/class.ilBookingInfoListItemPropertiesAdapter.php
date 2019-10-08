<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Get list item properties for booking info
 *
 * @author killing@leifos.de
 * @ingroup ModulesBookingManager
 */
class ilBookingInfoListItemPropertiesAdapter
{
	/**
	 * @var ilBookingReservationDBRepository
	 */
	protected $repo;

	/**
	 * Constructor
	 * @param ilInfoScreenGUI $info_screen_gui
	 */
	public function __construct(ilBookingReservationDBRepository $repo = null)
	{
		$this->repo = $repo;
	}


	/**
	 * Get booking info properties
	 */
	public function appendProperties($obj_id, $props)
	{
		$repo = $this->repo;
		$info = [];
		if ($repo) {
			foreach($repo->getCachedContextObjBookingInfo($obj_id) as $item)
			{
				$info[$item["pool_id"]]["title"] = ilObject::_lookupTitle($item["pool_id"]);
				$info[$item["pool_id"]]["object"][$item["obj_id"]]["title"] = $item["title"];
				$info[$item["pool_id"]]["object"][$item["obj_id"]]["bookings"][] =
					ilDatePresentation::formatDate(new ilDate($item["date"], IL_CAL_DATE)).", ".$item["slot"]." (".$item["counter"].")";
			}
			foreach ($info as $pool) {
				$val = "";
				foreach ($pool["object"] as $o) {
					$val.= $o["title"].": ".implode(", ", $o["bookings"]);
				}
				$props[] = array("alert" => false, "property" => $pool["title"], "value" => $val);
			}
		}
		return $props;
	}
}