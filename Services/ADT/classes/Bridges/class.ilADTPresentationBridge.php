<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ADT presentation bridge base class
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesADT
 */
abstract class ilADTPresentationBridge
{
    protected ilADT $adt;
    /**
     * @var ?callable
     */
    protected $decorator;
    protected ilLanguage $lng;

    public function __construct(ilADT $a_adt)
    {
        global $DIC;

        $this->lng = $DIC->language();

        $this->setADT($a_adt);
    }

    abstract protected function isValidADT(ilADT $a_adt) : bool;

    protected function setADT(ilADT $a_adt) : void
    {
        if (!$this->isValidADT($a_adt)) {
            throw new InvalidArgumentException('ADTPresentationBridge Type mismatch.');
        }
        $this->adt = $a_adt;
    }

    public function getADT() : ?ilADT
    {
        return $this->adt;
    }

    public function getList() : string
    {
        return $this->getHTML();
    }

    abstract public function getHTML() : string;

    /**
     * Get sortable value presentation
     * @return
     */
    abstract public function getSortable();

    /**
     * Set decorator callback
     * @param callable $a_callback
     */
    public function setDecoratorCallBack(?callable $a_callback) : void
    {
        $this->decorator = $a_callback;
    }

    /**
     * Decorate value
     * @param string|int $a_value
     * @return string|int
     */
    protected function decorate($a_value)
    {
        if (is_callable($this->decorator)) {
            $a_value = call_user_func($this->decorator, $a_value);
        }
        return $a_value;
    }
}
