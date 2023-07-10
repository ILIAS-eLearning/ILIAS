<?php

declare(strict_types=1);

use ILIAS\BackgroundTasks\Implementation\Values\AbstractValue;
use ILIAS\BackgroundTasks\Value;

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class class
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilCalendarCopyDefinition extends AbstractValue
{
    public const COPY_SOURCE_DIR = 'source';
    public const COPY_TARGET_DIR = 'target';

    /**
     * Copy Jobs: source file => relative target file in zip directory.
     * @param ilCalendarCopyDefinition[]
     */
    private array $copy_definitions = [];

    /**
     * Temporary directory using the normalized title of the bucket.
     * @var string
     */
    private string $temp_dir;

    /**
     * Get copy definitions
     * @return ilCalendarCopyDefinition[]
     */
    public function getCopyDefinitions(): array
    {
        return $this->copy_definitions;
    }

    /**
     * Set copy definitions
     * @param ilCalendarCopyDefinition[] $a_definitions
     */
    public function setCopyDefinitions(array $a_definitions): void
    {
        $this->copy_definitions = $a_definitions;
    }

    /**
     * Get directory name located in /temp/ directory.
     * @return string
     */
    public function getTempDir(): string
    {
        return $this->temp_dir;
    }

    /**
     * Set directory name located in /temp/ directory.
     * @param string $temp_dir
     */
    public function setTempDir(string $temp_dir): void
    {
        $this->temp_dir = $temp_dir;
    }

    /**
     * Add copy definition
     */
    public function addCopyDefinition(string $a_source, string $a_target): void
    {
        $this->copy_definitions[] =
            [
                self::COPY_SOURCE_DIR => $a_source,
                self::COPY_TARGET_DIR => $a_target
            ];
    }

    /**
     * @inheritDoc
     */
    public function equals(Value $other): bool
    {
        return strcmp($this->getHash(), $other->getHash()) === 0;
    }

    /**
     * @inheritDoc
     */
    public function getHash(): string
    {
        return md5($this->serialize());
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    public function setValue($value): void
    {
        $this->copy_definitions = $value;
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        $elements = unserialize($serialized);

        $this->setCopyDefinitions($elements["copy_definition"]);
        $this->setTempDir($elements['temp_dir']);
    }
}
