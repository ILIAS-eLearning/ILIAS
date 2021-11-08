<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component;

/**
 * Class SignalGenerator
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component
 */
class SignalGenerator implements SignalGeneratorInterface
{
    public const PREFIX = 'il_signal_';

    /**
     * @inheritdoc
     */
    public function create(string $class = '') : Signal
    {
        $id = $this->createId();
        return ($class) ? new $class($id) : new Signal($id);
    }

    protected function createId() : string
    {
        return str_replace(".", "_", uniqid(self::PREFIX, true));
    }
}
