<?php
/**
 * Class ilBiblOverviewModelFactory
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblOverviewModelFactory implements ilBiblOverviewModelFactoryInterface {

	/**
	 * @deprecated REFACTOR use active record. Create ilBiblOverviewModel AR, Factory and Interface
	 *
	 * @return array
	 */
	public function getAllOverviewModels() {
		global $DIC;
		$ilDB = $DIC['ilDB'];
		$overviewModels = ilBiblOverviewModel::get();

		$overviewModels = array();
		$set = $ilDB->query('SELECT * FROM il_bibl_overview_model');
		while ($rec = $ilDB->fetchAssoc($set)) {
			if ($rec['literature_type']) {
				$overviewModels[$rec['filetype']][$rec['literature_type']] = $rec['pattern'];
			} else {
				$overviewModels[$rec['filetype']] = $rec['pattern'];
			}
		}
		return $overviewModels;
	}

	/**
	 * @inheritDoc
	 */
	public function initOverviewHTML(ilBiblEntryInterface $entry) {
		$ilBiblOverviewGUI = new ilBiblEntryTablePresentationGUI($entry);
		$this->setOverview($ilBiblOverviewGUI->getHtml());
	}

}