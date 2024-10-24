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

namespace ILIAS\Services\Help\ScreenId;

class HelpScreenIdObserver implements \ilCtrlObserver
{
    use ClassNameToScreenId;

    private const SCREEN_SEPARATOR = '/';
    private const COMMAND_SEPARATOR = '/';
    private array $map;
    protected array $clean_name_stack = [];
    protected ?string $command = null;

    public function __construct()
    {
        $this->map = [];
        //$this->map = include \ilHelpBuildScreenIdMapObjective::PATH();
    }

    public function getId(): string
    {
        return self::class;
    }

    public function update(\ilCtrlEvent $event, ?string $data): void
    {
        match ($event) {
            \ilCtrlEvent::COMMAND_CLASS_FORWARD => $this->addLatestCommandClass($data),
            \ilCtrlEvent::COMMAND_DETERMINATION => $this->setLatestCommand($data),
        };
    }

    public function getScreenId(): string
    {
        return implode(
            self::SCREEN_SEPARATOR,
            $this->clean_name_stack
        ) . ($this->command !== null ? self::COMMAND_SEPARATOR . $this->command : '');
    }

    protected function addLatestCommandClass(?string $class): void
    {
        if (null === $class) {
            return;
        }

        $clean_class_name = $this->cleanClassName($class);
        $last_stack_entry = (($stack_size = count($this->clean_name_stack)) > 0) ?
            $this->clean_name_stack[($stack_size - 1)] :
            null;

        if ($last_stack_entry !== $clean_class_name) {
            $this->clean_name_stack[] = $clean_class_name;
        }
    }

    protected function setLatestCommand(?string $command): void
    {
        if (null !== $command) {
            $this->command = $this->cleanCommandName($command);
        }
    }

    protected function cleanClassName(string $classname): ?string
    {
        // check for attributes in artifact
        if (isset($this->map[$classname])) {
            return $this->map[$classname];
        }

        // This is the fallback for classes which do not have a screen id attribute yet.
        return $this->classNameToScreenId($classname);
    }

    protected function cleanCommandName(string $command): string
    {
        return $this->snakeToCamel($command);
    }
}
