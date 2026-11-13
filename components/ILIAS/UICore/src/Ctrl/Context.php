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

namespace ILIAS\UICore\Ctrl;

/**
 * Interface Context holds the current ilCtrl context
 * information.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface Context
{
    /**
     * Returns whether this context is asynchronous or not.
     *
     * @return bool
     */
    public function isAsync(): bool;

    /**
     * Returns where the request of this context was redirected from.
     *
     * @return string|null
     */
    public function getRedirectSource(): ?string;

    /**
     * Returns the path of this context.
     *
     * @return Path
     */
    public function getPath(): Path;

    /**
     * Sets the command mode of the current context.
     *
     * @param string $mode
     * @return Context
     */
    public function setCmdMode(string $mode): Context;

    /**
     * Returns the command mode of the current context.
     *
     * @return string|null
     */
    public function getCmdMode(): ?string;

    /**
     * Sets the baseclass of the current context.
     *
     * @param string $base_class
     * @return Context
     */
    public function setBaseClass(string $base_class): Context;

    /**
     * Returns the baseclass the current context.
     *
     * @return string|null
     */
    public function getBaseClass(): ?string;

    /**
     * Sets the target script of this context (usually ilias.php).
     *
     * @param string $target_script
     * @return Context
     */
    public function setTargetScript(string $target_script): Context;

    /**
     * Returns the target script of this context.
     *
     * @return string
     */
    public function getTargetScript(): string;

    /**
     * Sets the current contexts command class.
     *
     * @param string|null $cmd_class
     * @return Context
     */
    public function setCmdClass(?string $cmd_class): Context;

    /**
     * Returns the command class of this context.
     *
     * @return string|null
     */
    public function getCmdClass(): ?string;

    /**
     * Sets the command which the current command- or baseclass
     * should perform.
     *
     * @param string|null $cmd
     * @return self
     */
    public function setCmd(?string $cmd): self;

    /**
     * Returns the command which the current command- or baseclass
     * should perform.
     *
     * @return string|null
     */
    public function getCmd(): ?string;

    /**
     * Sets the object type of the current context.
     *
     * @param string $obj_type
     * @return Context
     */
    public function setObjType(string $obj_type): Context;

    /**
     * Returns the object type of the current context.
     *
     * @return string|null
     */
    public function getObjType(): ?string;

    /**
     * Sets the object id of the current context.
     *
     * @param int $obj_id
     * @return Context
     */
    public function setObjId(int $obj_id): Context;

    /**
     * Returns the object id of the current context.
     *
     * @return int|null
     */
    public function getObjId(): ?int;
}
