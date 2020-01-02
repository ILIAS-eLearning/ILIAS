<?php
/**
 * Class ilBiblDataInterface
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

interface ilBiblDataInterface
{
    /**
     * @return integer
     */
    public function getId();


    /**
     * @param integer $id
     */
    public function setId($id);


    /**
     * @return string
     */
    public function getFilename();


    /**
     * @param string $filename
     */
    public function setFilename($filename);


    /**
     * @return integer
     */
    public function getIsOnline();


    /**
     * @param integer $is_online
     */
    public function setIsOnline($is_online);


    /**
     * @return integer
     */
    public function getFileType();


    /**
     * @param integer $file_type
     */
    public function setFileType($file_type);
}
