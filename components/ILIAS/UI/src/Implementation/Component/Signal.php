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

namespace ILIAS\UI\Implementation\Component;

/**
 * Class Signal
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component
 */
class Signal implements \ILIAS\UI\Component\Signal
{
    protected string $id;
    protected array $options = array();

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param mixed $value
     */
    public function addOption(string $key, $value): void
    {
        $this->options[$key] = $value;
    }

    /**
     * @return mixed|null
     */
    protected function getOption(string $key)
    {
        return (isset($this->options[$key])) ? $this->options[$key] : null;
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
