<?php declare(strict_types=1);

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

use ILIAS\Setup;

class ilUtilitiesSetupConfig implements Setup\Config
{
    protected string $path_to_convert;
    protected string $path_to_zip;
    protected string $path_to_unzip;
    
    public function __construct(
        string $path_to_convert,
        string $path_to_zip,
        string $path_to_unzip
    ) {
        $this->path_to_convert = $this->toLinuxConvention($path_to_convert);
        $this->path_to_zip = $this->toLinuxConvention($path_to_zip);
        $this->path_to_unzip = $this->toLinuxConvention($path_to_unzip);
    }
    
    protected function toLinuxConvention(?string $p) : ?string
    {
        if (!$p) {
            return null;
        }
        return preg_replace("/\\\\/", "/", $p);
    }
    
    public function getPathToConvert() : string
    {
        return $this->path_to_convert;
    }
    
    public function getPathToZip() : string
    {
        return $this->path_to_zip;
    }
    
    public function getPathToUnzip() : string
    {
        return $this->path_to_unzip;
    }
}
