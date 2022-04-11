<?php
/**
 * Class ilBiblDataFactoryInterface
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

interface ilBiblDataFactoryInterface
{
    public function getIlBiblDataById(int $id) : ?ilBiblData;
}
