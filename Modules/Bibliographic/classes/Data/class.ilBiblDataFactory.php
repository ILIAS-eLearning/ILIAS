<?php
/**
 * Class ilBiblDataFactory
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblDataFactory implements ilBiblDataFactoryInterface
{
    
    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getIlBiblDataById(int $id) : ?ilBiblData
    {
        return ilBiblData::where(["id" => $id])->first();
    }
}
