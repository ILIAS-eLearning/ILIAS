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
class ilCalendarCopyDefinition extends AbstractValue
{
    const COPY_SOURCE_DIR = 'source';
    const COPY_TARGET_DIR = 'target';
    
    /**
     * Copy Jobs: source file => relative target file in zip directory.
     * @param string[]
     */
    private $copy_definitions = [];

    /**
     * Temporary directory using the normalized title of the bucket.
     * @var string
     */
    private $temp_dir;

    
    
    /**
     * Get copy definitions
     * @return string[]
     */
    public function getCopyDefinitions()
    {
        return $this->copy_definitions;
    }
    
    /**
     * Set copy definitions
     * @param string[] $a_definitions
     */
    public function setCopyDefinitions($a_definitions)
    {
        $this->copy_definitions = $a_definitions;
    }

    /**
     * Get directory name located in /temp/ directory.
     * @return string
     */
    public function getTempDir()
    {
        return $this->temp_dir;
    }

    /**
     * Set directory name located in /temp/ directory.
     * @param $temp_dir
     */
    public function setTempDir($temp_dir)
    {
        $this->temp_dir = $temp_dir;
    }
    
    /**
     * Add copy definition
     * @param string $a_source
     * @param string $a_target
     */
    public function addCopyDefinition($a_source, $a_target)
    {
        $this->copy_definitions[] =
            [
                self::COPY_SOURCE_DIR => $a_source,
                self::COPY_TARGET_DIR => $a_target
            ];
    }
    

    /**
     * Check equality
     * @param Value $other
     * @return bool
     */
    public function equals(Value $other)
    {
        return strcmp($this->getHash(), $other->getHash());
    }

    
    /**
     * Get hash
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
                "temp_dir" => $this->getTempDir()
            ]
        );
    }

    /**
     * Set value
     * @param string[] $value
     */
    public function setValue($value)
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
    }
}
