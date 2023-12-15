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

declare(strict_types=1);

use ILIAS\BackgroundTasks\Implementation\Values\AbstractValue;
use ILIAS\BackgroundTasks\Value;

class ilCalendarRessourceStorageCopyDefinition extends AbstractValue
{
    public const COPY_RESSOURCE_ID = 'ressource_id';
    public const COPY_TARGET_DIR = 'target';

    private array $copy_definitions = [];

    /**
     * Temporary directory using the normalized title of the bucket.
     * @var string
     */
    private string $temp_dir;

    /**
     * Get copy definitions
     * @return ilCalendarRessourceStorageCopyDefinition[]
     */
    public function getCopyDefinitions(): array
    {
        return $this->copy_definitions;
    }

    /**
     * Set copy definitions
     * @param ilCalendarRessourceStorageCopyDefinition[] $a_definitions
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
    public function addCopyDefinition(string $a_ressource_id, string $a_target): void
    {
        $this->copy_definitions[] =
            [
                self::COPY_RESSOURCE_ID => $a_ressource_id,
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
    public function unserialize($data)
    {
        $elements = unserialize($data);

        $this->setCopyDefinitions($elements["copy_definition"]);
        $this->setTempDir($elements['temp_dir']);
    }
}
