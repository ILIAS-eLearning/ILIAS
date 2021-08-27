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
final class ilCtrl implements ilCtrlInterface
{
    /**
     * @var string command name which must be provided in $_GET when
     *             a POST request should be processed.
     */
    public const CMD_POST = 'post';

    /**
     * @var string regex for the validation of $_GET parameter names.
     *             (allows A-Z, a-z, 0-9, '_' and '-'.)
     */
    private const PARAM_NAME_REGEX = '/^[A-Za-z0-9_-]*$/';

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
     * Holds the current context information. e.g. obj_id, obj_type etc.
     *
     * @var ilCtrlContext
     */
    private ilCtrlContext $context;

    /**
     * Holds the current link target information.
     *
     * @var ilCtrlTarget
     */
    private ilCtrlTarget $target;

    /**
     * Holds the cached CID's mapped to their according structure information.
     *
     * @var array<string, string[]>
     */
    private static array $mapped_structure = [];

    /**
     * Holds the read control structure from the php artifact.
     *
     * @var array<string, string[]>
     */
    private array $structure;

    /**
     * Holds the saved parameters of each class.
     *
     * @see ilCtrl::saveParameterByClass(), ilCtrl::saveParameter()
     *
     * @var array<string, array>
     */
    private array $saved_parameters = [];

    /**
     * Holds the set parameters of each class.
     *
     * @see ilCtrl::setParameterByClass(), ilCtrl::setParameter()
     *
     * @var array<string, array>
     */
    private array $parameters = [];

    /**
     * Holds the base-script for link targets, usually isn't changed.
     *
     * @var string
     */
    private string $target_script = 'ilias.php';

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
     *
     * @throws ilCtrlException if the structure cannot be required.
     */
    public function __construct()
    {
        global $DIC;

        $this->target       = new ilCtrlTarget();
        $this->context      = new ilCtrlContext();
        $this->http_service = $DIC->http();
        $this->get_request  = $DIC->http()->wrapper()->query();
        $this->post_request = $DIC->http()->wrapper()->post();
        $this->database     = $DIC->database();

        // $DIC->refinery() is not initialized at this point.
        $this->refinery = new Refinery(
            new DataFactory(),
            $DIC->language()
        );

        // cannot be accessed via $DIC->aMethod().
        if (isset($DIC['ilPluginAdmin'])) {
            $this->plugin_service = $DIC['ilPluginAdmin'];
        }

        $this->initStructureOrAbort();
    }

    /**
     * @inheritDoc
     */
    public function callBaseClass() : void
    {
        $base_class = $this->getBaseClass();
        $class_info = $this->getStructureInfoByName($base_class);
        $class_name = $this->getClassName($class_info);

        $this->determineCidTrace($class_name);
        $this->forwardCommand(new $class_name());
    }

    /**
     * @inheritDoc
     */
    public function initBaseClass(string $a_base_class) : void
    {
        // reset the current target.
        $this->target = new ilCtrlTarget();
        $this->target->setBaseClass($a_base_class);

        // reset other currently stored information.
        $this->saved_parameters = [];
        $this->return_classes = [];
        $this->parameters = [];
        $this->stacktrace = [];
    }

    /**
     * @inheritDoc
     */
    public function forwardCommand(object $a_gui_object) : mixed
    {
        $class_name = strtolower(get_class($a_gui_object));
        $this->determineCidTrace($class_name);

        if (!method_exists($a_gui_object, 'executeCommand')) {
            throw new ilCtrlException(get_class($a_gui_object) . " doesn't implement executeCommand().");
        }

        $this->populateCall(
            $class_name,
            $this->getCmd(),
            self::CMD_MODE_PROCESS
        );

        return $a_gui_object->executeCommand();
    }

    /**
     * @inheritDoc
     */
    public function getHTML(object $a_gui_object, array $a_parameters = null) : string
    {
        $class_name = strtolower(get_class($a_gui_object));
        $this->determineCidTrace($class_name);

        if (!method_exists($a_gui_object, 'getHTML')) {
            throw new ilCtrlException( get_class($a_gui_object) . " doesn't implement getHTML().");
        }

        $this->populateCall(
            $class_name,
            $this->getCmd(),
            self::CMD_MODE_HTML
        );

        if (null !== $a_parameters) {
            return $a_gui_object->getHTML($a_parameters);
        }

        return $a_gui_object->getHTML();
    }

