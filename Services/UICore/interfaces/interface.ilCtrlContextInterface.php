<?php

/**
 * Interface ilCtrlContextInterface
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface ilCtrlContextInterface
{
    /**
     * Returns the baseclass this context was instantiated with.
     *
     * @return string
     */
    public function getBaseClass() : string;

    /**
     * Sets the path of this context.
     *
     * @param ilCtrlPathInterface $path
     * @return ilCtrlContextInterface
     */
    public function setPath(ilCtrlPathInterface $path) : ilCtrlContextInterface;

    /**
     * Returns the path of this context.
     *
     * @return ilCtrlPathInterface
     */
    public function getPath() : ilCtrlPathInterface;

    /**
     * Sets whether this context is asynchronous or not.
     *
     * @param bool $is_async
     * @return ilCtrlContextInterface
     */
    public function setAsync(bool $is_async) : ilCtrlContextInterface;

    /**
     * Returns whether this context is asynchronous or not.
     *
     * @return bool
     */
    public function isAsync() : bool;

    /**
     * Sets the target script of this context (usually ilias.php).
     *
     * @param string $target_script
     * @return ilCtrlContextInterface
     */
    public function setTargetScript(string $target_script) : ilCtrlContextInterface;

    /**
     * Returns the target script of this context.
     *
     * @return string
     */
    public function getTargetScript() : string;

    /**
     * Sets the command class of this context.
     *
     * @param string $cmd_class
     * @return self
     */
    public function setCmdClass(string $cmd_class) : self;

    /**
     * Returns the command class of this context.
     *
     * @return string|null
     */
    public function getCmdClass() : ?string;

    /**
     * Sets the command which the current command- or baseclass
     * should perform.
     *
     * @param string $cmd
     * @return self
     */
    public function setCmd(string $cmd) : self;

    /**
     * Returns the command which the current command- or baseclass
     * should perform.
     *
     * @return string|null
     */
    public function getCmd() : ?string;

    // BEGIN LEGACY METHODS

    /**
     * Sets the object id of the current context.
     *
     * @param int $obj_id
     * @return ilCtrlContextInterface
     */
    public function setObjId(int $obj_id) : ilCtrlContextInterface;

    /**
     * Returns the object id of the current context.
     *
     * @return int|null
     */
    public function getObjId() : ?int;

    /**
     * Sets the object type of the current context.
     *
     * @param string $obj_type
     * @return ilCtrlContextInterface
     */
    public function setObjType(string $obj_type) : ilCtrlContextInterface;

    /**
     * Returns the object type of the current context.
     *
     * @return string|null
     */
    public function getObjType() : ?string;

    // END LEGACY METHODS
}