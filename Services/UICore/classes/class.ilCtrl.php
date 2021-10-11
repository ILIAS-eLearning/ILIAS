<?php

use ILIAS\HTTP\Services as HttpService;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Response\Sender\ResponseSendingException;

/**
 * Class ilCtrl provides processing control methods. A global
 * instance is available through $DIC->ctrl() or $ilCtrl.
 *
 * @author Thibeau Fuhrer <thf@studer.raimann.ch>
 */
class ilCtrl implements ilCtrlInterface
{
    /**
     * Holds an instance of the HTTP service.
     *
     * @var HttpService
     */
    private HttpService $http;

    /**
     * Holds an instance of the refinery factory.
     *
     * @var Refinery
     */
    private Refinery $refinery;

    /**
     * Holds an instance of the currently read control structure.
     *
     * @var ilCtrlStructureInterface
     */
    private ilCtrlStructureInterface $structure;

    /**
     * Holds the current context information.
     *
     * @var ilCtrlContextInterface
     */
    private ilCtrlContextInterface $context;

    /**
     * Holds a history of calls made with the current ilCtrl instance.
     *
     * @var array<int, string[]>
     */
    private array $stacktrace;

    /**
     * Holds an instance of the object that is currently executed.
     *
     * @var object
     */
    private object $exec_object;

    /**
     * ilCtrl Constructor
     *
     * @throws ilCtrlException if the artifact cannot be included.
     */
    public function __construct()
    {
        global $DIC;

        $this->stacktrace = [];
        $this->http       = $DIC->http();
        $this->context    = new ilCtrlContext();
        $this->structure  = new ilCtrlStructure($DIC->database());
        $this->refinery   = new Refinery(new DataFactory(), $DIC->language());
    }

    /**
     * @inheritDoc
     */
    public function initBaseClass(string $a_base_class) : void
    {
        // abort if the given classname is not a baseclass.
        if (!$this->structure->isBaseClass($a_base_class)) {
            throw new ilCtrlException("Class '$a_base_class' is not a known baseclass.");
        }

        // reinitialize the current context with the new
        // baseclass (the stacktrace is also reset).
        $this->context = new ilCtrlContext($a_base_class);
        $this->stacktrace = [];
    }

    /**
     * @inheritDoc
     */
    public function callBaseClass() : void
    {
        $base_class = $this->getBaseClass();
        if (null === $base_class) {
            throw new ilCtrlException("ilCtrl cannot determine the current baseclass.");
        }

        $obj_name = $this->structure->getObjNameByName($base_class);
        $this->forwardCommand(new $obj_name());
    }

    /**
     * @inheritDoc
     */
    public function forwardCommand(object $a_gui_object)
    {
        $class_name = get_class($a_gui_object);
        if (!method_exists($a_gui_object, 'executeCommand')) {
            throw new ilCtrlException("$class_name doesn't implement executeCommand().");
        }

        $this->exec_object = $a_gui_object;

        return $a_gui_object->executeCommand();
    }

    /**
     * @inheritDoc
     */
    public function getHTML(object $a_gui_object, array $a_parameters = null) : string
    {
        $class_name = get_class($a_gui_object);
        if (!method_exists($a_gui_object, 'getHTML')) {
            throw new ilCtrlException("$class_name doesn't implement getHTML().");
        }

        $this->exec_object = $a_gui_object;

        return (null !== $a_parameters) ?
            $a_gui_object->getHTML($a_parameters) :
            $a_gui_object->getHTML()
        ;
    }

