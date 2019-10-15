<?php
namespace ILIAS\UI\Implementation\Component;

/**
 * Class SignalGenerator
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 * @package ILIAS\UI\Implementation\Component
 */
class SignalGenerator implements SignalGeneratorInterface
{
    const PREFIX = 'il_signal_';

    /**
     * @inheritdoc
     */
    public function create($class = '')
    {
        $id = $this->createId();
        $instance = ($class) ? new $class($id) : new Signal($id);
        return $instance;
    }

    /**
     * @return string
     */
    protected function createId()
    {
        return str_replace(".", "_", uniqid(self::PREFIX, true));
    }
}
