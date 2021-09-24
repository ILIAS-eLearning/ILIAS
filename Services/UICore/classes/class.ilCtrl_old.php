<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\HTTP\Services as HttpServices;
use ILIAS\HTTP\Wrapper\RequestWrapper;

/**
 * Class ilCtrl provides processing control methods. A global
 * instance is available through $DIC->ctrl() or $ilCtrl.
 *
 * @author Thibeau Fuhrer <thf@studer.raimann.ch>
 */
final class ilCtrl2 implements ilCtrlInterface
{
    /**
     * @var ilPluginAdmin
     */
    private ilPluginAdmin $plugin_service;

    /**
     * @var HttpServices
     */
    private HttpServices $http_service;

    /**
     * @var RequestWrapper
     */
    private RequestWrapper $get_request;

    /**
     * @var RequestWrapper
     */
    private RequestWrapper $post_request;

    /**
     * @var ilDBInterface
     */
    private ilDBInterface $database;

    /**
     * @var Refinery
     */
    private Refinery $refinery;

    /**
     * Holds the currently read control structure.
     *
     * @var ilCtrlStructureInterface
     */
    private ilCtrlStructureInterface $structure;

    /**
     * Holds the current link target information.
     *
     * @var ilCtrlTarget
     */
    private ilCtrlTargetInterface $target;

    /**
     * Holds the ilCtrl token of the current user session.
     *
     * @var ilCtrlTokenInterface
     */
    private ilCtrlTokenInterface $token;

    /**
     * Holds the current context information. e.g. obj_id, obj_type etc.
     *
     * @var ilCtrlContext
     */
    private ilCtrlContext $context;

    /**
     * @TODO: this seems to be used as workaround when getLinkTarget()
     *        should be used instead. Deprecate?
     *
     * @var array<string, string>
     */
    private array $return_classes = [];

    /**
     * Holds the stacktrace of each call made with this ilCtrl instance.
     *
     * @var array<int, string>
     */
    private array $stacktrace = [];

    /**
     * ilCtrl constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->http_service = $DIC->http();
        $this->get_request  = $DIC->http()->wrapper()->query();
        $this->post_request = $DIC->http()->wrapper()->post();
        $this->database     = $DIC->database();

        // $DIC->refinery() is not initialized at this point.
        $this->refinery = new Refinery(
            new DataFactory(),
            $DIC->language()
        );

        $this->token        = new ilCtrlToken($this->database, $DIC->user());
        $this->structure    = new ilCtrlStructure($this->get_request, $this->refinery);
        $this->target       = new ilCtrlTarget($this->token, $this->structure, "");
        $this->context      = new ilCtrlContext();
    }

    /**
     * @inheritDoc
     */
    public function initBaseClass(string $a_base_class) : void
    {
        // reset the current target.
        $this->target = new ilCtrlTarget(
            $this->token,
            $this->structure,
            $a_base_class
        );

        // reset other currently stored information.
        $this->return_classes = [];
        $this->stacktrace = [];
    }

    /**
     * @inheritDoc
     */
    public function callBaseClass() : void
    {
        $base_class = $this->getBaseClass();
        if (null === $base_class) {
            throw new ilCtrlException("Cannot determine current baseclass, ilCtrl::initBaseClass() has to be called fisrt.");
        }

        $class_name = $this->structure->getObjectNameByClass($base_class);
        $this->forwardCommand(new $class_name());
    }

    /**
     * @inheritDoc
     */
    public function forwardCommand(object $a_gui_object) : mixed
    {
        if (!method_exists($a_gui_object, 'executeCommand')) {
            throw new ilCtrlException(get_class($a_gui_object) . " doesn't implement executeCommand().");
        }

        $class_name = $this->getClassNameOfObject($a_gui_object);
        $this->target->appendCmdClass($class_name);

        $this->populateCall(
            $class_name,
            $this->getCmd() ?? '',
            self::CMD_MODE_PROCESS
        );

        return $a_gui_object->executeCommand();
    }

