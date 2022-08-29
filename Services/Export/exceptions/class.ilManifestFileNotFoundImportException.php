<?php

declare(strict_types=1);

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

    public function getManifestDir(): string
    {
        return $this->manifest_dir;
    }

    public function setTmpDir(string $a_val): void
    {
        $this->tmp_dir = $a_val;
    }

    public function getTmpDir(): string
    {
        return $this->tmp_dir;
    }
}
