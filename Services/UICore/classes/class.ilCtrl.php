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

use ILIAS\HTTP\Response\Sender\ResponseSendingException;
use ILIAS\HTTP\Response\Sender\ResponseSenderStrategy;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Filesystem\Stream\Streams;
use Psr\Http\Message\ServerRequestInterface;
use GuzzleHttp\Psr7\Response;

/**
 * Class ilCtrl provides processing control methods. A global
 * instance is available through $DIC->ctrl() or $ilCtrl.
 * @author       Thibeau Fuhrer <thf@studer.raimann.ch>
 * @noinspection AutoloadingIssuesInspection
 */
class ilCtrl implements ilCtrlInterface
{
    /**
     * Holds an instance of the http service's response sender.
     * @var ResponseSenderStrategy
     */
    private ResponseSenderStrategy $response_sender;

    /**
     * Holds an instance of the current server request.
     * @var ServerRequestInterface
     */
    private ServerRequestInterface $server_request;

    /**
     * Holds the current requests $_POST parameters.
     * @var RequestWrapper
     */
    private RequestWrapper $post_parameters;

    /**
     * Holds the current requests $_GET parameters.
     * @var RequestWrapper
     */
    private RequestWrapper $get_parameters;

    /**
     * Holds an instance of the refinery factory.
     * @var Refinery
     */
    private Refinery $refinery;

    /**
     * Holds an instance of the currently read control structure.
     * @var ilCtrlStructureInterface
     */
    private ilCtrlStructureInterface $structure;

    /**
     * Holds an instance of the token repository.
     * @var ilCtrlTokenRepositoryInterface
     */
    private ilCtrlTokenRepositoryInterface $token_repository;

    /**
     * Holds the current context information.
     * @var ilCtrlContextInterface
     */
    private ilCtrlContextInterface $context;

    /**
     * Holds an instance of the path factory.
     * @var ilCtrlPathFactoryInterface
     */
    private ilCtrlPathFactoryInterface $path_factory;

    /**
     * Holds an instance of the component factory.
     * @var ilComponentFactory
     */
    private ilComponentFactory $component_factory;

    /**
     * Holds a history of calls made with the current ilCtrl instance.
     * @var array<int, string[]>
     */
    private array $stacktrace = [];

    /**
     * Holds an instance of the object that is currently executed.
     * @var object|null
     */
    private ?object $exec_object = null;

    /**
     * ilCtrl Constructor
     * @param ilCtrlStructureInterface       $structure
     * @param ilCtrlTokenRepositoryInterface $token_repository
     * @param ilCtrlPathFactoryInterface     $path_factory
     * @param ilCtrlContextInterface         $context
     * @param ResponseSenderStrategy         $response_sender
     * @param ServerRequestInterface         $server_request
     * @param RequestWrapper                 $post_parameters
     * @param RequestWrapper                 $get_parameters
     * @param Refinery                       $refinery
     * @param ilComponentFactory             $component_factory
     */
    public function __construct(
        ilCtrlStructureInterface $structure,
        ilCtrlTokenRepositoryInterface $token_repository,
        ilCtrlPathFactoryInterface $path_factory,
        ilCtrlContextInterface $context,
        ResponseSenderStrategy $response_sender,
        ServerRequestInterface $server_request,
        RequestWrapper $post_parameters,
        RequestWrapper $get_parameters,
        Refinery $refinery,
        ilComponentFactory $component_factory
    ) {
        $this->structure = $structure;
        $this->token_repository = $token_repository;
        $this->response_sender = $response_sender;
        $this->server_request = $server_request;
        $this->post_parameters = $post_parameters;
        $this->get_parameters = $get_parameters;
        $this->refinery = $refinery;
        $this->path_factory = $path_factory;
        $this->context = $context;
        $this->component_factory = $component_factory;
    }

    public function __clone()
    {
        $this->structure = clone $this->structure;
    }

