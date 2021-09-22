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
    private const CMD_POST = 'post';

    /**
     * Holds a token generated for the current user.
     *
     * @var ilCtrlTokenInterface
     */
    private ilCtrlTokenInterface $token;

    /**
     * Holds the currently read control structure.
     *
     * @var ilCtrlStructureInterface
     */
    private ilCtrlStructureInterface $structure;

    /**
     * Holds the trace from baseclass to command-class of the
     * current target.
     *
     * @var ilCtrlTraceInterface
     */
    private ilCtrlTraceInterface $trace;

    /**
     * Holds the target-script of the current target.
     *
     * @var string
     */
    private string $target_script = 'ilias.php';

    /**
     * Holds whether the target is asynchronous or not.
     *
     * If the target is asynchronous, an async flag will be appended
     * for controllers to recognize them.
     *
     * @var bool
     */
    private bool $is_async = false;

    /**
     * Holds whether the target is used for XML content or not.
     *
     * If the target is used for XML content, certain characters
     * will be escaped during link generations.
     *
     * @var bool
     */
    private bool $is_xml = false;

    /**
     * Holds the baseclass of the current target.
     *
     * @var string
     */
    private string $base_class;

    /**
     * Holds the executing class of the current target.
     *
     * @var string
     */
    private string $cmd_class;

    /**
     * Holds the command of the current target.
     *
     * @var string
     */
    private string $cmd;

    /**
     * Holds the anchor of the current target.
     *
     * @var string
     */
    private string $anchor;

    /**
     * @param ilCtrlTokenInterface     $token
     * @param ilCtrlStructureInterface $structure
     * @param string                   $base_class
     */
    public function __construct(ilCtrlTokenInterface $token, ilCtrlStructureInterface $structure, string $base_class)
    {
        $this->token      = $token;
        $this->structure  = $structure;
        $this->base_class = strtolower($base_class);

        $this->trace = new ilCtrlTrace(
            $this->structure,
            $this->base_class
        );
    }

    /**
     * @inheritDoc
     */
    public function setTargetScript(string $target_script) : ilCtrlTargetInterface
    {
        $this->target_script = $target_script;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setCmdClass(string $class_name) : ilCtrlTarget
    {
        $this->cmd_class = strtolower($class_name);
        $this->trace->appendClass($this->cmd_class);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setCmd(string $cmd) : ilCtrlTarget
    {
        $this->cmd = $cmd;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAnchor(string $anchor) : ilCtrlTargetInterface
    {
        $this->anchor = $anchor;
        return $this;
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
    public function setXml(bool $is_xml) : ilCtrlTargetInterface
    {
        $this->is_xml = $is_xml;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLinkTarget() : ?string
    {
        return $this->getTargetURL();
    }

    /**
     * @inheritDoc
     */
    public function getFormAction() : ?string
    {
        return $this->getTargetURL($this->cmd);
    }

    /**
     * @param string|null $post_cmd
     * @return string|null
     */
    private function getTargetURL(string $post_cmd = null) : ?string
    {
        // abort if no valid trace is found to the current
        // command class.
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
            $target_url = $this->appendParameterString($target_url, self::PARAM_TRACE, $this->trace);
            $target_url = $this->appendParameterString($target_url, self::PARAM_CMD_CLASS, $this->cmd_class);
        }

        // if no post command is provided, the current command
        // can be used if it has been set.
        if (null === $post_cmd && null !== $this->cmd) {
            $target_url = $this->appendParameterString($target_url, self::PARAM_CMD, $this->cmd);
        }

        // if a post command is provided, the command must be set
        // to CMD_POST and the current command has to be appended
        // as a fallback.
        if (null !== $post_cmd) {
            $target_url = $this->appendParameterString($target_url, self::PARAM_CMD, self::CMD_POST);
            $target_url = $this->appendParameterString($target_url, self::PARAM_CMD_FALLBACK, $post_cmd);
        }

        // append all existing parameters of each class known by trace.
        foreach ($this->trace->getCidPieces() as $cid) {
            $parameters = $this->structure->getParametersByClass(
                $this->structure->getClassNameByCid($cid)
            );

            if (!empty($parameters)) {
                foreach ($parameters as $key => $value) {
                    $target_url = $this->appendParameterString($target_url, $key, $value);
                }
            }
        }

        if (!$this->isSafe()) {
            $target_url = $this->appendParameterString(
                $target_url,
                self::PARAM_CSRF_TOKEN,
                $this->token->get()
            );
        }

        if ($this->is_async) {
            $target_url = $this->appendParameterString(
                $target_url,
                self::PARAM_CMD_MODE,
                self::CMD_MODE_ASYNC
            );
        }

        if (null !== $this->anchor) {
            $target_url .= "#$this->anchor";
        }

        return $target_url;
    }

    /**
     * Appends a parameter => value pair to the given URL.
     *
     * @param string $url
     * @param string $parameter_name
     * @param mixed  $value
     * @return string
     */
    private function appendParameterString(string $url, string $parameter_name, mixed $value) : string
    {
        if (null !== $value) {
            // determine the ampersand according to whether
            // the target is used for XML content or not.
            $amp = ($this->is_xml) ? "&amp;" : "&";

            // append the given parameter => value pair to the
            // url - use question-mark the first time.
            $url = (is_int(strpos($url, '?'))) ?
                $url . $amp. $parameter_name . '=' . $value :
                $url . '?' . $parameter_name . '=' . $value
            ;
        }

        return $url;
    }

    /**
     * Returns whether the current target can be safely executed
     * or not.
     *
     * @return bool
     */
    private function isSafe() : bool
    {
        $class_name = (null !== $this->cmd_class) ?
            $this->structure->getQualifiedClassName($this->cmd_class) :
            $this->structure->getQualifiedClassName($this->base_class)
        ;

        $class_obj  = new $class_name();
        if ($class_obj instanceof ilCtrlSecurityInterface) {
            return in_array($this->cmd, $class_obj->getSafeCommands(), true);
        }

        return false;
    }
}