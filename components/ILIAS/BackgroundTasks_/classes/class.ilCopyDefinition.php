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
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\BooleanValue;
use ILIAS\BackgroundTasks\Value;

/**
 * Description of class class
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilCopyDefinition extends AbstractValue
{
    public const COPY_SOURCE_DIR = 'source';
    public const COPY_TARGET_DIR = 'target';
    /**
     * Copy Jobs: source file => relative target file in zip directory.
     * @param string[]
     * @var string[]|array<mixed, array<string, string>>
     */
    private array $copy_definitions = [];
    /**
     * Temporary directory using the normalized title of the bucket.
     */
    private string $temp_dir;
    /**
     * Ref_ids of all selected objects (files as well as folders)
     * @var int[]
     */
    private array $object_ref_ids = [];
    /**
     * Number of files to be downloaded. Required to determine whether there is anything to download or not.
     * @var int
     */
    private int $num_files = 0;
    /**
     * Sum of the size of all files. Required to determine whether the global limit has been violated or not.
     * @var int
     */
    private ?int $sum_file_sizes = 0;
    /**
     * States if the sum of all file sizes adheres to the global limit.
     */
    private ?BooleanValue $adheres_to_limit;

    /**
     * Get copy definitions
     * @return array<string, string>[]|string[]
     */
    public function getCopyDefinitions(): array
    {
        return $this->copy_definitions;
    }

    /**
     * Set copy definitions
     * @param string[] $a_definitions
     */
    public function setCopyDefinitions(array $a_definitions): void
    {
        $this->copy_definitions = $a_definitions;
    }

    /**
     * Get directory name located in /temp/ directory.
     */
    public function getTempDir(): string
    {
        return $this->temp_dir;
    }

    /**
     * Set directory name located in /temp/ directory.
     * @param $temp_dir
     */
    public function setTempDir(string $temp_dir): void
    {
        $this->temp_dir = $temp_dir;
    }

    /**
     * @return int[]
     */
    public function getObjectRefIds(): array
    {
        return $this->object_ref_ids;
    }

    /**
     * @param int[] $object_ref_ids
     */
    public function setObjectRefIds(array $object_ref_ids, bool $append = false): void
    {
        $this->object_ref_ids = $append ? array_merge($this->object_ref_ids, $object_ref_ids) : $object_ref_ids;
    }

    public function getNumFiles(): int
    {
        return $this->num_files;
    }

    public function setNumFiles(int $num_files): void
    {
        $this->num_files = $num_files;
    }

    public function getSumFileSizes(): int
    {
        return $this->sum_file_sizes ?? 0;
    }

    public function setSumFileSizes(int $sum_file_sizes): void
    {
        $this->sum_file_sizes = $sum_file_sizes;
    }

    public function getAdheresToLimit(): BooleanValue
    {
        $fallback = new BooleanValue();
        $fallback->setValue(false);
        return $this->adheres_to_limit ?? $fallback;
    }

    public function setAdheresToLimit(BooleanValue $adheres_to_limit): void
    {
        $this->adheres_to_limit = $adheres_to_limit;
    }

    public function addCopyDefinition(string $a_source, string $a_target): void
    {
        $this->copy_definitions[]
            = [
            self::COPY_SOURCE_DIR => $a_source,
            self::COPY_TARGET_DIR => $a_target,
        ];
    }

    /**
     * Check equality
     */
    public function equals(Value $other): bool
    {
        return strcmp($this->getHash(), $other->getHash());
    }

    /**
     * Get hash
     */
    public function getHash(): string
    {
        return md5($this->serialize());
    }

    /**
     * Serialize content
     * @return string
     */
    public function serialize()
    {
        return serialize(
            [
                "copy_definition" => $this->getCopyDefinitions(),
                "temp_dir" => $this->getTempDir(),
                "object_ref_ids" => implode(",", $this->getObjectRefIds()),
                "num_files" => $this->getNumFiles(),
                "sum_file_sizes" => $this->getSumFileSizes(),
                "adheres_to_limit" => $this->getAdheresToLimit(),
            ]
        );
    }

    /**
     * Set value
     * @param $value
     */
    public function setValue($value): void
    {
        $this->copy_definitions = $value;
    }

    /**
     * Unserialize definitions
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $elements = unserialize($serialized);

        $this->setCopyDefinitions($elements["copy_definition"]);
        $this->setTempDir($elements['temp_dir']);
        $this->setObjectRefIds(explode(",", $elements["object_ref_ids"]));
        $this->setNumFiles($elements["num_files"]);
        $this->setSumFileSizes($elements["sum_file_sizes"]);
        $this->setAdheresToLimit($elements["adheres_to_limit"]);
    }
}