    /**
     * @inheritDoc
     */
    public function callBaseClass(string $a_base_class = null): void
    {
        // prioritise the context's baseclass over the given one.
        $a_base_class = $this->context->getBaseClass() ?? $a_base_class;

        // abort if no baseclass was provided.
        if (null === $a_base_class) {
            throw new ilCtrlException(__METHOD__ . " was not given a baseclass and the request doesn't include one either.");
        }

        // abort if the provided baseclass is unknown.
        if (!$this->structure->isBaseClass($a_base_class)) {
            throw new ilCtrlException("Provided class '$a_base_class' is not a baseclass");
        }

        // in case the baseclass was given by argument,
        // set the context's baseclass.
        $this->context->setBaseClass($a_base_class);

        // no null-check needed as previous isBaseClass() was true.
        $obj_name = $this->structure->getObjNameByName($a_base_class);
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
            ->setCmdMode(self::CMD_MODE_PROCESS);

        return $a_gui_object->executeCommand();
    }

    /**
     * @inheritDoc
     */
    public function getHTML(object $a_gui_object, array $a_parameters = null): string
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
            ->setCmdMode(self::CMD_MODE_HTML);

        return (null !== $a_parameters) ?
            $a_gui_object->getHTML($a_parameters) :
            $a_gui_object->getHTML();
    }

    /**
     * @inheritDoc
     */
    public function getCmd(string $fallback_command = null): ?string
    {
        // retrieve $_GET and $_POST parameters.
        $post_command = $this->getPostCommand();
        $get_command = $this->getQueryParam(self::PARAM_CMD);
        $table_command = $this->getTableCommand();

        $is_post = (self::CMD_POST === $get_command);

        // if the $_GET command is 'post', either the $_POST
        // command or $_GETs fallback command is used.
        // for now, the table command is used as fallback as well,
        // but this will be removed once the implementation of
        // table actions change.
        $command = ($is_post) ?
            $post_command ?? $table_command ?? $this->getQueryParam(self::PARAM_CMD_FALLBACK) :
            $get_command;

        // override the command that has been set during a
        // request via ilCtrl::setCmd().
        $context_command = $this->context->getCmd();
        if (null !== $context_command && self::CMD_POST !== $context_command) {
            $command = $context_command;
        }

        if (null !== $command) {
            // if the command is for post requests, or the command
            // is not considered safe, the csrf-validation must pass.
            $cmd_class = $this->context->getCmdClass();
            if (null !== $cmd_class && !$this->isCmdSecure($is_post, $cmd_class, $command)) {
                $stored_token = $this->token_repository->getToken();
                $sent_token = $this->getQueryParam(self::PARAM_CSRF_TOKEN);

                if (null !== $sent_token && $stored_token->verifyWith($sent_token)) {
                    return $command;
                }
            } else {
                return $command;
            }
        }

        return $fallback_command ?? '';
    }

    /**
     * @inheritDoc
     */
    public function setCmd(?string $a_cmd): void
    {
        if (!empty($a_cmd)) {
            $this->context->setCmd($a_cmd);
        } else {
            $this->context->setCmd(null);
        }
    }

    /**
     * @inheritDoc
     */
    public function getCmdClass(): ?string
    {
        return $this->context->getCmdClass() ?? '';
    }

    /**
     * @inheritDoc
     */
    public function setCmdClass($a_cmd_class): void
    {
        if (!empty($a_cmd_class)) {
            $this->context->setCmdClass($a_cmd_class);
        } else {
            $this->context->setCmdClass(null);
        }
    }

    /**
     * @inheritDoc
     */
    public function getNextClass($a_gui_class = null): ?string
    {
        if (null === $a_gui_class && null === $this->exec_object) {
            return '';
        }

        if (null === $this->context->getPath()) {
            return '';
        }

        $next_cid = $this->context->getPath()->getNextCid(
            $this->getClassByObject($a_gui_class ?? $this->exec_object)
        );

        if (null !== $next_cid) {
            return $this->structure->getClassNameByCid($next_cid) ?? '';
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function saveParameter(object $a_gui_obj, $a_parameter): void
    {
        $this->saveParameterByClass($this->getClassByObject($a_gui_obj), $a_parameter);
    }

    /**
     * @inheritDoc
     */
    public function saveParameterByClass(string $a_class, $a_parameter): void
    {
        if (!empty($a_parameter)) {
            if (is_array($a_parameter)) {
                foreach ($a_parameter as $parameter) {
                    $this->structure->setPermanentParameterByClass($a_class, $parameter);
                }
            } else {
                $this->structure->setPermanentParameterByClass($a_class, $a_parameter);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function setParameter(object $a_gui_obj, string $a_parameter, $a_value): void
    {
        $this->setParameterByClass($this->getClassByObject($a_gui_obj), $a_parameter, $a_value);
    }

    /**
     * @inheritDoc
     */
    public function setParameterByClass(string $a_class, string $a_parameter, $a_value): void
    {
        $this->structure->setTemporaryParameterByClass($a_class, $a_parameter, $a_value);
    }

    /**
     * @inheritDoc
     */
    public function getParameterArray(object $a_gui_obj): array
    {
        return $this->getParameterArrayByClass($this->getClassByObject($a_gui_obj));
    }

    /**
     * @inheritDoc
     */
    public function getParameterArrayByClass(string $a_class): array
    {
        if (null === $this->structure->getClassCidByName($a_class)) {
            throw new ilCtrlException("Cannot find provided class '$a_class' in the control structure.");
        }

        $parameters = [];
        $permanent_parameters = $this->structure->getPermanentParametersByClass($a_class);
        if (null !== $permanent_parameters) {
            foreach ($permanent_parameters as $parameter) {
                $parameters[$parameter] = $this->getQueryParam($parameter);
            }
        }

        $temporary_parameters = $this->structure->getTemporaryParametersByClass($a_class);
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
    public function clearParameters(object $a_gui_obj): void
    {
        $this->clearParametersByClass($this->getClassByObject($a_gui_obj));
    }

    /**
     * @inheritDoc
     */
    public function clearParametersByClass(string $a_class): void
    {
        // apparently permanent parameters should not be removable,
        // therefore the line below stays commented:
        // $this->structure->removePermanentParametersByClass($a_class);
        $this->structure->removeTemporaryParametersByClass($a_class);
    }

    /**
     * @inheritDoc
     */
    public function clearParameterByClass(string $a_class, string $a_parameter): void
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
    ): string {
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
    ): string {
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
        string $a_fallback_cmd = null,
        string $a_anchor = null,
        bool $is_async = false,
        bool $has_xml_style = false
    ): string {
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
        string $a_fallback_cmd = null,
        string $a_anchor = null,
        bool $is_async = false,
        bool $has_xml_style = false
    ): string {
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
    ): void {
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
    ): void {
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
    public function redirectToURL(string $target_url): void
    {
        // prepend the ILIAS HTTP path if it wasn't already.
        if (defined("ILIAS_HTTP_PATH") &&
            strpos($target_url, "://") === false &&
            strpos($target_url, "/") !== 0
        ) {
            $target_url = ILIAS_HTTP_PATH . "/" . $target_url;
        }

        // this line can be dropped after discussion with TB or JF,
        // it keeps the functionality of UI plugin hooks alive.
        $target_url = $this->modifyUrlWithPluginHooks($target_url);

        // initialize http response object
        $response = new Response();

        // there's an exceptional case for asynchronous file uploads
        // where a json response is delivered.
        if ('application/json' === $this->server_request->getHeaderLine('Accept')) {
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

            $response = $response->withBody($body);
        } else {
            $response = $response->withAddedHeader('Location', $target_url);
        }

        // manually trigger session_write_close() due to exceptions stored
        // in the ILIAS database, otherwise this method is called by exit()
        // which leads to the exceptions not being written to the database.
        session_write_close();

        try {
            $this->response_sender->sendResponse($response);
        } catch (ResponseSendingException $e) {
            header("Location: $target_url");
            if ('application/json' === $this->server_request->getHeaderLine('Accept')) {
                $content = (null !== $response->getBody()) ?
                    $response->getBody()->getContents() :
                    [];

                echo json_encode($content, JSON_THROW_ON_ERROR);
            }
        } catch (Throwable $t) {
            header("Location: $target_url");
            echo $t->getMessage();
        }

        exit;
    }

    /**
     * @inheritDoc
     */
    public function setContextObject(int $obj_id, string $obj_type): void
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
    public function getContextObjId(): ?int
    {
        return $this->context->getObjId();
    }

    /**
     * @inheritDoc
     */
    public function getContextObjType(): ?string
    {
        return $this->context->getObjType();
    }

    /**
     * @inheritDoc
     */
    public function getCallHistory(): array
    {
        return $this->stacktrace;
    }

    /**
     * @inheritDoc
     */
    public function lookupClassPath(string $a_class): string
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
    public function getClassForClasspath(string $a_class_path): string
    {
        $path_info = pathinfo($a_class_path);

        return substr($path_info['basename'], 6, -4);
    }

    /**
     * @inheritDoc
     */
    public function setTargetScript(string $a_target_script): void
    {
        $this->context->setTargetScript($a_target_script);
    }

    /**
     * @inheritDoc
     */
    public function isAsynch(): bool
    {
        return $this->context->isAsync();
    }

    /**
     * @inheritDoc
     */
    public function setReturn(object $a_gui_obj, string $a_cmd = null): void
    {
        $this->setReturnByClass($this->getClassByObject($a_gui_obj), $a_cmd);
    }

    /**
     * @inheritDoc
     */
    public function setReturnByClass(string $a_class, string $a_cmd = null): void
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
    public function returnToParent(object $a_gui_obj, string $a_anchor = null): void
    {
        $class_name = $this->getClassByObject($a_gui_obj);
        $target_url = $this->getParentReturnByClass($class_name);

        // append redirect source to target url.
        $target_url = $this->appendParameterString(
            $target_url,
            self::PARAM_REDIRECT,
            $class_name
        );

        // append the provided anchor if necessary.
        if (null !== $a_anchor) {
            $target_url .= "#$a_anchor";
        }

        $this->redirectToURL($target_url);
    }

    /**
     * @inheritDoc
     */
    public function getParentReturn(object $a_gui_obj): ?string
    {
        return $this->getParentReturnByClass($this->getClassByObject($a_gui_obj));
    }

    /**
     * @inheritDoc
     */
    public function getParentReturnByClass(string $a_class): ?string
    {
        $path = $this->path_factory->find($this->context, $a_class);
        if (null !== $path->getCidPath()) {
            foreach ($path->getCidArray() as $cid) {
                $current_class = $this->structure->getClassNameByCid($cid);
                $return_target = $this->structure->getReturnTargetByClass($current_class);
                if (null !== $return_target) {
                    return $return_target;
                }
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getRedirectSource(): ?string
    {
        return $this->context->getRedirectSource();
    }

    /**
     * @inheritDoc
     */
    public function insertCtrlCalls($a_parent, $a_child, string $a_comp_prefix): void
    {
        throw new ilCtrlException(__METHOD__ . " is deprecated and must not be used.");
    }

    /**
     * @inheritDoc
     */
    public function checkCurrentPathForClass(string $gui_class): bool
    {
        $class_cid = $this->structure->getClassCidByName($gui_class);
        if (null === $class_cid) {
            return false;
        }

        return strpos(
            $this->context->getPath()->getCidPath() ?? '',
            $class_cid
        ) !== false;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentClassPath(): array
    {
        if (null === $this->context->getPath()->getCidPath()) {
            return [];
        }

        $class_paths = [];
        foreach ($this->context->getPath()->getCidArray(SORT_ASC) as $cid) {
            $class_paths[] = $this->structure->getObjNameByCid($cid);
        }

        return $class_paths;
    }

    /**
     * Returns a parameter with the given name from the current GET
     * request.
     * @param string $parameter_name
     * @return string|null
     */
    private function getQueryParam(string $parameter_name): ?string
    {
        if ($this->get_parameters->has($parameter_name)) {
            return $this->get_parameters->retrieve(
                $parameter_name,
                $this->refinery->to()->string()
            );
        }

        return null;
    }

    /**
     * @deprecated
     */
    private function getTableCommand(): ?string
    {
        if ($this->post_parameters->has('table_top_cmd')) {
            return $this->post_parameters->retrieve(
                'table_top_cmd',
                $this->refinery->custom()->transformation(function ($item): ?string {
                    return is_array($item) ? key($item) : null;
                })
            );
        }
        // Button on top of the table
        if ($this->post_parameters->has('select_cmd2')) {
            return $this->post_parameters->has('selected_cmd2')
                ? $this->post_parameters->retrieve('selected_cmd2', $this->refinery->to()->string())
                : null;
        }
        // Button at bottom of the table
        if ($this->post_parameters->has('select_cmd')) {
            return $this->post_parameters->has('selected_cmd')
                ? $this->post_parameters->retrieve('selected_cmd', $this->refinery->to()->string())
                : null;
        }

        return null;
    }

    /**
     * Returns the current $_POST command.
     * @return string|null
     */
    private function getPostCommand(): ?string
    {
        if ($this->post_parameters->has(self::PARAM_CMD)) {
            return $this->post_parameters->retrieve(
                self::PARAM_CMD,
                $this->refinery->custom()->transformation(
                    static function ($value): ?string {
                        if (!empty($value)) {
                            if (is_array($value)) {
                                // this most likely only works by accident, but
                                // the selected or clicked command button will
                                // always be sent as first array entry. This
                                // should definitely be done differently.
                                return (string) array_key_first($value);
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
    ): ?string {
        if (empty($a_class)) {
            throw new ilCtrlException(__METHOD__ . " was provided with an empty class or class-array.");
        }

        $is_array = is_array($a_class);

        $path = $this->path_factory->find($this->context, $a_class);
        if (null !== ($exception = $path->getException())) {
            throw $exception;
        }

        $base_class = $path->getBaseClass();
        if (null === $base_class) {
            throw new ilCtrlException("Cannot find a valid baseclass in the cid path '{$path->getCidPath()}'");
        }

        $target_url = $this->context->getTargetScript();
        $target_url = $this->appendParameterString(
            $target_url,
            self::PARAM_BASE_CLASS,
            $base_class,
            $is_escaped
        );

        $cmd_class = ($is_array) ?
            $a_class[array_key_last($a_class)] :
            $a_class;

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

        // collect all parameters of classes within the current
        // targets path and append them to the target url.
        foreach ($path->getCidArray(SORT_ASC) as $cid) {
            $class_name = $this->structure->getClassNameByCid($cid);
            if (null === $class_name) {
                throw new ilCtrlException("Classname for cid '$cid' in current path cannot be found.");
            }

            $target_url = $this->appendParameterStringsByClass(
                $class_name,
                $target_url,
                $is_escaped
            );
        }

        // append a csrf token if the command is considered
        // unsafe or the link is for form actions.
        if (!$this->isCmdSecure($is_post, $cmd_class, $a_cmd)) {
            $token = $this->token_repository->getToken();
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
     * @param string $target_url
     * @return string
     */
    private function modifyUrlWithPluginHooks(string $target_url): string
    {
        $ui_plugins = $this->component_factory->getActivePluginsInSlot("uihk");
        foreach ($ui_plugins as $plugin_instance) {
            /** @var $plugin_instance ilUserInterfaceHookPlugin */

            $html = $plugin_instance
                ->getUIClassInstance()
                ->getHTML(
                    'Services/Utilities',
                    'redirect',
                    ["html" => $target_url]
                );

            if (ilUIHookPluginGUI::KEEP !== $html['mode']) {
                $target_url = $plugin_instance
                    ->getUIClassInstance()
                    ->modifyHTML(
                        $target_url,
                        $html
                    );
            }
        }

        return $target_url;
    }

    /**
     * Returns whether a given command is considered safe or not.
     * @param bool        $is_post
     * @param string      $cmd_class
     * @param string|null $cmd
     * @return bool
     */
    private function isCmdSecure(bool $is_post, string $cmd_class, string $cmd = null): bool
    {
        // if no command is specified, the command is
        // considered safe if it's not a POST command.
        if (null === $cmd) {
            return !$is_post;
        }

        // if the given command class doesn't exist, the
        // command is not considered safe as it might've been
        // tampered with.
        $obj_name = $this->structure->getObjNameByName($cmd_class);
        if (null === $obj_name) {
            return false;
        }

        // if the command class does not yet implement the
        // ilCtrlSecurityInterface, the command is considered
        // safe if it's not a POST command.
        if (!is_a($obj_name, ilCtrlSecurityInterface::class, true)) {
            return !$is_post;
        }

        // the post command is considered safe if it's contained
        // in the list of safe post commands.
        if ($is_post) {
            return in_array($cmd, $this->structure->getSafeCommandsByName($cmd_class), true);
        }

        // the get command is considered safe if it's not
        // contained in the list of unsafe get commands.
        return !in_array($cmd, $this->structure->getUnsafeCommandsByName($cmd_class), true);
    }

    /**
     * Appends all parameters for a given class to the given URL.
     * @param string $class_name
     * @param string $target_url
     * @param bool   $is_escaped
     * @return string
     * @throws ilCtrlException
     */
    private function appendParameterStringsByClass(
        string $class_name,
        string $target_url,
        bool $is_escaped = false
    ): string {
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
     * @param string $url
     * @param string $parameter_name
     * @param mixed  $value
     * @param bool   $is_escaped
     * @return string
     */
    private function appendParameterString(
        string $url,
        string $parameter_name,
        $value,
        bool $is_escaped = false
    ): string {
        // transform value into a string, since null will fail we can
        // (temporarily) use the null coalescing operator.
        $value = $this->refinery->kindlyTo()->string()->transform($value ?? '');

        // only append value if its not an empty string. note that empty()
        // cannot be used here since e.g. '0' would be empty.
        if ('' !== $value) {
            // declare ampersand escaped or not, according to
            // the given argument.
            $ampersand = ($is_escaped) ? '&amp;' : '&';

            // check if the given url already contains the given
            // parameter name.
            if (preg_match("/($ampersand|\?)$parameter_name(=|$)/", $url)) {
                // replace the value appended to the given parameter
                // name by the provided one.
                $url = preg_replace("/(?<=($parameter_name=))([^&]*|$)/", (string) $value, $url);
            } else {
                // append the parameter key => value pair and prepend
                // a question mark or ampersand, determined by whether
                // it's the first query param or not.
                $url .= (strpos($url, '?') !== false) ?
                    $ampersand . $parameter_name . '=' . $value :
                    '?' . $parameter_name . '=' . $value;
            }
        }

        return $url;
    }

    /**
     * Helper function that populates a call in the current stacktrace.
     * @param string $class_name
     * @param string $cmd_mode
     */
    private function populateCall(string $class_name, string $cmd_mode): void
    {
        $obj_name = $this->structure->getObjNameByName($class_name);

        $this->stacktrace[] = [
            self::PARAM_CMD_CLASS => $obj_name,
            self::PARAM_CMD_MODE => $cmd_mode,
            self::PARAM_CMD => $this->getCmd(),
        ];
    }

    /**
     * Helper function that returns the class name of a mixed
     * (object or string) parameter.
     * @param object|string $object
     * @return string
     */
    private function getClassByObject($object): string
    {
        return (is_object($object)) ? get_class($object) : $object;
    }
}
