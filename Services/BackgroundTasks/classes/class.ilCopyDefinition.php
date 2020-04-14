<?php

use ILIAS\BackgroundTasks\Implementation\Values\AbstractValue;
use ILIAS\BackgroundTasks\Value;

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilCopyDefinition extends AbstractValue
{
    const COPY_SOURCE_DIR = 'source';
    const COPY_TARGET_DIR = 'target';
    /**
     * Copy Jobs: source file => relative target file in zip directory.
     *
     * @param string[]
     */
    private $copy_definitions = [];
    /**
     * Temporary directory using the normalized title of the bucket.
     *
     * @var string
     */
    private $temp_dir;
    /**
     * Ref_ids of all selected objects (files as well as folders)
     *
     * @var string[]
     */
    private $object_ref_ids = [];
    /**
     * Number of files to be downloaded. Required to determine whether there is anything to download or not.
     *
     * @var int
     */
    private $num_files = 0;
    /**
     * Sum of the size of all files. Required to determine whether the global limit has been violated or not.
     *
     * @var int
     */
    private $sum_file_sizes = 0;
    /**
     * States if the sum of all file sizes adheres to the global limit.
     *
     * @var bool
     */
    private $adheres_to_limit = false;


    /**
     * Get copy definitions
     *
     * @return string[]
     */
    public function getCopyDefinitions()
    {
        return $this->copy_definitions;
    }


    /**
     * Set copy definitions
     *
     * @param string[] $a_definitions
     */
    public function setCopyDefinitions($a_definitions)
    {
        $this->copy_definitions = $a_definitions;
    }


    /**
     * Get directory name located in /temp/ directory.
     *
     * @return string
     */
    public function getTempDir()
    {
        return $this->temp_dir;
    }


    /**
     * Set directory name located in /temp/ directory.
     *
     * @param $temp_dir
     */
    public function setTempDir($temp_dir)
    {
        $this->temp_dir = $temp_dir;
    }


    /**
     * @return string[]
     */
    public function getObjectRefIds()
    {
        return $this->object_ref_ids;
    }


    /**
     * @param $object_ref_ids
     * @param $append
     */
    public function setObjectRefIds($object_ref_ids, $append = false)
    {
        if ($append) {
            array_merge($this->object_ref_ids, $object_ref_ids);
        } else {
            $this->object_ref_ids = $object_ref_ids;
        }
    }


    /**
     * @return int
     */
    public function getNumFiles()
    {
        return $this->num_files;
    }


    /**
     * @param $num_files
     */
    public function setNumFiles($num_files)
    {
        $this->num_files = $num_files;
    }


    /**
     * @return int
     */
    public function getSumFileSizes()
    {
        return $this->sum_file_sizes;
    }


    /**
     * @param int $sum_file_sizes
     */
    public function setSumFileSizes($sum_file_sizes)
    {
        $this->sum_file_sizes = $sum_file_sizes;
    }


    /**
     * @return bool
     */
    public function getAdheresToLimit()
    {
        return $this->adheres_to_limit;
    }


    /**
     * @param bool $adheres_to_limit
     */
    public function setAdheresToLimit($adheres_to_limit)
    {
        $this->adheres_to_limit = $adheres_to_limit;
    }


    /**
     * Add copy definition
     *
     * @param string $a_source
     * @param string $a_target
     */
    public function addCopyDefinition($a_source, $a_target)
    {
        $this->copy_definitions[]
            = [
            self::COPY_SOURCE_DIR => $a_source,
            self::COPY_TARGET_DIR => $a_target,
        ];
    }


    /**
     * Check equality
     *
     * @param Value $other
     *
     * @return bool
     */
    public function equals(Value $other)
    {
        return strcmp($this->getHash(), $other->getHash());
    }


    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return md5($this->serialize());
    }


    /**
     * Serialize content
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
     *
     * @param string[] $value
     */
    public function setValue($value)
    {
        $this->copy_definitions = $value;
    }


    /**
     * Unserialize definitions
     *
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
