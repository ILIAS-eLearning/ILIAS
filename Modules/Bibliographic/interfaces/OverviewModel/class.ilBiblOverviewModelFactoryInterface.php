<?php
/**
 * Class ilBiblOverviewModelFactoryInterface
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

interface ilBiblOverviewModelFactoryInterface
{

    /**
     * @param ilBiblTypeInterface $type
     *
     * @return ilBiblOverviewModelInterface
     */
    public function getAllOverviewModelsByType(ilBiblTypeInterface $type);
}
