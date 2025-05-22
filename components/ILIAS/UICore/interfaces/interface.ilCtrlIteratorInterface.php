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

/**
 * Interface ilCtrlIteratorInterface
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * This interface describes how an ilCtrl iterator must behave
 * like. It extends the original Iterator interface but overrides
 * the public functions current() and key(), as the Iterators
 * must always return class-paths mapped to the object name.
 *
 * This means, that Iterators implementing this interface have
 * rather complex valid() methods, as they need to check if
 * the current data and key provided by the source are strings.
 */
interface ilCtrlIteratorInterface extends Iterator
{
    /**
     * @inheritDoc
     *
     * @return string
     */
    public function current(): ?string;

    /**
     * @inheritDoc
     *
     * @return string
     */
    public function key(): ?string;
}