    /**
     * @inheritDoc
     */
    public function getCmd(string $fallback_command = null) : ?string
    {
        // retrieve $_GET and $_POST parameters.
        $post_command = $this->getPostParam(self::PARAM_CMD);
        $get_command  = $this->getQueryParam(self::PARAM_CMD);

        // if the $_GET command is 'post', either the $_POST
        // command or $_GETs fallback command is used.
        $command = (self::CMD_POST === $get_command) ?
            $post_command ?? $this->getQueryParam(self::PARAM_CMD_FALLBACK) :
            $get_command
        ;

        // if no command was found, check the current context.
        if (null === $command && null !== $this->context->getCmd()) {
            $command = $this->context->getCmd();
        }

        if (null !== $command) {

            // @TODO: fix security issue
            return $command;

            // if the executing object implements ilCtrl security,
            // the command is returned if it's a safe one.
            if ($this->isCmdSecure($this->getCmdClass(), $command)) {
                return $command;
            }

            global $DIC;
            $stored_token = new ilCtrlToken(
                $DIC->database(),
                $DIC->user()
            );

            // the CSRF token could either be in $_POST or $_GET.
            $token = $this->getQueryParam(self::PARAM_CSRF_TOKEN) ??
                $this->getPostParam(self::PARAM_CSRF_TOKEN)
            ;

            // if the command is considered unsafe, the CSRF token
            // has to bee valid to retrieve the command.
            if (null !== $token && $stored_token->verifyWith($token)) {
                return $command;
            }
        }

        return $fallback_command;
    }

    /**
     * @inheritDoc
     */
    public function setCmd(string $a_cmd) : void
    {
        if (!empty($a_cmd)) {
            $this->context->setCmd($a_cmd);
        }
    }

    /**
     * @inheritDoc
     */
    public function getCmdClass() : ?string
    {
        return $this->context->getCmdClass();
    }

    /**
     * @inheritDoc
     */
    public function setCmdClass($a_cmd_class) : void
    {
        if (!empty($a_cmd_class)) {
            $this->context->setCmdClass($a_cmd_class);
        }
    }

    /**
     * @inheritDoc
     */
    public function getNextClass($a_gui_class = null) : ?string
    {
        $next_cid = $this->context->getPath()->getNextCid();
        if (null !== $next_cid) {
            return $this->structure->getClassNameByCid($next_cid);
        }

        return null;

    }

    /**
     * @inheritDoc
     */
    public function saveParameter(object $a_gui_obj, $a_parameter) : void
    {
        $this->saveParameterByClass($this->getClassByObject($a_gui_obj), $a_parameter);
    }

