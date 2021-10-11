<?php

/**
 * Class ilCtrlContext is responsible for holding the
 * current context information.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilCtrlContext implements ilCtrlContextInterface
{
    /**
     * @var ilCtrlPathInterface|null
     */
    private ?ilCtrlPathInterface $path = null;

    /**
     * @var string
     */
    private string $target_script = 'ilias.php';

    /**
     * @var bool
     */
    private bool $is_async = false;

    /**
     * @var int|null
     */
    private ?int $obj_id = null;

    /**
     * @var string|null
     */
    private ?string $obj_type = null;

    /**
     * @var string|null
     */
    private ?string $cmd_class = null;

    /**
     * @var string|null
     */
    private ?string $cmd = null;

    /**
     * @var string|null
     */
    private ?string $base_class;

    /**
     * ilCtrlContext Constructor
     *
     * @param string|null $base_class
     */
    public function __construct(string $base_class = null)
    {
        $this->base_class = $base_class;
    }

    /**
     * @inheritDoc
     */
    public function setPath(ilCtrlPathInterface $path) : ilCtrlContextInterface
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPath() : ilCtrlPathInterface
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function setAsync(bool $is_async) : ilCtrlContextInterface
    {
        $this->is_async = $is_async;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isAsync() : bool
    {
        return $this->is_async;
    }

    /**
     * @inheritDoc
     */
    public function setTargetScript(string $target_script) : ilCtrlContextInterface
    {
        $this->target_script = $target_script;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTargetScript() : string
    {
        return $this->target_script;
    }

    /**
     * @inheritDoc
     */
    public function setBaseClass(string $base_class) : ilCtrlContextInterface
    {
        $this->base_class = $base_class;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBaseClass() : ?string
    {
        return $this->base_class;
    }

    /**
     * @inheritDoc
     */
    public function setCmdClass(string $cmd_class) : ilCtrlContextInterface
    {
        $this->cmd_class = $cmd_class;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCmdClass() : ?string
    {
        return $this->cmd_class;
    }

    /**
     * @inheritDoc
     */
    public function setCmd(string $cmd) : ilCtrlContextInterface
    {
        $this->cmd = $cmd;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCmd() : ?string
    {
        return $this->cmd;
    }

    /**
     * @inheritDoc
     */
    public function setObjId(int $obj_id) : ilCtrlContext
    {
        $this->obj_id = $obj_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getObjId() : ?int
    {
        return $this->obj_id;
    }

    /**
     * @inheritDoc
     */
    public function setObjType(string $obj_type) : ilCtrlContext
    {
        $this->obj_type = $obj_type;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getObjType() : ?string
    {
        return $this->obj_type;
    }
}