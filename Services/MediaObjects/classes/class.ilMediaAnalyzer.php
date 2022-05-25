<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Analyzes media files. Wrapper for getid3 library.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilMediaAnalyzer
{
    protected array $file_info;
    protected getID3 $getid3;
    public string $file;

    public function __construct()
    {
        $this->getid3 = new getID3();
    }

    /**
     * Set Full File Path.
     */
    public function setFile(string $a_file) : void
    {
        $this->file = $a_file;
    }

    public function getFile() : string
    {
        return $this->file;
    }
    
    /**
     * Get PlaytimeString.
     */
    public function getPlaytimeString() : string
    {
        return $this->file_info["playtime_string"];
    }

    public function getPlaytimeSeconds() : int
    {
        return $this->file_info["playtime_seconds"] ?? 0;
    }

    /**
     * Analyze current file.
     */
    public function analyzeFile() : void
    {
        $this->file_info = $this->getid3->analyze($this->getFile());
    }
}
