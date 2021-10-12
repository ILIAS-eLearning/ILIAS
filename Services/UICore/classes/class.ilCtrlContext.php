<?php

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Wrapper\RequestWrapper;

/**
 * Class ilCtrlContext is responsible for holding the
 * current context information.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @TODO: discuss: it's possible for ilCtrl to be in an unstable
 *        state, because possibly no context exists. Contexts
 *        start existing AFTER initBaseClass() or callBaseClass()
 *        is called. It is possible to work around that with null
 *        checks, but we shouldn't have to.
 */
final class ilCtrlContext implements ilCtrlContextInterface
{
    /**
     * @var RequestWrapper
     */
    private RequestWrapper $request;

    /**
     * @var Refinery
     */
    private Refinery $refinery;

    /**
     * @var ilCtrlPathFactory
     */
    private ilCtrlPathFactory $path_factory;

    /**
     * @var ilCtrlPathInterface
     */
    private ilCtrlPathInterface $path;

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
     * @param ilCtrlPathFactory $path_factory
     * @param RequestWrapper    $request
     * @param Refinery          $refinery
     * @param string|null       $base_class
     */
    public function __construct(
        ilCtrlPathFactory $path_factory,
        RequestWrapper $request,
        Refinery $refinery,
        string $base_class = null
    ) {
        $this->base_class   = $base_class;
        $this->path_factory = $path_factory;
        $this->refinery     = $refinery;
        $this->request      = $request;

        $this->initFromRequest();
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

    /**
     * Initializes the current context with information
     * from the current request.
     */
    private function initFromRequest() : void
    {
        if (null === $this->base_class) {
            $this->base_class = $this->getQueryParam(ilCtrlInterface::PARAM_BASE_CLASS);
        }

        $cmd_class = $this->getQueryParam(ilCtrlInterface::PARAM_CMD_CLASS);
        $cid_path  = $this->getQueryParam(ilCtrlInterface::PARAM_CID_PATH);
        if (null !== $cmd_class && null !== $cid_path) {
            $this->setCmdClass($cmd_class);
            $this->setPath($this->path_factory->byExistingPath($cid_path));
        }

        if (null !== ($cmd = $this->getQueryParam(ilCtrlInterface::PARAM_CMD))) {
            $this->setCmd($cmd);
        }

        $this->setAsync(
            ilCtrlInterface::CMD_MODE_ASYNC === $this->getQueryParam(ilCtrlInterface::PARAM_CMD_MODE)
        );
    }

    /**
     * Helper function to retrieve $_GET parameters by name.
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