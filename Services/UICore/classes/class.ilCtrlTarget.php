<?php

/**
 * Class ilCtrlTarget is a data transfer object of a
 * link target that has been generated with ilCtrl.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
final class ilCtrlTarget implements ilCtrlTargetInterface
{
    /**
     * @var string command name which must be provided in $_GET when
     *             a POST request should be processed.
     */
    public const CMD_POST = 'post';

    /**
     * Holds the trace from baseclass to command-class of the
     * current target.
     *
     * @var ilCtrlTraceInterface
     */
    private ilCtrlTraceInterface $trace;

    /**
     * Holds a token generated for the current user session that
     * is used for unsecure link targets.
     *
     * @var ilCtrlTokenInterface|null
     */
    private ?ilCtrlTokenInterface $token = null;

    /**
     * Holds the current target's parameters.
     *
     * @var array<string, mixed>|null
     */
    private ?array $parameters = null;

    /**
     * Holds the current target's site anchor.
     *
     * @var string|null
     */
    private ?string $anchor = null;


    /**
     * Holds the command of the current target.
     *
     * @var string|null
     */
    private ?string $cmd = null;

    /**
     * Holds whether the current target is asynchronous or not.
     * Depending on this, an async flag may be appended to the
     * target URL.
     *
     * @var bool
     */
    private bool $is_async = false;

    /**
     * Holds whether the current target should append further
     * link parameters escaped or not.
     *
     * @var bool
     */
    private bool $is_escaped = false;

    /**
     * Holds the target-script of the current target.
     *
     * @var string
     */
    private string $target_script = 'ilias.php';

    /**
     * Holds the executing class of the current target.
     *
     * @var string|null
     */
    private ?string $cmd_class = null;

    /**
     * Holds the baseclass of the current target.
     *
     * @var string
     */
    private string $base_class;

    /**
     * Constructor
     *
     * @param ilCtrlStructureInterface $structure
     * @param string                   $base_class
     */
    public function __construct(ilCtrlStructureInterface $structure, string $base_class)
    {
        $this->base_class = strtolower($base_class);
        $this->trace = new ilCtrlTrace(
            $structure,
            $base_class
        );
    }

    /**
     * @inheritDoc
     */
    public function getBaseClass() : string
    {
        return $this->base_class;
    }

    /**
     * @inheritDoc
     */
    public function setTargetScript(string $target_script) : ilCtrlTargetInterface
    {
        // no empty check as there may be an edge case
        // somewhere I don't know about.
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
    public function appendCmdClass(string $class_name) : ilCtrlTarget
    {
        if (!empty($class_name)) {
            $cmd_class = strtolower($class_name);

            // only append command class if it's not the
            // current baseclass.
            if ($this->base_class !== $cmd_class) {
                $this->trace->appendByClass($cmd_class);
                $this->cmd_class = $cmd_class;
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function appendCmdClassArray(array $classes) : ilCtrlTargetInterface
    {
        // if only one class is delivered we can to assume
        // the wrong method is called.
        if (1 === count($classes)) {
            $this->appendCmdClass($classes[0]);
        }

        // if there are more than one class we can assume a
        // (maybe) valid class path is provided that can be
        // used to replace the current trace.
        if (1 < count($classes)) {
            $this->trace->replaceByClassPath($classes);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentCmdClass() : string
    {
        // if no command class is provided, the baseclass
        // is the current command class.
        return $this->cmd_class ?? $this->base_class;
    }

    /**
     * @inheritDoc
     */
    public function setCmd(string $cmd) : ilCtrlTarget
    {
        if (!empty($cmd)) {
            $this->cmd = $cmd;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCmd() : ?string
    {
        return $this->cmd ?? null;
    }

    /**
     * @inheritDoc
     */
    public function setAsync(bool $is_async) : ilCtrlTargetInterface
    {
        $this->is_async = $is_async;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setEscaped(bool $is_escaped) : ilCtrlTargetInterface
    {
        $this->is_escaped = $is_escaped;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setToken(ilCtrlTokenInterface $token) : ilCtrlTargetInterface
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setParameters(array $parameters) : ilCtrlTargetInterface
    {
        if (!empty($parameters)) {
            if (null === $this->parameters) {
                $this->parameters = $parameters;
            } else {
                $this->parameters = array_merge_recursive($this->parameters, $parameters);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAnchor(string $anchor) : ilCtrlTargetInterface
    {
        if (!empty($anchor)) {
            // append hashtag if none was provided.
            $this->anchor = (!is_int(strpos($anchor, '#'))) ?
                '#' . $anchor : $anchor
            ;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTargetUrl(bool $is_post = false) : ?string
    {
        if (!$this->trace->isValid()) {
            return null;
        }

        // initialize the target URL and append it with the
        // current baseclass.
        $target_url = $this->appendParameterString(
            $this->target_script,
            self::PARAM_BASE_CLASS,
            $this->base_class
        );

        // if a command class exists, it must be appended with
        // the according trace.
        if (null !== $this->cmd_class) {
            $target_url = $this->appendParameterString($target_url, self::PARAM_TRACE, $this->trace->getCidTrace());
            $target_url = $this->appendParameterString($target_url, self::PARAM_CMD_CLASS, $this->cmd_class);
        }

        // if no post command is provided, the current command
        // can be used if it has been set.
        if (!$is_post && null !== $this->cmd) {
            $target_url = $this->appendParameterString($target_url, self::PARAM_CMD, $this->cmd);
        }

        // if a post command is provided, the command must be set
        // to CMD_POST and the current command has to be appended
        // as a fallback.
        if ($is_post && null !== $this->cmd) {
            $target_url = $this->appendParameterString($target_url, self::PARAM_CMD, self::CMD_POST);
            $target_url = $this->appendParameterString($target_url, self::PARAM_CMD_FALLBACK, $this->cmd);
        }

        if (null !== $this->parameters) {
            foreach ($this->parameters as $key => $value) {
                $target_url = $this->appendParameterString($target_url, $key, $value);
            }
        }
        if (null !== $this->token) {
            $target_url = $this->appendParameterString($target_url, self::PARAM_CSRF_TOKEN, $this->token->getToken());
        }
        if ($this->is_async) {
            $target_url = $this->appendParameterString($target_url, self::PARAM_CMD_MODE, self::CMD_MODE_ASYNC);
        }
        if (null !== $this->anchor) {
            $target_url .= $this->anchor;
        }

        return $target_url;
    }

    /**
     * @inheritDoc
     */
    public function getTrace() : ilCtrlTraceInterface
    {
        return $this->trace;
    }

    /**
     * Appends a query parameter to the given URL and returns it.
     *
     * @param string $url
     * @param string $parameter_name
     * @param mixed  $value
     * @return string
     */
    private function appendParameterString(string $url, string $parameter_name, mixed $value) : string
    {
        if (null !== $value && !is_array($value)) {
            $url .= (is_int(strpos($url, '?'))) ?
                $this->getAmpersand() . $parameter_name . '=' . $value :
                '?' . $parameter_name . '=' . $value
            ;
        }

        return $url;
    }

    /**
     * Helper function that returns the correct ampersand
     * (according to whether it should be escaped or not).
     *
     * @return string
     */
    private function getAmpersand() : string
    {
        return ($this->is_escaped) ? "&amp;" : "&";
    }
}