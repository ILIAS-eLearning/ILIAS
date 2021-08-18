<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Analyzes media files. Wrapper for getid3 library.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilMediaAnalyzer
{
    public $file;

    public function __construct()
    {
        $this->getid3 = new getID3();
    }

    /**
    * Set Full File Path.
    *
    * @param	string	$a_file	Full File Path
    */
    public function setFile($a_file)
    {
        $this->file = $a_file;
    }

    /**
    * Get Full File Path.
    *
    * @return	string	Full File Path
    */
    public function getFile()
    {
        return $this->file;
    }
    
    /**
    * Get PlaytimeString.
    *
    * @return	string	PlaytimeString
    */
    public function getPlaytimeString()
    {
        return $this->file_info["playtime_string"];
    }

    /**
    * Get PlaytimeSeconds.
    *
    * @return	double	PlaytimeSeconds
    */
    public function getPlaytimeSeconds()
    {
        return $this->file_info["playtime_seconds"];
    }

    /**
    * Analyze current file.
    */
    public function analyzeFile()
    {
        $this->file_info = $this->getid3->analyze($this->getFile());
    }
}