    /**
     * @inheritDoc
     */
    public function getHTML(object $a_gui_object, array $a_parameters = null) : string
    {
        if (!method_exists($a_gui_object, 'getHTML')) {
            throw new ilCtrlException( get_class($a_gui_object) . " doesn't implement getHTML().");
        }

        $class_name = $this->getClassNameOfObject($a_gui_object);
        $this->target->appendCmdClass($class_name);

        $this->populateCall(
            $class_name,
            $this->getCmd(),
            self::CMD_MODE_HTML
        );

        return (null !== $a_parameters) ?
            $a_gui_object->getHTML($a_parameters) :
            $a_gui_object->getHTML()
        ;
    }

    /**
     * @inheritDoc
     */
    public function getCmd(string $fallback_command = null, ilCtrlCommandHandler $handler = null) : string
    {
        $get_command  = $this->getQueryCmd();
        $post_command = $this->getPostCmd();

        // if the command 'post' is retrieved via $_GET, the post
        // command must be used.
        $command = (ilCtrlTarget::CMD_POST === $get_command) ?
            $post_command : $get_command
        ;

        // apply temporarily added handlers in case some exceptional
        // command manipulation needs to happen.
        if (null !== $handler) {
            $command = $handler->handle(
                $get_command,
                $this->getPostCmdArray()
            );
        }

        // in case of GET command 'post' and no found command, the
        // GET fallback command is returned if possible.
        if (ilCtrlTarget::CMD_POST === $get_command && null === $command) {
            if ($this->get_request->has(ilCtrlTargetInterface::PARAM_CMD_FALLBACK)) {
                return $this->get_request->retrieve(
                    ilCtrlTargetInterface::PARAM_CMD_FALLBACK,
                    $this->refinery->to()->string()
                );
            }

            $command = $fallback_command;
        }

        $cmd_class = $this->structure->getObjectNameByClass($this->target->getCurrentCmdClass());
        if (null !== $command && is_a($cmd_class, ilCtrlSecurityInterface::class, true)) {
            $cmd_class = new $cmd_class();
            if (!in_array($command, $cmd_class->getSafeCommands(), true)) {
                $token = ($this->get_request->has(ilCtrlTargetInterface::PARAM_CSRF_TOKEN)) ?
                    $this->get_request->retrieve(

                    ): ;
            }
        }

        return $command;
    }

    /**
     * @inheritDoc
     */
    public function setCmd(string $a_cmd) : void
    {
        $this->target->setCmd($a_cmd);
    }

    /**
     * @inheritDoc
     */
    public function getCmdClass() : ?string
    {
        if ($this->get_request->has(self::PARAM_CMD_CLASS)) {
            return $this->get_request->retrieve(
                self::PARAM_CMD_CLASS,
                $this->refinery->to()->string()
            );
        }

        return $this->target->getCurrentCmdClass() ?? '';
    }

    /**
     * @inheritDoc
     */
    public function setCmdClass(object|string $a_cmd_class) : void
    {
        $a_cmd_class = (is_object($a_cmd_class)) ?
            strtolower(get_class($a_cmd_class)) :
            strtolower($a_cmd_class)
        ;

        $this->target->appendCmdClass($a_cmd_class);
    }

