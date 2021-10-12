<?php

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Wrapper\RequestWrapper;

/**
 * Class ilCtrlContext is responsible for holding the
 * current context information.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilCtrlContext implements ilCtrlContextInterface
{
    /**
     * @var ilCtrlPathFactory
     */
    private ilCtrlPathFactory $path_factory;

    /**
     * @var RequestWrapper
     */
    private RequestWrapper $request;

    /**
     * @var Refinery
     */
    private Refinery $refinery;

    /**
     * @var ilCtrlPathInterface
     */
    private ilCtrlPathInterface $path;

    /**
     * @var bool
     */
    private bool $is_async = false;

    /**
     * @var string
     */
    private string $target_script = 'ilias.php';

    /**
     * @var string|null
     */
    private ?string $base_class = null;

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
    private ?string $obj_type = null;

    /**
     * @var int|null
     */
    private ?int $obj_id = null;

    /**
     * ilCtrlContext Constructor
     *
     * @param ilCtrlPathFactory $path_factory
     * @param RequestWrapper    $request
     * @param Refinery          $refinery
     */
    public function __construct(ilCtrlPathFactory $path_factory, RequestWrapper $request, Refinery $refinery)
    {
        $this->path_factory = $path_factory;
        $this->request      = $request;
        $this->refinery     = $refinery;

        // initialize null-path per default.
        $this->path = $path_factory->null();
    }

    /**
     * @inheritDoc
     */
    public function adoptRequestParameters() : void
    {
        $this->is_async   = (ilCtrlInterface::CMD_MODE_ASYNC === $this->getQueryParam(ilCtrlInterface::PARAM_CMD_MODE));
        $this->base_class = $this->getQueryParam(ilCtrlInterface::PARAM_BASE_CLASS) ?? $this->base_class;
        $this->cmd_class  = $this->getQueryParam(ilCtrlInterface::PARAM_CMD_CLASS);
        $this->cmd        = $this->getQueryParam(ilCtrlInterface::PARAM_CMD);

        $existing_path = $this->getQueryParam(ilCtrlInterface::PARAM_CID_PATH);
        if (null !== $existing_path) {
            $this->path = $this->path_factory->existingPath($existing_path);
        } elseif (null !== $this->base_class) {
            $this->path = $this->path_factory->singleClass($this, $this->base_class);
        }
        // otherwise, the path is null per default.
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
    public function getPath() : ilCtrlPathInterface
    {
        return $this->path;
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
    public function setObjType(string $obj_type) : ilCtrlContextInterface
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

    /**
     * @inheritDoc
     */
    public function setObjId(int $obj_id) : ilCtrlContextInterface
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
     * Helper function to retrieve request parameter values by name.
     *
     * @param string $parameter_name
     * @return string|null
     */
    private function getQueryParam(string $parameter_name) : ?string
    {
        if ($this->request->has($parameter_name)) {
            return $this->request->retrieve(
                $parameter_name,
                $this->refinery->to()->string()
            );
        }

        return null;
    }
}