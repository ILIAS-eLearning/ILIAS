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
     * @var string public POST command name.
     */
    public const CMD_POST = 'post';

    /**
     * different modes used for UI plugins (or in dev-mode).
     */
    public const UI_MODE_PROCESS = 'execComm';
    public const UI_MODE_HTML    = 'getHtml';

    /**
     * HTTP request parameter names, that are needed throughout
     * this service.
     */
    public  const PARAM_CSRF_TOKEN      = 'token';
    private const PARAM_REDIRECT        = 'redirectSource';
    private const PARAM_BASE_CLASS      = 'baseClass';
    private const PARAM_CMD_FALLBACK    = 'fallbackCmd';
    private const PARAM_CMD_CLASS       = 'cmdClass';
    private const PARAM_CMD_MODE        = 'cmdMode';
    private const PARAM_CMD_TRACE       = 'cmdNode';
    private const PARAM_CMD             = 'cmd';

    /**
     * @var string command mode for asynchronous requests.
     */
    private const CMD_MODE_ASYNC = 'asynch';

    /**
     * @var string separator used for CID-traces.
     */
    private const CID_TRACE_SEPARATOR = ':';

    /**
     * HTTP request type constants, might be extended further when
     * accepting REST API.
     */
    private const HTTP_METHOD_POST = 'POST';
    private const HTTP_METHOD_GET  = 'GET';

    /**
     * Constants for the context information array keys.
     */
    private const CONTEXT_KEY_OBJ_ID        = 'obj_id';
    private const CONTEXT_KEY_OBJ_TYPE      = 'obj_type';
    private const CONTEXT_KEY_SUB_OBJ_ID    = 'sub_obj_id';
    private const CONTEXT_KEY_SUB_OBJ_TYPE  = 'sub_obj_type';

    /**
     * @var ilPluginAdmin
     */
    private ilPluginAdmin $plugin_service;

    /**
     * @var HttpServices
     */
    private HttpServices $http_service;

    /**
     * @var ilDBInterface
     */
    private ilDBInterface $database;

    /**
     * @var RequestWrapper
     */
    private RequestWrapper $get_request;

    /**
     * @var RequestWrapper
     */
    private RequestWrapper $post_request;

    /**
     * @var Refinery
     */
    private Refinery $refinery;

    /**
     * Holds the cached CID's mapped to their according structure information.
     *
     * @var array<string, string>
     */
    private static array $cid_mapped_structure = [];

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
     * Holds the current context information. e.g. obj_id, obj_type etc.
     *
     * @var array<string, int|string>
     */
    private array $context_information = [];

    /**
     * Holds the current command that should be executed.
     *
     * @TODO: maybe drop this
     *
     * @var string|null
     */
    private ?string $command = null;

    /**
     * Holds the current command class that should be executed.
     *
     * @TODO: maybe drop this
     *
     * @var string|null
     */
    private ?string $command_class = null;

    /**
     * Holds the current baseclass that should be executed.
     *
     * @TODO: maybe drop this
     *
     * @var string|null
     */
    private ?string $base_class = null;

    /**
     * Holds the base-script for link targets.
     *
     * @var string
     */
    private string $target_script = 'ilias.php';

    /**
     * @TODO: maybe drop this.
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
     * Holds the current CID trace (e.g. 'cid1:cid2:cid3').
     *
     * @var string|null
     */
    private ?string $cid_trace = null;

    /**
     * Holds the read control structure from the php artifact.
     *
     * @var array<string, string>
     */
    private array $structure;

    /**
     * ilCtrl constructor
     */
    public function __construct()
    {
        /**
         * @var $DIC \ILIAS\DI\Container
         */
        global $DIC;

        $this->structure = require ilCtrlStructureArtifactObjective::ARTIFACT_PATH;

        $this->http_service = $DIC->http();
        $this->database     = $DIC->database();
        $this->get_request  = $DIC->http()->wrapper()->query();
        $this->post_request = $DIC->http()->wrapper()->post();

        if (isset($DIC['ilPluginAdmin'])) {
            $this->plugin_service = $DIC['ilPluginAdmin'];
        }

        // $DIC->refinery() is not initialized at this point.
        $this->refinery = new Refinery(
            new DataFactory(),
            $DIC->language()
        );
    }

    // BEGIN PRIVATE METHODS
    // @TODO: move private methods to the bottom of $this

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
     * Helper function to loop through cids of a given trace.
     *
     * @param string $cid_trace
     * @return Generator|string[]
     */
    private function getCidsFromTrace(string $cid_trace) : Generator
    {
        foreach (explode(self::CID_TRACE_SEPARATOR, $cid_trace) as $cid) {
            yield $cid;
        }
    }

    /**
     * Returns the request wrapper according to the HTTP method.
     *
     * @return RequestWrapper
     * @throws ilException if the HTTP method is not supported.
     */
    private function getRequest() : RequestWrapper
    {
        $request_method = $this->getRequestMethod();

        switch ($request_method) {
            case self::HTTP_METHOD_POST:
                return $this->http_service->wrapper()->post();

            case self::HTTP_METHOD_GET:
                return $this->http_service->wrapper()->query();

            default:
                throw new ilException("HTTP request method '$request_method' is not yet supported.");
        }
    }

    /**
     * Returns the classname of the current request's baseclass.
     *
     * @return string|null
     */
    private function getBaseClass() : ?string
    {
        $request = $this->http_service->wrapper()->query();
        if ($request->has(self::PARAM_BASE_CLASS)) {
            $class_name = $request->retrieve(
                self::PARAM_BASE_CLASS,
                $this->refinery->to()->string()
            );

            $base_class = strtolower($class_name);
        } else {
            $base_class = $this->base_class;
        }

        return $base_class ?? null;
    }

    /**
     * Returns the information stored in the artifact for the
     * given classname.
     *
     * @param string $class_name
     * @return array<int, string>
     * @throws ilException if the classname was not read.
     */
    private function getClassInfoByName(string $class_name) : array
    {
        // lowercase the $class_name in case the developer forgot.
        $class_name = strtolower($class_name);

        if (!isset($this->structure[$class_name])) {
            throw new ilException("Class '$class_name' was not yet read by the " . ilCtrlStructureReader::class . ". Try `composer du` to build artifacts first.");
        }

        return $this->structure[$class_name];
    }

    /**
     * Returns the information stored in the artifact for the given CID.
     *
     * @param string $cid
     * @return array<int, string>
     * @throws ilCtrlException if the given CID was not found.
     */
    private function getClassInfoByCid(string $cid) : array
    {
        // check the cached cid-map for an existing entry.
        if (isset(self::$cid_mapped_structure[$cid])) {
            return self::$cid_mapped_structure[$cid];
        }

        foreach ($this->structure as $class_info) {
            foreach ($class_info as $key => $value) {
                if (ilCtrlStructureReader::KEY_CID === $key && $cid === $value) {
                    // store a cached cid-map entry for the found information.
                    self::$cid_mapped_structure[$cid] = $class_info;
                    return $class_info;
                }
            }
        }

        throw new ilCtrlException("The demanded CID '$cid' was not found. Try `composer du` to create artifacts first.");
    }

    /**
     * Returns the CID trace for the provided classname.
     *
     * @param string $target_class
     * @param string $cid_trace
     * @return string|null
     */
    private function getTraceForTargetClass(string $target_class, string $cid_trace = null) : ?string
    {
        // lowercase the $target_class in case the developer forgot.
        $target_class = strtolower($target_class);
        $target_info  = $this->getClassInfoByName($target_class);
        $target_cid   = $this->getCidFromInfo($target_info);

        // the target cid can be returned, if its the only one in trace.
        if (null === $cid_trace || $target_cid === $cid_trace) {
            return $target_cid;
        }

        $current_cid   = $this->getCurrentCidFromTrace($cid_trace);
        $current_info  = $this->getClassInfoByCid($current_cid);
        $current_class = strtolower($this->getClassFromInfo($current_info));

        // the target cid can be returned, if it's the current cid from trace.
        if ($target_cid === $current_cid) {
            return $target_cid;
        }

        $d = $this->getCalledClassesFromInfo($current_info);
        $k = $this->getCalledByClassesFromInfo($target_info);

        // the target cid is appended, if it's a child of the current cid.
        // it is possible to establish that relation in two ways:
        //   a) the parent ($current_cid) has an @ilCtrl_calls $target_cid statement, or
        //   b) the child ($target_cid) has an @ilCtrl_calledBy $current_cid statement.
        if (in_array($target_class, $this->getCalledClassesFromInfo($current_info), true) ||
            in_array($current_class, $this->getCalledByClassesFromInfo($target_info), true)
        ) {
            return $cid_trace . self::CID_TRACE_SEPARATOR . $target_cid;
        }

        $parent_cid  = $this->getParentCidFromTrace($cid_trace);
        if (null !== $parent_cid) {
            $parent_info = $this->getClassInfoByCid($parent_cid);
            $parent_class = strtolower($this->getClassFromInfo($parent_info));

            // the target cid replaces the current cid, if the target class
            // shares the same grand parent. This relation is established as
            // stated above, with the difference that it's over three classes.
            if (in_array($target_class, $this->getCalledClassesFromInfo($parent_info), true) ||
                in_array($parent_class, $this->getCalledByClassesFromInfo($target_info), true)
            ) {
                // remove the current cid from trace (if possible) and append target.
                $cid_trace = $this->removeCurrentCidFromTrace($cid_trace) ?? $cid_trace;
                return $cid_trace . self::CID_TRACE_SEPARATOR . $target_cid;
            }
        }

        // @TODO: test whether this loops through all parent cids or not.

        $cids = explode(self::CID_TRACE_SEPARATOR, $cid_trace);
        $paths = $this->getPathsForTrace($cid_trace);

        for ($i = (count($cids) - 1); 0 < $i; $i--) {
            $tmp_cid = $cids[$i];
            $tmp_info = $this->getClassInfoByCid($tmp_cid);
            $tmp_class = $this->getClassFromInfo($tmp_info);

            if (in_array($target_class, $this->getCalledClassesFromInfo($tmp_info), true) ||
                in_array($tmp_class, $this->getCalledByClassesFromInfo($target_info), true)
            ) {
                return $paths[$i] . self::CID_TRACE_SEPARATOR . $target_cid;
            }
        }

        $debug_trace = $cid_trace;

        $debug_target = [
            'cid' => $target_cid,
            'class' => $target_class,
            'calls' => $this->getCalledClassesFromInfo($target_info),
            'calledby' => $this->getCalledByClassesFromInfo($target_info),
        ];

        $debug_current = [
            'cid' => $current_cid,
            'class' => $this->getClassFromInfo($current_info),
            'calls' => $this->getCalledClassesFromInfo($current_info),
            'calledby' => $this->getCalledByClassesFromInfo($current_info),
        ];

        if (null !== $this->getParentCidFromTrace($cid_trace)) {
            $debug_parent = [
                'cid' => $this->getParentCidFromTrace($cid_trace),
                'class' => $this->getClassFromInfo($this->getClassInfoByCid($this->getParentCidFromTrace($cid_trace))),
                'calls' => $this->getCalledClassesFromInfo($this->getClassInfoByCid($this->getParentCidFromTrace($cid_trace))),
                'calledby' => $this->getCalledByClassesFromInfo($this->getClassInfoByCid($this->getParentCidFromTrace($cid_trace))),
            ];
        }

        $info = $this->getClassInfoByCid($current_cid);
        $missing_info = $this->getClassInfoByName('ilmystaffgui');

        $break = 1;

        // @TODO: finish the whatever is happening in legacy here, here.

        return null;
    }

    /**
     * Returns the classname of the baseclass for the given cid trace.
     *
     * @param string $cid_trace
     * @return string|null
     * @throws ilException
     */
    private function getBaseClassByTrace(string $cid_trace) : ?string
    {
        // get the most left position of a trace separator.
        $position = strpos($cid_trace, self::CID_TRACE_SEPARATOR);

        // if a position was found, the trace can be reduced to that position.
        if ($position) {
            $base_class_cid  = substr($cid_trace, 0, $position);
            $base_class_info = $this->getClassInfoByCid($base_class_cid);

            return $this->getClassFromInfo($base_class_info);
        }

        return null;
    }

    /**
     * Returns the last appended CID from a cid-trace.
     *
     * @param string $cid_trace
     * @return string
     */
    private function getCurrentCidFromTrace(string $cid_trace) : ?string
    {
        if ('' === $cid_trace) {
            return null;
        }

        $trace = explode(self::CID_TRACE_SEPARATOR, $cid_trace);
        $key   = (count($trace) - 1);

        return $trace[$key];
    }

    /**
     * Returns the second-last appended CID from a cid-trace, if it exists.
     *
     * @param string $cid_trace
     * @return string|null
     */
    private function getParentCidFromTrace(string $cid_trace) : ?string
    {
        if ('' === $cid_trace) {
            return null;
        }

        $trace = explode(self::CID_TRACE_SEPARATOR, $cid_trace);
        $key   = (count($trace) - 2);

        // abort if the index and therefore no parent exists.
        if (0 > $key) {
            return null;
        }

        return $trace[$key];
    }

    /**
     * Returns the given CID trace without the current CID or null, if
     * it was the only CID.
     *
     * @param string $cid_trace
     * @return string|null
     */
    private function removeCurrentCidFromTrace(string $cid_trace) : ?string
    {
        // get the most right position of a trace separator.
        $position = strrpos($cid_trace, self::CID_TRACE_SEPARATOR);

        // if a position was found, the trace can be reduced to that position.
        if ($position) {
            return substr($cid_trace, 0, $position);
        }

        return null;
    }

    /**
     * Returns the cid trace of each cid within the given trace.
     *
     *      $example = array(
     *          'cid1',
     *          'cid1:cid2',
     *          'cid1:cid2:cid3',
     *          ...
     *      );
     *
     * @param string $cid_trace
     * @return array<int, string>
     */
    private function getPathsForTrace(string $cid_trace) : array
    {
        if ('' === $cid_trace) {
            return [];
        }

        $cids = explode(self::CID_TRACE_SEPARATOR, $cid_trace);
        $paths = [];

        foreach ($cids as $i => $cid) {
            if ($i === 0) {
                // on first iteration the cid is added.
                $paths[] = $cid;
            } else {
                // on every other iteration the cid is appended to the
                // one from the last iteration.
                $paths[] = $paths[($i - 1)] . self::CID_TRACE_SEPARATOR . $cid;
            }
        }

        return $paths;
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
     * Helper function to fetch CID of passed class information.
     *
     * @param array $class_info
     * @return string
     */
    private function getCidFromInfo(array $class_info) : string
    {
        return $class_info[ilCtrlStructureReader::KEY_CID];
    }

    /**
     * Helper function to fetch classname of passed class information.
     *
     * @param array $class_info
     * @return string
     */
    private function getClassFromInfo(array $class_info) : string
    {
        return $class_info[ilCtrlStructureReader::KEY_CLASS_NAME];
    }

    /**
     * Helper function to fetch class path of passed class information.
     *
     * @param array $class_info
     * @return string
     */
    private function getPathFromInfo(array $class_info) : string
    {
        return $class_info[ilCtrlStructureReader::KEY_CLASS_PATH];
    }

    /**
     * Helper function to fetch called classes of passed class information.
     *
     * @param array $class_info
     * @return array
     */
    private function getCalledClassesFromInfo(array $class_info) : array
    {
        return $class_info[ilCtrlStructureReader::KEY_CALLS] ?? [];
    }

    /**
     * Helper function to fetch called-by classes of passed class information.
     *
     * @param array $class_info
     * @return array
     */
    private function getCalledByClassesFromInfo(array $class_info) : array
    {
        return $class_info[ilCtrlStructureReader::KEY_CALLED_BY] ?? [];
    }

    /**
     * Helper function to return the current HTTP request method.
     *
     * @return string
     */
    private function getRequestMethod() : string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    // END PRIVATE METHODS

    /**
     * @inheritDoc
     */
    public function callBaseClass() : void
    {
        $class_name = $this->getBaseClass();
        $class_info = $this->getClassInfoByName($class_name);
        $class_name = $this->getClassFromInfo($class_info);

        $this->cid_trace = $this->getCidFromInfo($class_info);

        $this->forwardCommand(new $class_name());
    }

    /**
     * @inheritDoc
     */
    public function getModuleDir()
    {
        throw new ilException(self::class . "::getModuleDir is deprecated.");
    }

    /**
     * @inheritDoc
     */
    public function forwardCommand(object $a_gui_object)
    {
        $class_name = strtolower(get_class($a_gui_object));
        $cid_trace  = $this->getTraceForTargetClass($class_name, $this->cid_trace);

        if (null === $cid_trace) {
            throw new ilException("Cannot forward to class '$class_name', CID-Trace could not be generated.");
        }

        $current_trace = $this->cid_trace;

        // update cid trace and populate call.
        $this->cid_trace = $cid_trace;
        $this->populateCall(
            $class_name,
            $this->getCmd(),
            self::UI_MODE_PROCESS
        );

        $html = $a_gui_object->executeCommand();

        // reset trace why-so-ever.
        $this->cid_trace = $current_trace;

        return $html;
    }

    /**
     * @inheritDoc
     */
    public function getHTML($a_gui_object, array $a_parameters = null) : string
    {
        $class_name = strtolower(get_class($a_gui_object));
        $cid_trace = $this->getTraceForTargetClass($class_name, $this->cid_trace);

        if (null === $cid_trace) {
            throw new ilException("Could not fetch or generate CID trace for target class " . $class_name);
        }

        $current_trace = $this->cid_trace;

        // update cid trace and populate call.
        $this->cid_trace = $cid_trace;
        $this->populateCall(
            $class_name,
            $this->getCmd(),
            self::UI_MODE_HTML
        );

        if (null !== $a_parameters) {
            $html = $a_gui_object->getHTML($a_parameters);
        } else {
            $html = $a_gui_object->getHTML();
        }

        // reset the trace, tbh I have no idea why but apparently
        $this->cid_trace = $current_trace;

        return $html;
    }

    /**
     * @inheritDoc
     */
    public function setContext(
        int $a_obj_id,
        string $a_obj_type,
        int $a_sub_obj_id = 0,
        string $a_sub_obj_type = null
    ) : void {
        $this->context_information[self::CONTEXT_KEY_OBJ_ID]        = $a_obj_id;
        $this->context_information[self::CONTEXT_KEY_OBJ_TYPE]      = $a_obj_type;
        $this->context_information[self::CONTEXT_KEY_SUB_OBJ_ID]    = $a_sub_obj_id;
        $this->context_information[self::CONTEXT_KEY_SUB_OBJ_TYPE]  = $a_sub_obj_type;
    }

    /**
     * @inheritDoc
     */
    public function getContextObjId() : ?int
    {
        return $this->context_information[self::CONTEXT_KEY_OBJ_ID] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getContextObjType() : ?string
    {
        return $this->context_information[self::CONTEXT_KEY_OBJ_TYPE] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getContextSubObjId() : ?int
    {
        return $this->context_information[self::CONTEXT_KEY_SUB_OBJ_ID] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getContextSubObjType() : ?string
    {
        return $this->context_information[self::CONTEXT_KEY_SUB_OBJ_TYPE] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function checkTargetClass($a_class) : bool
    {
        throw new ilCtrlException(__METHOD__ . " should not be used.");
    }

    /**
     * @inheritDoc
     */
    public function getCmdNode() : string
    {
        if ($this->get_request->has(self::PARAM_CMD_TRACE)) {
            return $this->get_request->retrieve(
                self::PARAM_CMD_TRACE,
                $this->refinery->to()->string()
            );
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function addTab($a_lang_var, $a_link, $a_cmd, $a_class)
    {
        throw new ilCtrlException(__METHOD__ . " is deprecated, use ilTabs instead.");
    }

    /**
     * @inheritDoc
     */
    public function getTabs()
    {
        throw new ilCtrlException(__METHOD__ . " is deprecated, use ilTabs instead.");
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
    public function saveParameter(object $a_obj, $a_parameter) : void
    {
        $this->saveParameterByClass(get_class($a_obj), $a_parameter);
    }

    /**
     * @inheritDoc
     */
    public function saveParameterByClass(string $a_class, $a_parameter) : void
    {
        if (empty($a_parameter)) {
            throw new ilCtrlException("Cannot save empty parameters or empty string, " . var_dump($a_parameter) . " given");
        }

        if (!is_array($a_parameter)) {
            $a_parameter = [$a_parameter];
        }

        foreach ($a_parameter as $parameter_name) {
            $this->saved_parameters[strtolower($a_class)][] = $parameter_name;
        }
    }

    /**
     * @inheritDoc
     */
    public function setParameter(object $a_obj, string $a_parameter, $a_value) : void
    {
        $this->setParameterByClass(get_class($a_obj), $a_parameter, $a_value);
    }

    /**
     * @inheritDoc
     */
    public function setParameterByClass(string $a_class, string $a_parameter, $a_value) : void
    {
        $this->parameters[strtolower($a_class)][$a_parameter] = $a_value;
    }

    /**
     * @inheritDoc
     */
    public function clearParameters(object $a_obj) : void
    {
        $this->clearParametersByClass(get_class($a_obj));
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
    public function getNextClass($a_gui_class = null)
    {
        if (null !== $a_gui_class) {
            // abort if an invalid argument was supplied.
            if (!is_object($a_gui_class) && !is_string($a_gui_class)) {
                return false;
            }

            $class_name = (is_object($a_gui_class)) ?
                strtolower(get_class($a_gui_class)) :
                strtolower($a_gui_class)
            ;

            $target_trace = $this->getTraceForTargetClass($class_name, $this->cid_trace);
            if (null !== $target_trace) {
                $current_cid  = $this->getCurrentCidFromTrace($target_trace);
                $current_info = $this->getClassInfoByCid($current_cid);

                return $this->getClassFromInfo($current_info);
            }
        }

        if ($this->get_request->has(self::PARAM_CMD_TRACE)) {
            $cid_trace = $this->get_request->retrieve(
                self::PARAM_CMD_TRACE,
                $this->refinery->to()->string()
            );

            $current_cid = $this->getCurrentCidFromTrace($cid_trace);
            if (null !== $current_cid) {
                $class_info = $this->getClassInfoByCid($cid_trace);
                return $this->getClassFromInfo($class_info);
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function lookupClassPath(string $a_class) : string
    {
        $class_info = $this->getClassInfoByName($a_class);
        return $this->getPathFromInfo($class_info);
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
    public function initBaseClass(string $a_base_class) : void
    {
        $this->base_class = strtolower($a_base_class);
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
    public function getCmd(string $fallback_command = '', array $safe_commands = [], ilCtrlCommandHandler $handler = null) : string
    {
        $get_command  = $this->getQueryCmd();
        $post_command = $this->getPostCmd();

        // all commands which are not $safe_commands MUST pass the
        // CSRF token validation in order to be returned.

        // @TODO: fix CSRF validation
//        if (!empty($safe_commands) &&
//            !$this->verifyToken($this->get_request) &&
//            (
//                !in_array($get_command, $safe_commands, true) ||
//                !in_array($post_command, $safe_commands, true)
//            )
//        ) {
//            return $fallback_command;
//        }

        $command = (self::CMD_POST === $get_command) ?
            $post_command : $get_command
        ;

        // apply temporarily added handlers in case some exceptional
        // command determination needs to happen.
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

        return $command ?? $fallback_command;
    }

    /**
     * @inheritDoc
     */
    public function setCmd(string $a_cmd) : void
    {
        $this->command = $a_cmd;
    }

    /**
     * @inheritDoc
     */
    public function setCmdClass($a_cmd_class) : void
    {
        $class_name = (is_object($a_cmd_class)) ?
            get_class($a_cmd_class) :
            strtolower($a_cmd_class)
        ;

        $this->command_class = $class_name;
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

        return '';
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
    public function getLinkTarget(
        object $a_gui_obj,
        string $a_cmd = "",
        string $a_anchor = "",
        bool $a_asynch = false,
        bool $xml_style = false
    ) : string {
        return $this->getLinkTargetByClass(
            get_class($a_gui_obj),
            $a_cmd,
            $a_anchor,
            $a_asynch,
            $xml_style
        );
    }

    /**
     * @inheritDoc
     */
    public function getLinkTargetByClass(
        $a_class,
        string $a_cmd = "",
        string $a_anchor = "",
        bool $a_asynch = false,
        bool $xml_style = false
    ) : string {
        // force xml style to be disabled for async requests
        if ($a_asynch) {
            $xml_style = false;
        }

        $target_url = $this->getTargetScript();
        $target_url = $this->getUrlParameters($a_class, $target_url, $a_cmd, $xml_style);

        $this->appendRequestTokenParameterString($target_url, $xml_style);

        if ($a_asynch) {
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

        $trace = $this->getTraceForTargetClass($class_name, $this->cid_trace);
        $cids  = explode(self::CID_TRACE_SEPARATOR, $trace);

        for ($i = count($cids); 0 <= $i; $i--) {
            $class_info = $this->getClassInfoByCid($cids[$i]);
            $class_name_of_iteration = $this->getClassFromInfo($class_info);
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
    public function getUrlParameters($a_classes, string $a_str, string $a_cmd = null, bool $xml_style = false) : string
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
     * @inheritDoc
     */
    public function getParameterArray($a_gui_obj, $a_cmd = null) : array
    {
        return $this->getParameterArrayByClass(get_class($a_gui_obj), $a_cmd);
    }

    /**
     * Returns all parameters that have been saved or set using multiple
     * or one classname.
     *
     * @param string      $a_class
     * @param string|null $a_cmd
     * @return array
     */
    private function getParameterArrayByClass(string $a_class, string $a_cmd = null) : array
    {
        if (empty($a_class)) {
            return [];
        }

        if (null === $this->cid_trace) {
            $this->cid_trace = $this->getTraceForTargetClass($a_class);
        }

        $parameters = [];
        // retrieve the parameters of all parent objects.
        foreach ($this->getCidsFromTrace($this->cid_trace) as $cid) {
            $class_info = $this->getClassInfoByCid($cid);
            $class_name = $this->getClassFromInfo($class_info);

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

        $command_class_info = $this->getClassInfoByName(strtolower($a_class));

        // set default GET parameters
        $parameters[self::PARAM_BASE_CLASS] = $this->getBaseClass() ?? $this->getClassFromInfo($command_class_info);
        $parameters[self::PARAM_CMD_CLASS]  = $this->getClassFromInfo($command_class_info);
        $parameters[self::PARAM_CMD_TRACE]  = $this->cid_trace;
        $parameters[self::PARAM_CMD]        = $a_cmd ?? $this->command;

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
            $class_info = $this->getClassInfoByCid($cid);
            if ($gui_class_name === $this->getClassFromInfo($class_info)) {
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
            $class_info = $this->getClassInfoByCid($cid);
            $classes[]  = $this->getClassFromInfo($class_info);
        }

        return $classes;
    }
}