    /**
     * @inheritDoc
     */
    public function saveParameterByClass(string $a_class, $a_parameter) : void
    {
        if (!empty($a_parameter)) {
            if (is_array($a_parameter)) {
                foreach ($a_parameter as $parameter) {
                    $this->structure->saveParameterByClass($a_class, $parameter);
                }
            } else {
                $this->structure->saveParameterByClass($a_class, $a_parameter);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function setParameter(object $a_gui_obj, string $a_parameter, $a_value) : void
    {
        $this->setParameterByClass($this->getClassByObject($a_gui_obj), $a_parameter, $a_value);
    }

    /**
     * @inheritDoc
     */
    public function setParameterByClass(string $a_class, string $a_parameter, $a_value) : void
    {
        $this->structure->setParameterByClass($a_class, $a_parameter, $a_value);
    }

    /**
     * @inheritDoc
     */
    public function getParameterArray(object $a_gui_obj) : array
    {
        return $this->getParameterArrayByClass($this->getClassByObject($a_gui_obj));
    }

    /**
     * @inheritDoc
     */
    public function getParameterArrayByClass(string $a_class, string $a_cmd = null) : array
    {
        $parameters = [];

        $permanent_parameters = $this->structure->getSavedParametersByClass($a_class);
        if (null !== $permanent_parameters) {
            foreach ($permanent_parameters as $parameter) {
                $parameters[$parameter] = $this->getQueryParam($parameter);
            }
        }

        $temporary_parameters = $this->structure->getParametersByClass($a_class);
        if (null !== $temporary_parameters) {
            $parameters = array_merge_recursive($parameters, $temporary_parameters);
        }

        return $parameters;
    }

    /**
     * @inheritDoc
     */
    public function clearParameters(object $a_gui_obj) : void
    {
        $this->clearParametersByClass($this->getClassByObject($a_gui_obj));
    }

    /**
     * @inheritDoc
     */
    public function clearParametersByClass(string $a_class) : void
    {
        $this->structure->removeSavedParametersByClass($a_class);
        $this->structure->removeParametersByClass($a_class);
    }

    /**
     * @inheritDoc
     */
    public function clearParameterByClass(string $a_class, string $a_parameter) : void
    {
        $this->structure->removeSingleParameterByClass($a_class, $a_parameter);
    }

    /**
     * @inheritDoc
     */
    public function getLinkTarget(
        object $a_gui_obj,
        string $a_cmd = "",
        string $a_anchor = "",
        bool $is_async = false,
        bool $has_xml_style = false
    ) : string {
        return $this->getLinkTargetByClass(
            $this->getClassByObject($a_gui_obj),
            $a_cmd,
            $a_anchor,
            $is_async,
            $has_xml_style
        );
    }

    /**
     * @inheritDoc
     */
    public function getLinkTargetByClass(
        $a_class,
        string $a_cmd = "",
        string $a_anchor = "",
        bool $is_async = false,
        bool $has_xml_style = false
    ) : string {
        return $this->getTargetUrl(
            $a_class,
            $a_cmd,
            $a_anchor,
            $is_async,
            $has_xml_style
        ) ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getFormAction(
        object $a_gui_obj,
        string $a_fallback_cmd = "",
        string $a_anchor = "",
        bool $is_async = false,
        bool $has_xml_style = false
    ) : string {
        return $this->getFormActionByClass(
            $this->getClassByObject($a_gui_obj),
            $a_fallback_cmd,
            $a_anchor,
            $is_async,
            $has_xml_style
        );
    }

    /**
     * @inheritDoc
     */
    public function getFormActionByClass(
        $a_class,
        string $a_fallback_cmd = "",
        string $a_anchor = "",
        bool $is_async = false,
        bool $has_xml_style = false
    ) : string {
        return $this->getTargetUrl(
            $a_class,
            $a_fallback_cmd,
            $a_anchor,
            $is_async,
            $has_xml_style,
            true
        ) ?? '';
    }

    /**
     * @inheritDoc
     */
    public function redirect(
        object $a_gui_obj,
        string $a_cmd = "",
        string $a_anchor = "",
        bool $is_async = false
    ) : void {
        $this->redirectByClass(
            $this->getClassByObject($a_gui_obj),
            $a_cmd,
            $a_anchor,
            $is_async
        );
    }

    /**
     * @inheritDoc
     */
    public function redirectByClass(
        $a_class,
        string $a_cmd = "",
        string $a_anchor = "",
        bool $is_async = false
    ) : void {
        $this->redirectToURL(
            $this->getLinkTargetByClass(
                $a_class,
                $a_cmd,
                $a_anchor,
                $is_async
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function redirectToURL(string $target_url) : void
    {
        // prepend the ILIAS HTTP path if it wasn't already.
        if (defined("ILIAS_HTTP_PATH") &&
            !is_int(strpos($target_url, "://")) &&
            !str_starts_with($target_url, "/")
        ) {
            $target_url = ILIAS_HTTP_PATH . "/" . $target_url;
        }

        // this line can be dropped after discussion with TB or JF,
        // it keeps the functionality of UI plugin hooks alive.
        $target_url = $this->modifyUrlWithPluginHooks($target_url);

        // there's an exceptional case for asynchronous file uploads
        // where a json response is delivered.
        if ('application/json' === $this->http->request()->getHeaderLine('Accept')) {
            try {
                $body = Streams::ofString(
                    json_encode(
                        [
                            'redirect_url' => $target_url,
                            'success' => true,
                            'message' => 'called redirect after asynchronous file-upload request.',
                        ],
                        JSON_THROW_ON_ERROR
                    )
                );
            } catch (Throwable $exception) {
                $body = Streams::ofString($exception->getMessage());
            }

            $response = $this->http->response()->withBody($body);
        } else {
            $response = $this->http->response()->withAddedHeader('Location', $target_url);
        }

        $this->http->saveResponse($response);

        // manually trigger session_write_close() due to exceptions stored
        // in the ILIAS database, otherwise this method is called by exit()
        // which leads to the exceptions not being written to the database.
        session_write_close();

        try {
            $this->http->sendResponse();
        } catch (ResponseSendingException $e) {
            header("Location: $target_url");
            echo json_encode(
                $response->getBody()->getContents(),
                JSON_THROW_ON_ERROR
            );
        } catch (Throwable $exception) {
            echo "
                {
                    success: false,
                    message: {$exception->getMessage()}
                }
            ";
        }

        exit;
    }

    /**
     * @inheritDoc
     */
    public function setContextObject(int $obj_id, string $obj_type) : void
    {
        // cannot process object without object type.
        if (!empty($obj_type)) {
            $this->context->setObjId($obj_id);
            $this->context->setObjType($obj_type);
        }
    }

    /**
     * @inheritDoc
     */
    public function getContextObjId() : ?int
    {
        return $this->context->getObjId();
    }

    /**
     * @inheritDoc
     */
    public function getContextObjType() : ?string
    {
        return $this->context->getObjType();
    }

    /**
     * @inheritDoc
     */
    public function getCallHistory() : array
    {
        return $this->stacktrace;
    }

    /**
     * @inheritDoc
     */
    public function lookupClassPath(string $a_class) : string
    {
        $path = $this->structure->getRelativePathByName($a_class);
        if (null === $path) {
            throw new ilCtrlException("Class '$a_class' cannot be found in the control structure.");
        }

        return $path;
    }

    /**
     * @inheritDoc
     */
    public function getClassForClasspath(string $a_class_path) : string
    {
        $path_info = pathinfo($a_class_path);

        return substr($path_info['basename'], 6, -4);
    }

    /**
     * @inheritDoc
     */
    public function setTargetScript(string $a_target_script) : void
    {
        if (null === $this->target) {
            throw new ilCtrlException("REPLACE_THIS_MESSAGE");
        }

        $this->target->setTargetScript($a_target_script);
    }

    /**
     * @inheritDoc
     */
    public function isAsynch() : bool
    {
        return (ilCtrlTarget::CMD_MODE_ASYNC === $this->getQueryParam(ilCtrlTarget::PARAM_CMD_MODE));
    }

    /**
     * @inheritDoc
     */
    public function setReturn(object $a_gui_obj, string $a_cmd) : void
    {
//        $this->setReturnByClass($this->getClassByObject($a_gui_obj), $a_cmd);
    }

    /**
     * @inheritDoc
     */
    public function setReturnByClass(string $a_class, string $a_cmd) : void
    {

    }

    /**
     * @inheritDoc
     */
    public function returnToParent(object $a_gui_obj, string $a_anchor = null) : void
    {
        throw new ilCtrlException("Not yet implemented");
    }

    /**
     * @inheritDoc
     */
    public function getParentReturn(object $a_gui_obj)
    {
        throw new ilCtrlException("Not yet implemented");
    }

    /**
     * @inheritDoc
     */
    public function getParentReturnByClass(string $a_class)
    {
        throw new ilCtrlException("Not yet implemented");
    }

    /**
     * @inheritDoc
     */
    public function getReturnClass($a_class)
    {
        throw new ilCtrlException("Not yet implemented");
    }

    /**
     * @inheritDoc
     */
    public function getRedirectSource() : ?string
    {
        return $this->getQueryParam(ilCtrlTarget::PARAM_REDIRECT);
    }

    /**
     * @inheritDoc
     */
    public function insertCtrlCalls($a_parent, $a_child, string $a_comp_prefix) : void
    {
        throw new ilCtrlException(__METHOD__ . " is deprecated and must not be used.");
    }

    /**
     * @inheritDoc
     */
    public function checkCurrentPathForClass(string $gui_class) : bool
    {
        $class_cid = $this->structure->getClassCidByName($gui_class);
        if (null === $class_cid || null === $this->target) {
            return false;
        }

        return str_contains(
            $this->target->getTrace()->getCidPath(),
            $class_cid
        );
    }

    /**
     * @inheritDoc
     */
    public function getCurrentClassPath() : array
    {
        if (null === $this->target || !$this->target->getTrace()->isValid()) {
            return [];
        }

        $class_path = [];
        foreach ($this->target->getTrace()->getCidPieces() as $cid) {
            $class_path[] = $this->structure->getObjNameByCid($cid);
        }

        return $class_path;
    }

    private function initTarget(string $base_class = null) : void
    {
        $base_class = $base_class ?? $this->getBaseClass();

        // abort if the baseclass is not provided or unknown.
        if (null === $base_class || !$this->structure->isBaseClass($base_class)) {
            throw new ilCtrlException("ilCtrl was not provided with a known baseclass.");
        }

        $this->target = new ilCtrlTarget(
            $this->structure,
            $base_class
        );

        $this->target->withInformation([
            ilCtrlTarget::PARAM_CMD => $this->getQueryParam()
        ]);
    }

    /**
     * Returns the baseclass of the current ilCtrl instance.
     *
     * This method prioritises a baseclass passed by $_GET over
     * the baseclass ilCtrl was initialized with.
     *
     * @return string|null
     */
    private function getBaseClass() : ?string
    {
        // target information will never be found in $_POST,
        // therefore only query-params are fetched.
        return $this->getQueryParam(self::PARAM_BASE_CLASS) ?? $this->context->getBaseClass();
    }

    /**
     * Returns a parameter with the given name from the current GET
     * request.
     *
     * @param string $parameter_name
     * @return string|null
     */
    private function getQueryParam(string $parameter_name) : ?string
    {
        if ($this->http->wrapper()->query()->has($parameter_name)) {
            return $this->http->wrapper()->query()->retrieve(
                $parameter_name,
                $this->refinery->to()->string()
            );
        }

        return null;
    }

    /**
     * Returns a parameter with the given name from the current POST
     * request.
     *
     * @param string $parameter_name
     * @return string|null
     */
    private function getPostParam(string $parameter_name) : ?string
    {
        if ($this->http->wrapper()->post()->has($parameter_name)) {
            return $this->http->wrapper()->post()->retrieve(
                $parameter_name,
                $this->refinery->custom()->transformation(
                    static function ($value) {
                        if (!empty($value)) {
                            if (is_array($value)) {
                                // this most likely only works by accident, but
                                // the selected or clicked command button will
                                // always be sent as first array entry. This
                                // should definitely be done better.
                                return array_key_first($value);
                            }

                            return (string) $value;
                        }

                        return null;
                    }
                )
            );
        }

        return null;
    }

    /**
     * Helper function that returns a target URL string.
     *
     * @param array|string $a_class
     * @param string       $a_cmd
     * @param string       $a_anchor
     * @param bool         $is_async
     * @param bool         $has_xml_style
     * @param bool         $is_post
     * @return string|null
     * @throws ilCtrlException
     */
    private function getTargetUrl(
        $a_class,
        string $a_cmd = "",
        string $a_anchor = "",
        bool $is_async = false,
        bool $has_xml_style = false,
        bool $is_post = false
    ) : ?string {
        if (empty($a_class)) {
            throw new ilCtrlException(__METHOD__ . " was provided with an empty class or class-array.");
        }

        $is_class_path = is_array($a_class);
        $base_class    = ($is_class_path) ? $a_class[0] : $a_class;

        // initialize new target if currently no target
        // is set or the target class is a known baseclass.
        $target = (null === $this->target || $this->structure->isBaseClass($base_class)) ?
            new ilCtrlTarget($this->structure, $base_class) : $this->target
        ;

        // makes PHPStorm happy, null-pointer never occurs.
        if (null === $target) { $x = 1; }

        if ($is_class_path) {
            $target->appendCmdClassArray($a_class);
        } else {
            $target->appendCmdClass($a_class);
        }

        if (!$target->getTrace()->isValid()) {
            throw new ilCtrlException("ilCtrl cannot determine a trace to the provided command class.");
        }

        // once $target->getTrace()->isValid() is called no
        // further structure checks are needed.
        foreach ($target->getTrace()->getCidPieces() as $cid) {
            $target->setParameters(
                $this->getParameterArrayByClass(
                    // so this call doesn't need a null check.
                    $this->structure->getClassNameByCid($cid)
                )
            );
        }

        $target
            ->setCmd($a_cmd)
            ->setAsync($is_async)
            ->setEscaped($has_xml_style)
            ->setAnchor($a_anchor)
        ;

        if ($this->isCmdSecure($target->getCurrentCmdClass(), $a_cmd)) {
            global $DIC;

            $target->setToken(
                new ilCtrlToken(
                    $DIC->database(),
                    $DIC->user()
                )
            );
        }

        return $target->getTargetUrl($is_post);
    }

    /**
     * This helper function wraps the deprecated UI functionality that
     * modifies a URL target and "hacks into" existing HTML.
     *
     * (Tbh I don't really get that mechanism, but it stays for now.)
     *
     * @param string $target_url
     * @return string
     */
    private function modifyUrlWithPluginHooks(string $target_url) : string
    {
        $ui_plugins = ilPluginAdmin::getActivePluginsForSlot('Services', 'UIComponent', 'uihk');
        if (!empty($ui_plugins)) {
            foreach ($ui_plugins as $plugin_name) {
                /** @var $plugin_instance ilUserInterfaceHookPlugin */
                $plugin_instance = ilPluginAdmin::getPluginObject(
                    'Services', 'UIComponent', 'uihk', $plugin_name
                );

                $html = $plugin_instance
                    ->getUIClassInstance()
                    ->getHTML(
                        'Services/Utilities',
                        'redirect',
                        ["html" => $target_url]
                    )
                ;

                if (ilUIHookPluginGUI::KEEP === $html['mode']) {
                    $target_url = $plugin_instance
                        ->getUIClassInstance()
                        ->modifyHTML(
                            $target_url,
                            $html
                        )
                    ;
                }
            }
        }

        return $target_url;
    }

    /**
     * Returns whether a given command is considered safe or not.
     *
     * @param string $cmd_class
     * @param string $cmd
     * @return bool
     */
    private function isCmdSecure(string $cmd_class, string $cmd) : bool
    {
        $obj_name = $this->structure->getObjNameByName($cmd_class);
        if (is_a($obj_name, ilCtrlSecurityInterface::class, true)) {
            if (null !== $this->exec_object) {
                $obj = ($obj_name !== get_class($this->exec_object)) ?
                    new $obj_name() : $this->exec_object
                ;
            } else {
                $obj = new $obj_name();
            }

            if (in_array($cmd, $obj->getSafeCommands(), true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Populates a call to the given target in the stacktrace of
     * this ilCtrl instance.
     *
     * @param ilCtrlTarget $target
     */
    private function populateCall(ilCtrlTarget $target) : void
    {
        $this->stacktrace[] = $target;
    }

    /**
     * Helper function that returns the class name of a mixed
     * (object or string) parameter.
     *
     * @param object|string $object
     * @return string
     */
    private function getClassByObject($object) : string
    {
        return (is_object($object)) ? get_class($object) : $object;
    }
}