    /**
     * @inheritDoc
     */
    public function getCmd(
        string $fallback_command = '',
        array $safe_commands = [],
        ilCtrlCommandHandler $handler = null
    ) : string {
        $get_command  = $this->getQueryCmd();
        $post_command = $this->getPostCmd();

        // all commands which are not $safe_commands MUST pass the
        // CSRF token validation in order to be returned.
        // @TODO: implement token validation

        // if the post command is retrieved via $_GET, the post
        // command must be used.
        $command = (self::CMD_POST === $get_command) ?
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
        if (self::CMD_POST === $get_command && null === $command) {
            if ($this->get_request->has(self::PARAM_CMD_FALLBACK)) {
                return $this->get_request->retrieve(
                    self::PARAM_CMD_FALLBACK,
                    $this->refinery->to()->string()
                );
            }
        }

        // if no command was found, check if a custom command has been
        // set with setCmd() before fallback is returned.
        if (null === $command && null !== $this->target->getCmd()) {
            return $this->target->getCmd();
        }

        return $fallback_command;
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
    public function getCmdClass() : string
    {
        if ($this->get_request->has(self::PARAM_CMD_CLASS)) {
            return $this->get_request->retrieve(
                self::PARAM_CMD_CLASS,
                $this->refinery->to()->string()
            );
        }

        return $this->target->getCmdClass();
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

        $this->target->setCmdClass($a_cmd_class);
    }

    /**
     * @inheritDoc
     */
    public function getNextClass(object|string $a_gui_class = null) : string|false
    {
        if (null !== $a_gui_class) {
            // abort if an invalid argument was supplied.
            if (!is_object($a_gui_class) && !is_string($a_gui_class)) {
                return false;
            }

            $a_gui_class = (is_object($a_gui_class)) ?
                strtolower(get_class($a_gui_class)) :
                strtolower($a_gui_class)
            ;

            $cid_trace = $this->determineCidTrace($a_gui_class, true);
            if (null !== $cid_trace) {
                $current_cid  = $this->target->getCurrentCidFrom($cid_trace);
                $current_info = $this->getStructureInfoByCid($current_cid);

                return $this->getClassName($current_info);
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
    public function getParameterArray(object $a_gui_obj, $a_cmd = null) : array
    {
        return $this->getParameterArrayByClass(get_class($a_gui_obj), $a_cmd);
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

        $target_url = $this->getTargetScript();
        $target_url = $this->getUrlParameters($a_class, $target_url, $a_cmd, $has_xml_style);

        $this->appendRequestTokenParameterString($target_url, $has_xml_style);

        if ($is_async) {
            $this->appendUrlParameterString(
                $target_url,
                self::PARAM_CMD_MODE,
                self::CMD_MODE_ASYNC
            );
        }

        if ('' !== $a_anchor) {
            $target_url .= "#" . $a_anchor;
        }

        return $target_url;
    }

    /**
     * @inheritDoc
     */
    public function getFormAction(
        object $a_gui_object,
        string $a_fallback_cmd = "",
        string $a_anchor = "",
        bool $a_asynch = false,
        bool $xml_style = false
    ) : string {
        return $this->getFormActionByClass(
            get_class($a_gui_object),
            $a_fallback_cmd,
            $a_anchor,
            $a_asynch,
            $xml_style
        );
    }

    /**
     * @inheritDoc
     */
    public function getFormActionByClass(
        $a_class,
        string $a_fallback_cmd = "",
        string $a_anchor = "",
        bool $a_asynch = false,
        bool $xml_style = false
    ) : string {
        $form_action = $this->getLinkTargetByClass(
            $a_class,
            self::CMD_POST,
            '',
            $a_asynch,
            $xml_style
        );

        if ('' !== $a_fallback_cmd) {
            $this->appendUrlParameterString(
                $form_action,
                self::PARAM_CMD_FALLBACK,
                $a_fallback_cmd,
                $xml_style
            );
        }

        if ('' !== $a_anchor) {
            $form_action .= '#' . $a_anchor;
        }

        return $form_action;
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
        if (0 <= $user_id && ANONYMOUS_USER_ID !== $user_id) {
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
    public function redirect(
        object $a_gui_obj,
        string $a_cmd = "",
        string $a_anchor = "",
        bool $a_asynch = false
    ) : void {
        $this->redirectToURL(
            $this->getLinkTargetByClass(
                get_class($a_gui_obj),
                $a_cmd,
                $a_anchor,
                $a_asynch
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function redirectToURL(string $a_url) : void
    {
        if (!is_int(strpos($a_url, '://'))) {
            if (defined('ILIAS_HTTP_PATH') && 0 !== strpos($a_url, '/')) {
                if (is_int(strpos($_SERVER['PHP_SELF'], '/setup/'))) {
                    $a_url = 'setup/' . $a_url;
                }

                $a_url = ILIAS_HTTP_PATH . '/' . $a_url;
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
                    $resp = $gui_object->getHTML("Services/Utilities", "redirect", array( "html" => $a_url ));
                    if ($resp["mode"] != ilUIHookPluginGUI::KEEP) {
                        $a_url = $gui_object->modifyHTML($a_url, $resp);
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
                "redirect_url" => $a_url,
            ]));

            $this->http_service->saveResponse(
                $this->http_service->response()->withBody($stream)
            );
        } else {
            $this->http_service->saveResponse(
                $this->http_service->response()->withHeader(
                    'Location',
                    $a_url
                )
            );
        }

        $this->http_service->sendResponse();
        exit;
    }

    /**
     * @inheritDoc
     */
    public function redirectByClass(
        $a_class,
        string $a_cmd = "",
        string $a_anchor = "",
        bool $a_asynch = false
    ) : void {
        $this->redirectToURL(
            $this->getLinkTargetByClass(
                $a_class,
                $a_cmd,
                $a_anchor,
                $a_asynch
            )
        );
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

        $script = $this->getTargetScript();
        $script = $this->getUrlParameters($class_name, $script, $a_cmd);

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

        $trace = $this->determineCidTrace($class_name, $this->cid_trace);
        $cids  = explode(self::CID_TRACE_SEPARATOR, $trace);

        for ($i = count($cids); 0 <= $i; $i--) {
            $class_info = $this->getStructureInfoByCid($cids[$i]);
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
     * Get URL parameters for a class and append them to a string.
     *
     * @param string|string[] $a_classes
     * @param string          $a_str
     * @param string|null     $a_cmd
     * @param bool            $xml_style
     * @return string
     */
    private function getUrlParameters($a_classes, string $a_str, string $a_cmd = null, bool $xml_style = false) : string
    {
        if (is_array($a_classes)) {
            $parameters = [];
            foreach ($a_classes as $class) {
                array_merge($parameters, $this->getParameterArrayByClass($class));
            }
        } else {
            $parameters = $this->getParameterArrayByClass($a_classes, $a_cmd);
        }

        foreach ($parameters as $param_name => $value) {
            // if the given value is appendable as string, do it.
            if ('' !== (string) $value) {
                $this->appendUrlParameterString(
                    $a_str,
                    $param_name,
                    $value,
                    $xml_style
                );
            }
        }

        return $a_str;
    }

    /**
     * Returns all parameters that have been saved or set using multiple
     * or one classname.
     *
     * @param string      $a_class
     * @param string|null $a_cmd
     * @return array
     * @throws ilCtrlException if the cid trace cannot be found.
     */
    private function getParameterArrayByClass(string $a_class, string $a_cmd = null) : array
    {
        if (empty($a_class)) {
            return [];
        }

        if (null === $this->target->getCidTrace()) {
            $this->determineCidTrace($a_class);
        }

        $parameters = [];
        // retrieve the parameters of all parent objects.
        foreach ($this->getCidsFromTrace($this->cid_trace) as $cid) {
            $class_info = $this->getStructureInfoByCid($cid);
            $class_name = $this->getClassName($class_info);

            // retrieve all parameters that were set by saveParameterByClass().
            if (isset($this->saved_parameters[$class_name])) {
                foreach ($this->saved_parameters[$class_name] as $param_name) {
                    if ($this->get_request->has($param_name)) {
                        $parameters[$param_name] = $this->get_request->retrieve(
                            $param_name,
                            $this->refinery->to()->string()
                        );
                    } else {
                        $parameters[$param_name] = null;
                    }
                }
            }

            // retrieve all parameters that were set by setParameterByClass().
            if (isset($this->parameters[$class_name])) {
                foreach ($this->parameters[$class_name] as $param_name => $value) {
                    $parameters[$param_name] = $value;
                }
            }
        }

        $command_class_info = $this->getStructureInfoByName(strtolower($a_class));

        // set default GET parameters
        $parameters[self::PARAM_BASE_CLASS] = $this->getBaseClass() ?? $this->getClassName($command_class_info);
        $parameters[self::PARAM_CMD_CLASS]  = $this->getClassName($command_class_info);
        $parameters[self::PARAM_CMD_TRACE]  = $this->cid_trace;
        $parameters[self::PARAM_CMD]        = $a_cmd ?? $this->getCmd();

        return $parameters;
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
     * Helper function to initialize the structure array.
     *
     * @throws ilCtrlException if the artifact path cannot be required.
     */
    private function initStructureOrAbort() : void
    {
        try {
            /** @noinspection PhpIncludeInspection */
            $this->structure = require ilCtrlStructureArtifactObjective::ARTIFACT_PATH;
        } catch (Throwable $exception) {
            throw new ilCtrlException("Could not require structure artifact: " . $exception->getMessage());
        }
    }

    /**
     * Returns the classname of the current request's baseclass.
     *
     * @return string
     * @throws ilCtrlException if ilCtrl was not initialized correctly.
     */
    private function getBaseClass() : string
    {
        if ($this->get_request->has(self::PARAM_BASE_CLASS)) {
            $base_class = $this->get_request->retrieve(
                self::PARAM_BASE_CLASS,
                $this->refinery->to()->string()
            );

            return strtolower($base_class);
        }

        if (null === $this->target->getBaseClass()) {
            throw new ilCtrlException("ilCtrl was not yet initialized with a baseclass. Call " . self::class . "::initBaseClass() first.");
        }

        return $this->target->getBaseClass();
    }

    /**
     * Returns the CID trace of how the given target class can be found.
     *
     * @param string $target_class
     * @param bool   $ignore_baseclass
     * @return string|null
     * @throws ilCtrlException if the trace cannot be found.
     */
    private function determineCidTrace(string $target_class, bool $ignore_baseclass = false) : ?string
    {
        // retrieve information of target class.
        $target_class = strtolower($target_class);
        $target_info  = $this->getStructureInfoByName($target_class);
        $target_cid   = $this->getClassCid($target_info);

        // retrieve information of current class in trace.
        $current_trace = $this->target->getCidTrace();
        $current_cid   = $this->target->getCurrentCid();

        // the target cid can be returned safely, if either
        //      a) there's no trace yet, or
        //      b) the target is already the current cid of trace
        if (null === $current_trace || $target_cid === $current_cid) {
            return $target_cid;
        }

        // at this point a current trace is assured, therefore
        // the current class information can be retrieved.
        $current_info  = $this->getStructureInfoByCid($current_cid);
        $current_class = strtolower($this->getClassName($current_info));

        // now we check if the target class is a direct child of the
        // current class. This relation is true, if either
        //      a) the current class contains the target class within
        //         it's called classes, or
        //      b) the current class is contained in the target classes
        //         called by classes.
        if (in_array($target_class, $this->getCalledClasses($current_info), true) ||
            in_array($current_class, $this->getCalledByClasses($target_info), true)
        ) {
            // if that relation is true, the target cid is appended.
            $this->target->appendCid($target_cid);
        }

        // if the target class is not a direct child of the current one,
        // we loop through every other cid contained in the current trace
        // and check if either a) or b) of the above is true.
        $target_paths = $this->target->getCidPaths(SORT_DESC);
        foreach ($this->target->getCidPieces(SORT_DESC) as $index => $cid) {
            // retrieve info from current iteration.
            $current_info  = $this->getStructureInfoByCid($cid);
            $current_class = strtolower($this->getClassName($current_info));

            if (in_array($target_class, $this->getCalledClasses($current_info), true) ||
                in_array($current_class, $this->getCalledByClasses($target_info), true)
            ) {
                // if any of the parents are related, the cid trace of that
                // parent appended by the target class is used.
                $this->target
                    ->setCidTrace($target_paths[$index])
                    ->appendCid($target_cid)
                ;
            }
        }

        if ($ignore_baseclass) {
            return $this->target->getCidTrace();
        }

        // if none of these scenarios were the case, the target class
        // could be a new baseclass. In that case the target is used to
        // overwrite the existing trace.
        $this->target
            ->setCidTrace($target_cid)
            ->setBaseClass($target_class)
        ;

        // eventually, the trace is returned.
        return $this->target->getCidTrace();
    }

    /**
     * Returns the stored structure information for the given classname.
     *
     * @param string $class_name
     * @return array<string, string>
     * @throws ilCtrlException if the information cannot be found.
     */
    private function getStructureInfoByName(string $class_name) : array
    {
        // lowercase the $class_name in case the developer forgot.
        $class_name = strtolower($class_name);

        if (!isset($this->structure[$class_name])) {
            throw new ilCtrlException("ilCtrl cannot find class information of '$class_name' in artifact. Try `composer du` to re-read the structure.");
        }

        return $this->structure[$class_name];
    }

    /**
     * Returns the information stored in the artifact for the given CID.
     *
     * @param string $cid
     * @return array<string, string>
     * @throws ilCtrlException if the information cannot be found.
     */
    private function getStructureInfoByCid(string $cid) : array
    {
        // check the already mapped for an existing entry.
        if (isset(self::$mapped_structure[$cid])) {
            return self::$mapped_structure[$cid];
        }

        // search for the cid within the structure.
        foreach ($this->structure as $class_info) {
            foreach ($class_info as $key => $value) {
                if (ilCtrlStructureReader::KEY_CID === $key && $cid === $value) {
                    // store a mapped structure entry for the result.
                    self::$mapped_structure[$cid] = $class_info;

                    return $class_info;
                }
            }
        }

        throw new ilCtrlException("ilCtrl cannot find class information of '$cid' in artifact. Try `composer du` to re-read the structure.");
    }

    /**
     * Helper function to fetch CID of passed class information.
     *
     * @param array $class_info
     * @return string
     */
    private function getClassCid(array $class_info) : string
    {
        return $class_info[ilCtrlStructureReader::KEY_CID];
    }

    /**
     * Helper function to fetch classname of passed class information.
     *
     * @param array $class_info
     * @return string
     */
    private function getClassName(array $class_info) : string
    {
        return $class_info[ilCtrlStructureReader::KEY_CLASS_NAME];
    }

    /**
     * Helper function to fetch class path of passed class information.
     *
     * @param array $class_info
     * @return string
     */
    private function getClassPath(array $class_info) : string
    {
        return $class_info[ilCtrlStructureReader::KEY_CLASS_PATH];
    }

    /**
     * Helper function to fetch called classes of passed class information.
     *
     * @param array $class_info
     * @return array
     */
    private function getCalledClasses(array $class_info) : array
    {
        return $class_info[ilCtrlStructureReader::KEY_CALLS] ?? [];
    }

    /**
     * Helper function to fetch called-by classes of passed class information.
     *
     * @param array $class_info
     * @return array
     */
    private function getCalledByClasses(array $class_info) : array
    {
        return $class_info[ilCtrlStructureReader::KEY_CALLED_BY] ?? [];
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
     * Appends a parameter name and value to an existing URL string.
     *
     * This method was imported from @see ilUtil::appendUrlParameterString().
     *
     * @param string $url
     * @param string $parameter_name
     * @param mixed  $parameter_value
     * @param bool   $xml_style
     */
    private function appendUrlParameterString(string &$url, string $parameter_name, $parameter_value, bool $xml_style = false) : void
    {
        $amp = ($xml_style) ? "&amp;" : "&";

        $url = (is_int(strpos($url, "?"))) ?
            $url . $amp . $parameter_name . "=" . $parameter_value :
            $url . "?" . $parameter_name . "=" . $parameter_value
        ;
    }
}