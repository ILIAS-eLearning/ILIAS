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
	 * @param ilBiblTypeInterface $type
	 *
	 * @return ilBiblOverviewModelInterface
	 */
	public function getAllOverviewModelsByType(ilBiblTypeInterface $type);
}