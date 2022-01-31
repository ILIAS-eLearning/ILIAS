<?php
/**
 * Class ilBiblOverviewModelFactoryInterface
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

interface ilBiblOverviewModelFactoryInterface
{
    /**
     * @return ilBiblOverviewModelInterface[]
     */
    public function getAllOverviewModelsByType(ilBiblTypeInterface $type) : array;
}
