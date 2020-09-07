<?php
/**
 * Class ilBiblDataFactoryInterface
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

interface ilBiblDataFactoryInterface
{

    /**
     * @param integer $id
     *
     * @return array ilBiblData Record
     */
    public function getIlBiblDataById($id);
}
