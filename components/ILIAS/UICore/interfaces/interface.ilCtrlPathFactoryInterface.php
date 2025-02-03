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
 * Interface ilCtrlPathFactoryInterface describes the ilCtrl
 * Path factory.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface ilCtrlPathFactoryInterface
{
    /**
     * Returns the corresponding ilCtrlPath by the provided target type.
     *
     * @param ilCtrlContextInterface $context
     * @param string[]|string        $target
     * @return ilCtrlPathInterface
     */
    public function find(ilCtrlContextInterface $context, $target): ilCtrlPathInterface;

    /**
     * Returns an instance of an existing ilCtrlPath.
     *
     * @param string $cid_path
     * @return ilCtrlPathInterface
     */
    public function existing(string $cid_path): ilCtrlPathInterface;

    /**
     * Returns a pseudo instance of an ilCtrlPath.
     *
     * @return ilCtrlPathInterface
     */
    public function null(): ilCtrlPathInterface;
}
