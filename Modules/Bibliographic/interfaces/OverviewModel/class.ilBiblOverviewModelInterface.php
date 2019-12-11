<?php
/**
 * Class ilBiblOverviewModelInterface
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

interface ilBiblOverviewModelInterface
{

    /**
     * @return int
     */
    public function getOvmId();


    /**
     * @param int $ovm_id
     */
    public function setOvmId($ovm_id);


    /**
     * @return int
     */
    public function getFileTypeId();


    /**
     * @param int $file_type
     */
    public function setFileTypeId($file_type);


    /**
     * @return string
     */
    public function getLiteratureType();


    /**
     * @param string $literature_type
     */
    public function setLiteratureType($literature_type);


    /**
     * @return string
     */
    public function getPattern();


    /**
     * @param string $pattern
     */
    public function setPattern($pattern);
}
