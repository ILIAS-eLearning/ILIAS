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

    abstract protected function isValidADT(ilADT $a_adt): bool;

    protected function setADT(ilADT $a_adt): void
    {
        if (!$this->isValidADT($a_adt)) {
            throw new InvalidArgumentException('ADTPresentationBridge Type mismatch.');
        }
        $this->adt = $a_adt;
    }

    public function getADT(): ?ilADT
    {
        return $this->adt;
    }

    public function getList(): string
    {
        return $this->getHTML();
    }

    abstract public function getHTML(): string;

    /**
     * Get sortable value presentation
     * @return
     */
    abstract public function getSortable();

    /**
     * Takes as input an array consisting of the object that
     * the method that should be called back to belongs to, and
     * a string with the name of the method.
     * @param null|array{0: ilObject, 1: string} $a_callback
     */
    public function setDecoratorCallBack(?array $a_callback): void
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