    /**
     * @inheritDoc
     */
    public function getNextClass(object|string $a_gui_class = null) : string|false
    {
        if (null !== $this->target->getNestedTarget()) {
            $cid_trace = $this->target->getNestedTarget()->getCidTrace();
        } else {
            $cid_trace = $this->target->getCidTrace();
        }

        if (null !== $a_gui_class) {
            // abort if an invalid argument was supplied.
            if (!is_object($a_gui_class) && !is_string($a_gui_class)) {
                return false;
            }

            $a_gui_class = (is_object($a_gui_class)) ?
                strtolower(get_class($a_gui_class)) :
                strtolower($a_gui_class)
            ;

            $gui_cid = $this->getStructureInfoByName($a_gui_class);
            if ($this->target->getCurrentCid() === $gui_cid) {

            }
        }

        if ($this->get_request->has(self::PARAM_CMD_TRACE)) {
            $cid_trace = $this->get_request->retrieve(
                self::PARAM_CMD_TRACE,
                $this->refinery->to()->string()
            );

            $current_cid = $this->target->getCurrentCidFrom($cid_trace);
            if (null !== $current_cid) {
                $class_info = $this->getStructureInfoByCid($cid_trace);
                return $this->getClassName($class_info);
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function saveParameter(object $a_gui_obj, array|string $a_parameter) : void
    {
        $this->saveParameterByClass(get_class($a_gui_obj), $a_parameter);
    }

    /**
     * @inheritDoc
     */
    public function saveParameterByClass(string $a_class, array|string $a_parameter) : void
    {
        if (!is_array($a_parameter)) {
            $a_parameter = [$a_parameter];
        }

        foreach ($a_parameter as $parameter_name) {
            if (!preg_match(self::PARAM_NAME_REGEX, $parameter_name)) {
                throw new ilCtrlException("Cannot save parameter '$parameter_name', as it contains invalid characters.");
            }

            $this->saved_parameters[strtolower($a_class)][] = $parameter_name;
        }
    }

    /**
     * @inheritDoc
     */
    public function setParameter(object $a_gui_obj, string $a_parameter, mixed $a_value) : void
    {
        $this->setParameterByClass(get_class($a_gui_obj), $a_parameter, $a_value);
    }

    /**
     * @inheritDoc
     */
    public function setParameterByClass(string $a_class, string $a_parameter, mixed $a_value) : void
    {
        if (!preg_match(self::PARAM_NAME_REGEX, $a_parameter)) {
            throw new ilCtrlException("Cannot save parameter '$a_parameter', as it contains invalid characters.");
        }

        $this->parameters[strtolower($a_class)][$a_parameter] = $a_value;
    }

    /**
     * @inheritDoc
     */
    public function getParameterArray(object $a_gui_obj) : array
    {
        return $this->getParameterArrayByClass(get_class($a_gui_obj));
    }

    /**
     * @inheritDoc
     */
    public function getParameterArrayByClass(string $a_class, string $a_cmd = null) : array
    {
        $class_name = strtolower($a_class);
        $class_info = $this->getStructureInfoByName($class_name);
        $class_cid  = $this->getClassCid($class_info);

        if ($class_cid !== $this->target->getCurrentCid()) {
            $this->determineCidTrace($class_name);
        }

        $parameters = [];
        foreach ($this->target->getCidPieces() as $cid) {
            $current_info  = $this->getStructureInfoByCid($cid);
            $current_class = $this->getClassName($current_info);

            // if the current class has saved parameters, they need to
            // be fetched from the current request.
            if (isset($this->saved_parameters[$current_class])) {
                foreach ($this->saved_parameters[$current_class] as $key => $value) {
                    if ($this->get_request->has($key)) {
                        $parameters[$key] = $this->get_request->retrieve(
                            $key,
                            $this->refinery->to()->string()
                        );
                    } else {
                        $parameters[$key] = null;
                    }
                }
            }

            if (isset($this->parameters[$current_class])) {
                foreach ($this->parameters[$current_class] as $key => $value) {
                    $parameters[$key] = $value;
                }
            }
        }

        return $parameters;
    }

    /**
     * @inheritDoc
     */
    public function clearParameters(object $a_gui_obj) : void
    {
        $this->clearParametersByClass(get_class($a_gui_obj));
    }

    /**
     * @inheritDoc
     */
    public function clearParametersByClass(string $a_class) : void
    {
        $class_name = strtolower($a_class);

        if (isset($this->saved_parameters[$class_name])) {
            unset($this->saved_parameters[$class_name]);
        }

        if (isset($this->parameters[$class_name])) {
            unset($this->parameters[$class_name]);
        }
    }

    /**
     * @inheritDoc
     */
    public function clearParameterByClass(string $a_class, string $a_parameter) : void
    {
        $class_name = strtolower($a_class);

        if (isset($this->saved_parameters[$class_name][$a_parameter])) {
            unset($this->saved_parameters[$class_name][$a_parameter]);
        }

        if (isset($this->parameters[$class_name][$a_parameter])) {
            unset($this->parameters[$class_name][$a_parameter]);
        }
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
            get_class($a_gui_obj),
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
        array|string $a_class,
        string $a_cmd = "",
        string $a_anchor = "",
        bool $is_async = false,
        bool $has_xml_style = false
    ) : string {
        // force xml style to be disabled for async requests
        if ($is_async) {
            $has_xml_style = false;
        }

        if (is_array($a_class)) {
            $this->target->setCidTrace(null);
            foreach ($a_class as $class_name) {
                $class_name = strtolower($class_name);
                $class_info = $this->getStructureInfoByName($class_name);
                $class_cid  = $this->getClassCid($class_info);

                $this->target->appendCid($class_cid);
                if (!$this->validateCidTrace($this->target->getCidTrace())) {
                    throw new ilCtrlException("CID Trace '{$this->target->getCidTrace()}' is not valid. Fix any missing ilCtrl-call statements.");
                }
            }
        } else {
            $this->determineCidTrace(strtolower($a_class));
        }

        $target_url = $this->getTargetScript();
        foreach ($this->target->getCidPieces() as $cid) {
            $parameters = $this->getParameterArrayByClass(
                $this->getClassName(
                    $this->getStructureInfoByCid($cid)
                )
            );

            if (!empty($parameters)) {
                foreach ($parameters as $key => $value) {
                    $this->appendUrlParameterString($target_url, $key, $value, $has_xml_style);
                }
            }
        }

        $this->appendRequestTokenParameterString($target_url, $has_xml_style);

        // append baseclass, cidtrace, cmdclass and cmd.
        if () {

        }

        if ($is_async) {
            $this->appendUrlParameterString(
                $target_url,
                self::PARAM_CMD_MODE,
                self::CMD_MODE_ASYNC
            );
        }

        if ('' !== $a_anchor) {
            $target_url .= "#$a_anchor";
        }

        return $target_url;
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
            get_class($a_gui_obj),
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
        array|string $a_class,
        string $a_fallback_cmd = "",
        string $a_anchor = "",
        bool $is_async = false,
        bool $has_xml_style = false
    ) : string {
        $form_action = $this->getLinkTargetByClass(
            $a_class,
            self::CMD_POST,
            '',
            $is_async,
            $has_xml_style
        );

        if ('' !== $a_fallback_cmd) {
            $this->appendUrlParameterString(
                $form_action,
                self::PARAM_CMD_FALLBACK,
                $a_fallback_cmd,
                $has_xml_style
            );
        }

        if ('' !== $a_anchor) {
            $form_action .= "#$a_anchor";
        }

        return $form_action;
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
        $this->redirectToURL(
            $this->getLinkTargetByClass(
                get_class($a_gui_obj),
                $a_cmd,
                $a_anchor,
                $is_async
            )
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
        if (!is_int(strpos($target_url, '://'))) {
            if (defined('ILIAS_HTTP_PATH') && 0 !== strpos($target_url, '/')) {
                if (is_int(strpos($_SERVER['PHP_SELF'], '/setup/'))) {
                    $target_url = 'setup/' . $target_url;
                }

                $target_url = ILIAS_HTTP_PATH . '/' . $target_url;
            }
        }

        if (null !== $this->plugin_service) {
            $plugin_names = ilPluginAdmin::getActivePluginsForSlot(
                IL_COMP_SERVICE,
                'UIComponent',
                'uihk'
            );

            if (!empty($plugin_names)) {
                foreach ($plugin_names as $plugin) {
                    $plugin = ilPluginAdmin::getPluginObject(
                        IL_COMP_SERVICE,
                        'UIComponent',
                        'uihk',
                        $plugin
                    );

                    /**
                     * @var $plugin ilUserInterfaceHookPlugin
                     *
                     * @TODO: THIS IS LEGACY CODE! Methods are deprecated an should not
                     *        be used anymore. There is no other implementation yet,
                     *        therefore it stays for now.
                     */
                    $gui_object = $plugin->getUIClassInstance();
                    $resp = $gui_object->getHTML("Services/Utilities", "redirect", array( "html" => $target_url ));
                    if ($resp["mode"] != ilUIHookPluginGUI::KEEP) {
                        $target_url = $gui_object->modifyHTML($target_url, $resp);
                    }
                }
            }
        }

        // Manually trigger to write and close the session. This has the advantage that if an exception is thrown
        // during the writing of the session (ILIAS writes the session into the database by default) we get an exception
        // if the session_write_close() is triggered by exit() then the exception will be dismissed but the session
        // is never written, which is a nightmare to develop with.
        session_write_close();

        if ('application/json' === $this->http_service->request()->getHeaderLine('Accept')) {
            $stream = \ILIAS\Filesystem\Stream\Streams::ofString(json_encode([
                'success' => true,
                'message' => 'Called redirect after async fileupload request',
                "redirect_url" => $target_url,
            ]));

            $this->http_service->saveResponse(
                $this->http_service->response()->withBody($stream)
            );
        } else {
            $this->http_service->saveResponse(
                $this->http_service->response()->withHeader(
                    'Location',
                    $target_url
                )
            );
        }

        $this->http_service->sendResponse();
        exit;
    }

    /**
     * @inheritDoc
     */
    public function setContext(ilCtrlContext $context) : void
    {
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function getContext() : ilCtrlContext
    {
        return $this->context;
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
    public function getContextSubObjId() : ?int
    {
        return $this->context->getSubObjId();
    }

    /**
     * @inheritDoc
     */
    public function getContextSubObjType() : ?string
    {
        return $this->context->getSubObjType();
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
        $class_info = $this->getStructureInfoByName($a_class);
        return $this->getClassPath($class_info);
    }

    /**
     * @inheritDoc
     */
    public function getClassForClasspath(string $a_class_path) : string
    {
        $path  = pathinfo($a_class_path);
        $file  = $path["basename"];
        $class = substr($file, 6, strlen($file) - 10);

        return $class;
    }

    /**
     * @inheritDoc
     */
    public function setTargetScript(string $a_target_script) : void
    {
        $this->target_script = $a_target_script;
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
    public function verifyToken(RequestWrapper $request) : bool
    {
        global $DIC;

        if (!$request->has(self::PARAM_CSRF_TOKEN)) {
            return false;
        }

        $stored_token  = $this->getRequestToken();
        $current_token = $request->retrieve(
            self::PARAM_CSRF_TOKEN,
            $this->refinery->to()->string()
        );

        if ($current_token === $stored_token) {
            $datetime = new ilDateTime(time(), IL_CAL_UNIX);
            $datetime->increment(IL_CAL_DAY, -1);
            $datetime->increment(IL_CAL_HOUR, -12);

            // according to bug #13551 the current token must not be removed
            // immediately from the database. Therefore only old(er) ones are
            // removed right now.
            $this->database->manipulateF(
                "DELETE FROM il_request_token WHERE user_id = %s AND session_id = %s AND stamp < %s;",
                ['integer', 'text', 'timestamp'],
                [$DIC->user()->getId(), session_id(), $datetime->get(IL_CAL_TIMESTAMP)]
            );

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function appendRequestTokenParameterString(string $a_url, bool $xml_style = false) : string
    {
        $this->appendUrlParameterString(
            $a_url,
            self::PARAM_CSRF_TOKEN,
            $this->getRequestToken(),
            $xml_style
        );

        return $a_url;
    }

    /**
     * @inheritDoc
     */
    public function getRequestToken() : string
    {
        global $DIC;
        static $token;

        if (isset($token)) {
            return $token;
        }

        $user_id = $DIC->user()->getId();
        if (0 < $user_id && ANONYMOUS_USER_ID !== $user_id) {
            $token_result = $this->database->fetchAssoc(
                $this->database->queryF(
                    "SELECT token FROM il_request_token WHERE user_id = %s AND session_id = %s;",
                    ['integer', 'text'],
                    [$user_id, session_id()]
                )
            );

            if (isset($token_result['token'])) {
                $token = $token_result['token'];
                return $token;
            }

            $random = new ilRandom();
            $token  = md5(uniqid($random->int(), true));

            $this->database->manipulateF(
                "INSERT INTO il_request_token (user_id, token, stamp, session_id) VALUES (%s, %s, %s, %s);",
                [
                    'integer',
                    'text',
                    'timestamp',
                    'text',
                ],
                [
                    $user_id,
                    $token,
                    $this->database->now(),
                    session_id(),
                ]
            );

            $this->maybeDeleteOldTokens($random);
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function isAsynch() : bool
    {
        if ($this->get_request->has(self::PARAM_CMD_MODE)) {
            $mode = $this->get_request->retrieve(
                self::PARAM_CMD_MODE,
                $this->refinery->to()->string()
            );

            return (self::CMD_MODE_ASYNC === $mode);
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function setReturn(object $a_gui_obj, string $a_cmd) : void
    {
        $this->setReturnByClass(get_class($a_gui_obj), $a_cmd);
    }

    /**
     * @inheritDoc
     */
    public function setReturnByClass(string $a_class, string $a_cmd) : void
    {
        $class_name = strtolower($a_class);

        $script = $this->getUrlParameterStringByClass($class_name, $a_cmd);

        $this->return_classes[$class_name] = $script;
    }

    /**
     * @inheritDoc
     */
    public function returnToParent(object $a_gui_obj, string $a_anchor = null) : void
    {
        $class_name = strtolower(get_class($a_gui_obj));
        $target_url = $this->getReturnClass($class_name);

        if (!$target_url) {
            throw new ilException("Cannot return from " . get_class($a_gui_obj) . ". The parent class was not found.");
        }

        $this->appendUrlParameterString(
            $target_url,
            self::PARAM_REDIRECT,
            $class_name
        );

        if ($this->get_request->has(self::PARAM_CMD_MODE)) {
            $cmd_mode = $this->get_request->retrieve(
                self::PARAM_CMD_MODE,
                $this->refinery->to()->string()
            );

            $this->appendUrlParameterString(
                $target_url,
                self::PARAM_CMD_MODE,
                $cmd_mode
            );
        }

        $this->redirectToURL($target_url);
    }

    /**
     * @inheritDoc
     */
    public function getParentReturn($a_gui_obj)
    {
        return $this->getReturnClass($a_gui_obj);
    }

    /**
     * @inheritDoc
     */
    public function getParentReturnByClass($a_class)
    {
        return $this->getReturnClass($a_class);
    }

    /**
     * @inheritDoc
     */
    public function getReturnClass($a_class)
    {
        if (is_object($a_class)) {
            $class_name = strtolower(get_class($a_class));
        } else {
            $class_name = strtolower($a_class);
        }

        $this->determineCidTrace($class_name);
        foreach ($this->target->getCidPieces(SORT_DESC) as $cid) {
            $class_info = $this->getStructureInfoByCid($cid);
            $class_name_of_iteration = $this->getClassName($class_info);
            if (isset($this->return_classes[$class_name_of_iteration])) {
                return $this->return_classes[$class_name_of_iteration];
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getRedirectSource() : string
    {
        if ($this->get_request->has(self::PARAM_REDIRECT)) {
            return $this->get_request->retrieve(
                self::PARAM_REDIRECT,
                $this->refinery->to()->string()
            );
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function insertCtrlCalls($a_parent, $a_child, string $a_comp_prefix) : void
    {
        throw new ilCtrlException("Cannot execute " . __METHOD__ . ". Information is no longer stored in the database.");
    }

    /**
     * @inheritDoc
     */
    public function checkCurrentPathForClass(string $gui_class) : bool
    {
        $gui_class_name = strtolower($gui_class);
        foreach ($this->getCidsFromTrace($this->cid_trace) as $cid) {
            $class_info = $this->getStructureInfoByCid($cid);
            if ($gui_class_name === $this->getClassName($class_info)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentClassPath() : array
    {
        if (null === $this->cid_trace && $this->get_request->has(self::PARAM_BASE_CLASS)) {
            return [
                $this->get_request->retrieve(
                    self::PARAM_BASE_CLASS,
                    $this->refinery->to()->string()
                )
            ];
        }

        $classes = [];
        foreach ((explode(self::CID_TRACE_SEPARATOR, $this->cid_trace)) as $cid) {
            $class_info = $this->getStructureInfoByCid($cid);
            $classes[]  = $this->getClassName($class_info);
        }

        return $classes;
    }

    //
    // BEGIN PRIVATE METHODS
    //

    /**
     * Returns the classname of the current request's baseclass.
     *
     * @return string|null
     */
    private function getBaseClass() : ?string
    {
        if ($this->get_request->has(self::PARAM_BASE_CLASS)) {
            $base_class = strtolower(
                $this->get_request->retrieve(
                    self::PARAM_BASE_CLASS,
                    $this->refinery->to()->string()
                )
            );

            $this->target->setBaseClass($base_class);
        }

        return $this->target->getBaseClass();
    }

    /**
     * Populates a call by making a stacktrace entry for the given information.
     *
     * @param string      $class_name
     * @param string      $cmd
     * @param string|null $mode
     */
    private function populateCall(string $class_name, string $cmd, string $mode = null) : void
    {
        $this->stacktrace[] = [
            'class' => $class_name,
            'cmd'   => $cmd,
            'mode'  => $mode,
        ];
    }

    /**
     * Helper function to retrieve current GET command.
     *
     * @return string|null
     */
    private function getQueryCmd() : ?string
    {
        if ($this->get_request->has(self::PARAM_CMD)) {
            $get_command = $this->get_request->retrieve(
                self::PARAM_CMD,
                $this->refinery->to()->string()
            );
        }

        return $get_command ?? null;
    }

    /**
     * Helper function to retrieve current POST command.
     *
     * @return string|null
     */
    private function getPostCmd() : ?string
    {
        if ($this->post_request->has(self::PARAM_CMD)) {
            $post_command = $this->post_request->retrieve(
                self::PARAM_CMD,
                $this->refinery->custom()->transformation(
                    static function ($command) {
                        if (is_array($command)) {
                            return array_key_first($command);
                        }

                        return $command;
                    }
                )
            );
        }

        return $post_command ?? null;
    }

    /**
     * Helper function to retrieve current POST commands.
     *
     * @return array|null
     */
    private function getPostCmdArray() : ?array
    {
        if ($this->post_request->has(self::PARAM_CMD)) {
            $post_commands = $this->post_request->retrieve(
                self::PARAM_CMD,
                $this->refinery->custom()->transformation(
                    static function ($command) {
                        if (is_array($command)) {
                            return (array) $command;
                        }

                        return null;
                    }
                )
            );
        }

        return $post_commands ?? null;
    }

    /**
     * Removes old or unnecessary tokens from the database if the answer to
     * life, the universe and everything is generated.
     *
     * @param ilRandom $random
     */
    private function maybeDeleteOldTokens(ilRandom $random) : void
    {
        if (42 === $random->int(1, 200)) {
            $datetime = new ilDateTime(time(), IL_CAL_UNIX);
            $datetime->increment(IL_CAL_DAY, -1);
            $datetime->increment(IL_CAL_HOUR, -12);

            $this->database->manipulateF(
                "DELETE FROM il_request_token WHERE stamp < %s;",
                ['timestamp'],
                [$datetime->get(IL_CAL_TIMESTAMP)]
            );
        }
    }

    /**
     * Returns all saved or set parameters as a query string.
     *
     * @param array|string $a_class
     * @param string|null  $cmd
     * @param bool         $xml_style
     * @return string
     * @throws ilCtrlException if a provided class was not found.
     */
    private function getUrlParameterStringByClass(array|string $a_class, string $cmd = null, bool $xml_style = false) : string
    {
        $query_string = $this->getTargetScript();

        if (is_array($a_class)) {
            $parameters = [];
            foreach ($a_class as $class_name) {
                array_merge_recursive($parameters, $this->getParameterArrayByClass($class_name, $cmd));
            }
        } else {
            $parameters = $this->getParameterArrayByClass($a_class, $cmd);
        }

        foreach ($parameters as $key => $value) {
            if ('' !== (string) $value) {
                $this->appendUrlParameterString(
                    $query_string,
                    $key,
                    $value,
                    $xml_style
                );
            }
        }

        return $query_string;
    }

    /**
     * Appends a parameter name and value to an existing URL string.
     *
     * This method was imported from @see ilUtil::appendUrlParameterString().
     *
     * @param string $url
     * @param string $parameter_name
     * @param mixed  $parameter_value
     * @param bool   $xml_style
     */
    private function appendUrlParameterString(string &$url, string $parameter_name, mixed $parameter_value, bool $xml_style = false) : void
    {
        $amp = ($xml_style) ? "&amp;" : "&";

        $url = (is_int(strpos($url, "?"))) ?
            $url . $amp . $parameter_name . "=" . $parameter_value :
            $url . "?" . $parameter_name . "=" . $parameter_value
        ;
    }

    /**
     * Returns the lower-cased class name of the given object.
     *
     * @param object $a_object
     * @return string
     */
    private function getClassNameOfObject(object $a_object) : string
    {
        return strtolower(get_class($a_object));
    }
}