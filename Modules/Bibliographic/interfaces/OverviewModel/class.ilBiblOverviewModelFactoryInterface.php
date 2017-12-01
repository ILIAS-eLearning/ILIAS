<?php
/**
 * Class ilBiblOverviewModelFactoryInterface
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

interface ilBiblOverviewModelFactoryInterface {

	/**
	 * @return \ilBiblOverviewModelInterface[]
	 */
	public function getAllOverviewModels();


	/**
	 * @param ilBiblEntryInterface $entry
	 */
	public function initOverviewHTML(ilBiblEntryInterface $entry);

}