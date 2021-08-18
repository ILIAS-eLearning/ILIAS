<?php

namespace ILIAS\Calendar\FileHandler;

/**
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * Class FileProperty
 */
class ilFileProperty
{
    /**
     * @var string
     */
    private $absolute_path;


    /**
     * @var string
     */
    private $file_name;

    public function __construct()
    {

    }

    /**
     * @return string
     */
    public function getAbsolutePath() : string
    {
        return $this->absolute_path;
    }

    /**
     * @param string $absolute_path
     */
    public function setAbsolutePath(string $absolute_path) : void
    {
        $this->absolute_path = $absolute_path;
    }

    /**
     * @return string
     */
    public function getFileName() : string
    {
        return $this->file_name;
    }

    /**
     * @param string $file_name
     */
    public function setFileName(string $file_name) : void
    {
        $this->file_name = $file_name;
    }
}