<?php
/**
 * Class ilBiblDataFactory
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblDataFactory implements ilBiblDataFactoryInterface
{
    public function getIlBiblDataById($id)
    {
        return ilBiblData::where([ "id" => $id])->first();
    }
}
