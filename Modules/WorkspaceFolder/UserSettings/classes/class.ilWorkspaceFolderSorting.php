<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author killing@leifos.de
 * @ingroup
 */
class ilWorkspaceFolderSorting
{
	const SORT_DERIVED = 0;
	const SORT_ALPHABETICAL_ASC = 1;
	const SORT_ALPHABETICAL_DESC = 2;
	const SORT_CREATION_ASC = 3;
	const SORT_CREATION_DESC = 4;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * Constructor
	 */
	public function __construct(ilLanguage $lng = null)
	{
		global $DIC;

		$this->lng = ($lng != null)
			? $lng
			: $DIC->language();

		$this->lng->loadLanguageModule("wfld");
	}

	/**
	 * Get options by type
	 *
	 * @param $wsp_type
	 * @param $selected
	 * @param $parent_effective
	 * @return array
	 */
	public function getOptionsByType($wsp_type, $selected, $parent_effective)
	{
		$sort_options = ($wsp_type == "wfld")
			? [self::SORT_DERIVED => $this->lng->txt("wfld_derive")]
			: [];
		if (in_array($wsp_type, ["wfld", "wsrt"])) {
			$sort_options[self::SORT_ALPHABETICAL_ASC] = $this->getLabel(self::SORT_ALPHABETICAL_ASC);
			$sort_options[self::SORT_ALPHABETICAL_DESC] = $this->getLabel(self::SORT_ALPHABETICAL_DESC);
			$sort_options[self::SORT_CREATION_ASC] = $this->getLabel(self::SORT_CREATION_ASC);
			$sort_options[self::SORT_CREATION_DESC] = $this->getLabel(self::SORT_CREATION_DESC);
		}

		if (isset($sort_options[self::SORT_DERIVED])) {
			$sort_options[self::SORT_DERIVED].= " (".$this->getLabel($parent_effective).")";
		}

		if (isset($sort_options[$selected])) {
			$sort_options[$selected] = "<strong>".$sort_options[$selected]."</strong>";
		}
		return $sort_options;
	}

	/**
	 * Get label
	 *
	 * @param int $option
	 * @return string
	 */
	protected function getLabel(int $option)
	{
		switch ($option)
		{
			case self::SORT_DERIVED: return $this->lng->txt("wfld_derive");
			case self::SORT_ALPHABETICAL_ASC: return $this->lng->txt("wfld_alphabetically_asc");
			case self::SORT_ALPHABETICAL_DESC: return $this->lng->txt("wfld_alphabetically_desc");
			case self::SORT_CREATION_ASC: return $this->lng->txt("wfld_creation_asc");
			case self::SORT_CREATION_DESC: return $this->lng->txt("wfld_creation_desc");
		}
		return "";
	}

	/**
	 * Sort nodes
	 *
	 * @param $nodes
	 * @param $sorting
	 * @return array
	 */
	public function sortNodes($nodes, $sorting)
	{
		switch ($sorting)
		{
			case self::SORT_ALPHABETICAL_ASC:
				$nodes = ilUtil::sortArray($nodes, "title", "asc");
				break;
			case self::SORT_ALPHABETICAL_DESC:
				$nodes = ilUtil::sortArray($nodes, "title", "desc");
				break;

			case self::SORT_CREATION_ASC:
				$nodes = ilUtil::sortArray($nodes, "create_date", "asc");
				break;

			case self::SORT_CREATION_DESC:
				$nodes = ilUtil::sortArray($nodes, "create_date", "desc");
				break;

		}
		return $nodes;
	}



}