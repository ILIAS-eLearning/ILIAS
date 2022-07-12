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

use ILIAS\BackgroundTasks\Implementation\Values\AbstractValue;
use ILIAS\BackgroundTasks\Value;

/**
 * Copy definition for workspace folders
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWorkspaceCopyDefinition extends AbstractValue
{
    public const COPY_SOURCE_DIR = 'source';
    public const COPY_TARGET_DIR = 'target';

    /**
     * Copy Jobs: source file => relative target file in zip directory.
     * @param string[]
     */
    private array $copy_definitions = [];
    private string $temp_dir;
    private array $object_wsp_ids = [];
    private int $num_files = 0;
    private int $sum_file_sizes = 0;
    private bool $adheres_to_limit = false;

    public function getCopyDefinitions() : array
    {
        return $this->copy_definitions;
    }

    /**
     * Set copy definitions
     * @param string[] $a_definitions
     */
    public function setCopyDefinitions(array $a_definitions) : void
    {
        $this->copy_definitions = $a_definitions;
    }

    public function getTempDir() : string
    {
        return $this->temp_dir;
    }

    /**
     * Set directory name located in /temp/ directory.
     */
    public function setTempDir(string $temp_dir) : void
    {
        $this->temp_dir = $temp_dir;
    }

    /**
     * @return string[]
     */
    public function getObjectWspIds() : array
    {
        return $this->object_wsp_ids;
    }

    public function setObjectWspIds(
        array $object_wps_ids,
        bool $append = false
    ) : void {
        if ($append) {
            $this->object_wsp_ids = array_merge($this->object_wsp_ids, $object_wps_ids);
        } else {
            $this->object_wsp_ids = $object_wps_ids;
        }
    }

    public function getNumFiles() : int
    {
        return $this->num_files;
    }

    public function setNumFiles(int $num_files) : void
    {
        $this->num_files = $num_files;
    }

    public function getSumFileSizes() : int
    {
        return $this->sum_file_sizes;
    }

    public function setSumFileSizes(int $sum_file_sizes) : void
    {
        $this->sum_file_sizes = $sum_file_sizes;
    }

    public function getAdheresToLimit() : bool
    {
        return $this->adheres_to_limit;
    }

    public function setAdheresToLimit(bool $adheres_to_limit) : void
    {
        $this->adheres_to_limit = $adheres_to_limit;
    }

    public function addCopyDefinition(string $a_source, string $a_target) : void
    {
        $this->copy_definitions[] =
            [
                self::COPY_SOURCE_DIR => $a_source,
                self::COPY_TARGET_DIR => $a_target
            ];
    }

    public function equals(Value $other) : bool
    {
        return strcmp($this->getHash(), $other->getHash());
    }

    public function getHash() : string
    {
        return md5($this->serialize());
    }

    public function serialize() : string
    {
        return serialize(
            [
                "copy_definition" => $this->getCopyDefinitions(),
                "temp_dir" => $this->getTempDir(),
                "object_wsp_ids" => implode(",", $this->getObjectWspIds()),
                "num_files" => $this->getNumFiles(),
                "sum_file_sizes" => $this->getSumFileSizes(),
                "adheres_to_limit" => $this->getAdheresToLimit()
            ]
        );
    }

    /**
     * Set value
     * @param $value
     */
    public function setValue($value) : void
    {
        $this->copy_definitions = $value;
    }

    /**
     * Unserialize definitions
     * @param string $data
     */
    public function unserialize($data)
    {
        $elements = unserialize($data);

        $this->setCopyDefinitions($elements["copy_definition"]);
        $this->setTempDir($elements['temp_dir']);
        $this->setObjectWspIds(explode(",", $elements["object_wsp_ids"]));
        $this->setNumFiles($elements["num_files"]);
        $this->setSumFileSizes($elements["sum_file_sizes"]);
        $this->setAdheresToLimit($elements["adheres_to_limit"]);
    }
}
