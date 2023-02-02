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
 */

declare(strict_types=1);

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Wrapper\RequestWrapper;

/**
 * Class ilCtrlContext is responsible for holding the
 * current context information.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 * @noinspection AutoloadingIssuesInspection
 */
class ilCtrlContext implements ilCtrlContextInterface
{
    /**
     * @var ilCtrlPathFactory
     */
    protected ilCtrlPathFactory $path_factory;

    /**
     * @var RequestWrapper
     */
    protected RequestWrapper $request_wrapper;

    /**
     * @var Refinery
     */
    protected Refinery $refinery;

    /**
     * @var ilCtrlPathInterface
     */
    protected ilCtrlPathInterface $path;

    /**
     * @var bool
     */
    protected bool $is_async = false;

    /**
     * @var string
     */
    protected string $target_script = 'ilias.php';

    /**
     * @var string|null
     */
    protected ?string $cmd_mode = null;

    /**
     * @var string|null
     */
    protected ?string $redirect = null;

    /**
     * @var string|null
     */
    protected ?string $base_class = null;

    /**
     * @var string|null
     */
    protected ?string $cmd_class = null;

    /**
     * @var string|null
     */
    protected ?string $cmd = null;

    /**
     * @var string|null
     */
    protected ?string $obj_type = null;

    /**
     * @var int|null
     */
    protected ?int $obj_id = null;

    /**
     * @var array<string, ilCtrlPathInterface>
     */
    protected array $history = [];

    /**
     * ilCtrlContext Constructor
     *
     * @param ilCtrlPathFactory $path_factory
     * @param RequestWrapper    $request_wrapper
     * @param Refinery          $refinery
     */
    public function __construct(ilCtrlPathFactory $path_factory, RequestWrapper $request_wrapper, Refinery $refinery)
    {
        $this->path_factory = $path_factory;
        $this->path = $path_factory->null();
        $this->request_wrapper = $request_wrapper;
        $this->refinery = $refinery;

        $this->adoptRequestParameters();
    }

    /**
     * @inheritDoc
     */
    public function isAsync(): bool
    {
        return $this->is_async;
    }

    /**
     * @inheritDoc
     */
    public function getRedirectSource(): ?string
    {
        return $this->redirect;
    }

    /**
     * @inheritDoc
     */
    public function getPath(): ilCtrlPathInterface
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function setCmdMode(string $mode): ilCtrlContextInterface
    {
        $this->cmd_mode = $mode;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCmdMode(): ?string
    {
        return $this->cmd_mode;
    }

    /**
     * @inheritDoc
     */
    public function setBaseClass(string $base_class): ilCtrlContextInterface
    {
        $path = $this->path_factory->find($this, $base_class);

        // only set baseclass if it's a valid target.
        if (null !== $path->getCidPath()) {
            $this->history[$base_class] = $path;
            $this->base_class = $base_class;
            // only update the path if the current one is null.
            if (null === $this->path->getCidPath()) {
                $this->path = $path;
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBaseClass(): ?string
    {
        return $this->base_class;
    }

    /**
     * @inheritDoc
     */
    public function setTargetScript(string $target_script): ilCtrlContextInterface
    {
        $this->target_script = $target_script;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTargetScript(): string
    {
        return $this->target_script;
    }

    /**
     * @inheritDoc
     */
    public function setCmdClass(?string $cmd_class): ilCtrlContextInterface
    {
        $path = $this->path_factory->find($this, $cmd_class);

        // only set command class if it's a valid target.
        if (null !== $path->getCidPath()) {
            $this->history[$cmd_class] = $path;
            $this->cmd_class = $cmd_class;
            $this->path = $path;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function popCmdClass(): ilCtrlContextInterface
    {
        $previous_cmd_class = array_key_last($this->history);

        if (null === $previous_cmd_class) {
            return $this;
        }

        $this->path = $this->history[$previous_cmd_class];
        $this->cmd_class = $previous_cmd_class;

        unset($this->history[$previous_cmd_class]);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCmdClass(): ?string
    {
        // if no cmd_class is set yet, the baseclass
        // value can be returned.
        return $this->cmd_class ?? $this->base_class;
    }

    /**
     * @inheritDoc
     */
    public function setCmd(?string $cmd): ilCtrlContextInterface
    {
        $this->cmd = $cmd;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCmd(): ?string
    {
        return $this->cmd;
    }

    /**
     * @inheritDoc
     */
    public function setObjType(string $obj_type): ilCtrlContextInterface
    {
        $this->obj_type = $obj_type;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getObjType(): ?string
    {
        return $this->obj_type;
    }

    /**
     * @inheritDoc
     */
    public function setObjId(int $obj_id): ilCtrlContextInterface
    {
        $this->obj_id = $obj_id;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getObjId(): ?int
    {
        return $this->obj_id;
    }

    /**
     * Adopts context properties from the according ones delivered by
     * the current request.
     *
     * Note that this method should only be called when initializing
     * ilCtrl, as methods may override delivered values on purpose
     * later on.
     */
    protected function adoptRequestParameters(): void
    {
        $this->redirect = $this->getQueryParam(ilCtrlInterface::PARAM_REDIRECT);
        $this->cmd_mode = $this->getQueryParam(ilCtrlInterface::PARAM_CMD_MODE);
        $this->cmd = $this->getQueryParam(ilCtrlInterface::PARAM_CMD);

        // the context is considered asynchronous if
        // the command mode is 'async'.
        if (ilCtrlInterface::CMD_MODE_ASYNC === $this->cmd_mode) {
            $this->is_async = true;
        }

        // if an existing path is provided use it by default.
        $existing_path = $this->getQueryParam(ilCtrlInterface::PARAM_CID_PATH);
        if (null !== $existing_path) {
            $this->path = $this->path_factory->existing($existing_path);
        }

        // set the provided baseclass, which might override the
        // previously set existing path.
        $base_class = $this->getQueryParam(ilCtrlInterface::PARAM_BASE_CLASS);
        if (null !== $base_class) {
            $this->setBaseClass($base_class);
        }

        // set or append the provided command class, which might
        // override the previously set path again.
        $cmd_class = $this->getQueryParam(ilCtrlInterface::PARAM_CMD_CLASS);
        if (null !== $cmd_class) {
            $this->setCmdClass($cmd_class);
        }
    }

    /**
     * Helper function to retrieve request parameter values by name.
     *
     * @param string $parameter_name
     * @return string|null
     */
    protected function getQueryParam(string $parameter_name): ?string
    {
        if ($this->request_wrapper->has($parameter_name)) {
            return $this->request_wrapper->retrieve(
                $parameter_name,
                $this->refinery->to()->string()
            );
        }

        return null;
    }
}
