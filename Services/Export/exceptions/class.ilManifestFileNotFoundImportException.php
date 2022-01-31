<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * manifest.xml file not found-exception for import
 * @author Alexander Killing <killing@leifos.de>
 */
class ilManifestFileNotFoundImportException extends ilImportException
{
    private string $manifest_dir = "";
    private string $tmp_dir = "";

    public function setManifestDir($a_val)
    {
        $this->manifest_dir = $a_val;
    }

    public function getManifestDir() : string
    {
        return $this->manifest_dir;
    }

    public function setTmpDir(string $a_val) : void
    {
        $this->tmp_dir = $a_val;
    }

    public function getTmpDir() : string
    {
        return $this->tmp_dir;
    }
}
