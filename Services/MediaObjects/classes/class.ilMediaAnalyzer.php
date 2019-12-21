<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* Analyzes media files. Wrapper for getid3 library.
*
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesMediaObjects
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
