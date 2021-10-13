<?php

use ILIAS\HTTP\Response\Sender\ResponseSendingException;
use ILIAS\HTTP\Services as HttpService;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Filesystem\Stream\Streams;

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
     * Holds an instance of the path factory.
     *
     * @var ilCtrlPathFactory
     */
    private ilCtrlPathFactory $path_factory;

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
     */
    public function __construct()
    {
        global $DIC;

        $this->stacktrace   = [];
        $this->http         = $DIC->http();
        $this->structure    = new ilCtrlStructure();
        $this->path_factory = new ilCtrlPathFactory($this->structure);
        $this->refinery     = new Refinery(new DataFactory(), $DIC->language());

        // initialize the context without adopting
        // any values.
        $this->context = new ilCtrlContext(
            $this->path_factory,
            $this->http->wrapper()->query(),
            $this->refinery
        );
    }

    /**
     * @inheritDoc
     */
    public function callBaseClass(string $a_base_class = null) : void
    {
        $this->context->adoptRequestParameters();

        if (null === $a_base_class && null === $this->context->getBaseClass()) {
            throw new ilCtrlException(__METHOD__ . " was not given a baseclass an the request doesn't include one either.");
        }

        if (null !== $a_base_class && null === $this->context->getBaseClass()) {
            if (!$this->structure->isBaseClass($a_base_class)) {
                throw new ilCtrlException("Provided class '$a_base_class' is not a baseclass in " . __METHOD__);
            }

            $this->context->setBaseClass($a_base_class);
        }

        // no null-check needed because
        $obj_name = $this->structure->getObjNameByName(
            $this->context->getBaseClass()
        );

        $this->forwardCommand(new $obj_name());
    }

    /**
     * @inheritDoc
     */
    public function forwardCommand(object $a_gui_object)
    {
        $class_name = get_class($a_gui_object);

        // @TODO: remove this check once an interface for command classes exists.
        if (!method_exists($a_gui_object, 'executeCommand')) {
            throw new ilCtrlException("$class_name doesn't implement executeCommand().");
        }

        $this->exec_object = $a_gui_object;
        $this->populateCall($class_name, self::CMD_MODE_PROCESS);

        // with forward command we cannot progress, or set
        // the current command class. Otherwise, the path-
        // finding gets mixed up, as it can only be used in
        // getHTML() method calls.
        $this->context
            ->setCmdMode(self::CMD_MODE_PROCESS)
        ;

        return $a_gui_object->executeCommand();
    }

    /**
     * @inheritDoc
     */
    public function getHTML(object $a_gui_object, array $a_parameters = null) : string
    {
        $class_name = get_class($a_gui_object);

        // @TODO: remove this check once an interface for command classes exists.
        if (!method_exists($a_gui_object, 'getHTML')) {
            throw new ilCtrlException("$class_name doesn't implement getHTML().");
        }

        $this->exec_object = $a_gui_object;
        $this->populateCall($class_name, self::CMD_MODE_HTML);
        $this->context
            ->setCmdClass($class_name)
            ->setCmdMode(self::CMD_MODE_HTML)
        ;

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
        $post_command = $this->getPostCommand();
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

            $token = $this->getQueryParam(self::PARAM_CSRF_TOKEN);

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
        // @TODO: remove null coalescing operator before release
        //        and inform developers that null-check is needed.
        return $this->context->getCmdClass() ?? '';
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
        if (null === $a_gui_class && null === $this->exec_object) {
            return null;
        }

        if (null === $this->context->getPath()) {
            return null;
        }

        $next_cid = $this->context->getPath()->getNextCid(
            $this->getClassByObject($a_gui_class ?? $this->exec_object)
        );

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
        if (null === $this->structure->getClassCidByName($a_class)) {
            throw new ilCtrlException("Cannot find provided class '$a_class' in the control structure.");
        }

        $parameters = [];
        $permanent_parameters = $this->structure->getSavedParametersByClass($a_class);
        if (null !== $permanent_parameters) {
            foreach ($permanent_parameters as $parameter) {
                $parameters[$parameter] = $this->getQueryParam($parameter);
            }
        }

        $temporary_parameters = $this->structure->getParametersByClass($a_class);
        if (null !== $temporary_parameters) {
            // override existing ones, as temporary parameters
            // are prioritised over fetched ones.
            foreach ($temporary_parameters as $key => $value) {
                $parameters[$key] = $value;
            }
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
        string $a_cmd = null,
        string $a_anchor = null,
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
        string $a_cmd = null,
        string $a_anchor = null,
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
        string $a_cmd = null,
        string $a_anchor = null,
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
        string $a_cmd = null,
        string $a_anchor = null,
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
        string $a_cmd = null,
        string $a_anchor = null,
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
        string $a_cmd = null,
        string $a_anchor = null,
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
            !str_contains($target_url, "://") &&
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
        $this->context->setTargetScript($a_target_script);
    }

    /**
     * @inheritDoc
     */
    public function isAsynch() : bool
    {
        return $this->context->isAsync();
    }

    /**
     * @inheritDoc
     */
    public function setReturn(object $a_gui_obj, string $a_cmd = null) : void
    {
        $this->setReturnByClass($this->getClassByObject($a_gui_obj), $a_cmd);
    }

    /**
     * @inheritDoc
     */
    public function setReturnByClass(string $a_class, string $a_cmd = null) : void
    {
        $this->structure->setReturnTargetByClass(
            $a_class,
            $this->getLinkTargetByClass(
                $a_class,
                $a_cmd
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function returnToParent(object $a_gui_obj, string $a_anchor = null) : void
    {
        $class_name = $this->getClassByObject($a_gui_obj);
        $target_url = $this->getParentReturnByClass($class_name);
        if (null === $target_url) {
            throw new ilCtrlException("ilCtrl was not yet provided with a return-target for class '$class_name'");
        }

        $target_url = $this->appendParameterString(
            $target_url,
            self::PARAM_REDIRECT,
            $class_name
        );

        $this->redirectToURL($target_url);
    }

    /**
     * @inheritDoc
     */
    public function getParentReturn(object $a_gui_obj) : ?string
    {
        return $this->structure->getReturnTargetByClass($this->getClassByObject($a_gui_obj));
    }

    /**
     * @inheritDoc
     */
    public function getParentReturnByClass(string $a_class) : ?string
    {
        return $this->structure->getReturnTargetByClass($a_class);
    }

    /**
     * @inheritDoc
     */
    public function getRedirectSource() : ?string
    {
        return $this->context->getRedirectSource();
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
        if (null === $class_cid) {
            return false;
        }

        return str_contains(
            $this->context->getPath()->getCidPath() ?? '',
            $class_cid
        );
    }

    /**
     * @inheritDoc
     */
    public function getCurrentClassPath() : array
    {
        if (null === $this->context->getPath()) {
            return [];
        }

        $class_paths = [];
        foreach ($this->context->getPath()->getCidArray(SORT_ASC) as $cid) {
            $class_paths[] = $this->structure->getObjNameByCid($cid);
        }

        return $class_paths;
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
     * Returns the current $_POST command.
     *
     * @return string|null
     */
    private function getPostCommand() : ?string
    {
        if ($this->http->wrapper()->post()->has(self::PARAM_CMD)) {
            return $this->http->wrapper()->post()->retrieve(
                self::PARAM_CMD,
                $this->refinery->custom()->transformation(
                    static function ($value) {
                        if (!empty($value)) {
                            if (is_array($value)) {
                                // this most likely only works by accident, but
                                // the selected or clicked command button will
                                // always be sent as first array entry. This
                                // should definitely be done differently.
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
     * (that function is horrific, I'm sorry little one)
     *
     * @param array|string $a_class
     * @param string|null  $a_cmd
     * @param string|null  $a_anchor
     * @param bool         $is_async
     * @param bool         $is_escaped
     * @param bool         $is_post
     * @return string|null
     * @throws ilCtrlException
     */
    private function getTargetUrl(
        $a_class,
        string $a_cmd = null,
        string $a_anchor = null,
        bool $is_async = false,
        bool $is_escaped = false,
        bool $is_post = false
    ) : ?string {
        if (empty($a_class)) {
            throw new ilCtrlException(__METHOD__ . " was provided with an empty class or class-array.");
        }

        $is_array = is_array($a_class);
        if ($is_array) {
            $base_class = (string) $a_class[array_key_first($a_class)];
            // if the provided classes don't include a baseclass
            // at first position, add the context's baseclass to
            // the first position instead.
            if (!$this->structure->isBaseClass($base_class)) {
                if (null === $this->context->getBaseClass()) {
                    throw new ilCtrlException("First class in array is not a baseclass and the current context has no baseclass yet.");
                }

                array_unshift($a_class, $this->context->getBaseClass());
                $base_class = $this->context->getBaseClass();
            }
        } else {
            if (null === $this->context->getBaseClass() && !$this->structure->isBaseClass($a_class)) {
                throw new ilCtrlException("Provided class is not a baseclass and the current context has no baseclass yet.");
            }

            // at this point, the current context either knows a
            // baseclass, or the provided one is one itself, so
            // the null coalescing operator can be used.
            $base_class = $this->context->getBaseClass() ?? $a_class;
        }

        $path = ($is_array) ?
            $this->path_factory->arrayClass($a_class) :
            $this->path_factory->singleClass($this->context, $a_class)
        ;

        if (null !== ($exception = $path->getException())) {
            throw $exception;
        }

        $target_url = $this->context->getTargetScript();
        $cmd_class  = ($is_array) ?
            $a_class[array_key_last($a_class)] :
            $a_class
        ;

        $target_url = $this->appendParameterString(
            $target_url,
            self::PARAM_BASE_CLASS,
            $base_class,
            $is_escaped
        );

        // only append the cid path and command class params
        // if they exist.
        if (null !== $path->getNextCid($base_class)) {
            $target_url = $this->appendParameterString(
                $target_url,
                self::PARAM_CID_PATH,
                $path->getCidPath(),
                $is_escaped
            );

            $target_url = $this->appendParameterString(
                $target_url,
                self::PARAM_CMD_CLASS,
                $cmd_class,
                $is_escaped
            );
        }

        // collect all set parameters from the structure and
        // append them to the target url.
        if ($is_array) {
            foreach ($a_class as $current_class) {
                $target_url = $this->appendParameterStringsByClass($current_class, $target_url, $is_escaped);
            }
        } else {
            $target_url = $this->appendParameterStringsByClass($a_class, $target_url, $is_escaped);
        }

        // if the target url is generated for form actions,
        // the command must be set to 'post'.
        if ($is_post) {
            $target_url = $this->appendParameterString(
                $target_url,
                self::PARAM_CMD,
                self::CMD_POST,
                $is_escaped
            );
        }

        // the actual command is appended as fallback command
        // for form actions and 'normal' get requests.
        if (!empty($a_cmd)) {
            $target_url = $this->appendParameterString(
                $target_url,
                ($is_post) ? self::PARAM_CMD_FALLBACK : self::PARAM_CMD,
                $a_cmd,
                $is_escaped
            );
        }

        // append a csrf token if the command is considered
        // unsafe, regardless of the request method.
        if (!$this->isCmdSecure($cmd_class, $a_cmd)) {
            global $DIC;

            $token = new ilCtrlToken(
                $DIC->database(),
                $DIC->user()
            );

            $target_url = $this->appendParameterString(
                $target_url,
                self::PARAM_CSRF_TOKEN,
                $token->getToken(),
                $is_escaped
            );
        }

        if ($is_async) {
            $target_url = $this->appendParameterString(
                $target_url,
                self::PARAM_CMD_MODE,
                self::CMD_MODE_ASYNC,
                $is_escaped
            );
        }

        if (!empty($a_anchor)) {
            $target_url .= "#$a_anchor";
        }

        return $target_url;
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
     * @param string      $cmd_class
     * @param string|null $cmd
     * @return bool
     */
    private function isCmdSecure(string $cmd_class, string $cmd = null) : bool
    {
        if (null === $cmd) {
            return true;
        }

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
     * Appends all parameters for a given class to the given URL.
     *
     * @param string $class_name
     * @param string $target_url
     * @param bool   $is_escaped
     * @return string
     * @throws ilCtrlException
     */
    private function appendParameterStringsByClass(string $class_name, string $target_url, bool $is_escaped = false) : string
    {
        $class_parameters = $this->getParameterArrayByClass($class_name);
        if (!empty($class_parameters)) {
            foreach ($class_parameters as $key => $value) {
                $target_url = $this->appendParameterString(
                    $target_url,
                    $key,
                    $value,
                    $is_escaped
                );
            }
        }

        return $target_url;
    }

    /**
     * Appends a query parameter to the given URL and returns it.
     *
     * @param string $url
     * @param string $parameter_name
     * @param mixed  $value
     * @param bool   $is_escaped
     * @return string
     */
    private function appendParameterString(string $url, string $parameter_name, $value, bool $is_escaped = false) : string
    {
        // only append value if it exists and can be cast
        // to string.
        if (null !== $value && !is_array($value) && !is_object($value)) {
            // declare ampersand escaped or not, according to
            // the given argument.
            $ampersand = ($is_escaped) ? '&amp;' : '&';

            // check if the given url already contains the given
            // parameter name.
            if (preg_match("/($ampersand|\?)$parameter_name(=|$)/", $url)) {
                // replace the value appended to the given parameter
                // name by the provided one.
                $url = preg_replace("/(?<=($parameter_name=))([^&]*|$)/", $value, $url);
            } else {
                // append the parameter key => value pair and prepend
                // a question mark or ampersand, determined by whether
                // it's the first query param or not.
                $url .= (str_contains($url, '?')) ?
                    $ampersand . $parameter_name . '=' . $value :
                    '?' . $parameter_name . '=' . $value
                ;
            }
        }

        return $url;
    }

    /**
     * Helper function that populates a call in the current stacktrace.
     *
     * @param string $class_name
     * @param string $cmd_mode
     */
    private function populateCall(string $class_name, string $cmd_mode) : void
    {
        $obj_name = $this->structure->getObjNameByName($class_name);

        $this->stacktrace[] = [
            self::PARAM_CMD_CLASS => $obj_name,
            self::PARAM_CMD_MODE  => $cmd_mode,
            self::PARAM_CMD       => $this->getCmd(),
        ];
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
