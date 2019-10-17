<?php

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\FileUpload;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\Handler\HandlerResult;
use ILIAS\FileUpload\Handler\UploadHandler;

/**
 * Class ilUIDemoFileUploadHandlerGUI
 *
 * @ilCtrl_isCalledBy ilUIDemoFileUploadHandlerGUI: ilUIPluginRouterGUI
 */
class ilUIDemoFileUploadHandlerGUI implements UploadHandler
{

    /**
     * @var \ILIAS\DI\HTTPServices
     */
    protected $http;
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var FileUpload
     */
    protected $upload;


    /**
     * ilUIDemoFileUploadHandlerGUI constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->upload = $DIC->upload();
        $this->http = $DIC->http();
    }


    /**
     * @inheritDoc
     */
    public function getUploadURL() : string
    {
        $this->ctrl->initBaseClass(ilUIPluginRouterGUI::class);

        return $this->ctrl->getLinkTargetByClass([ilUIPluginRouterGUI::class, self::class]);
    }


    public function executeCommand() : void
    {
        $response = $this->http->response()->withBody(Streams::ofString(json_encode($this->getResult())));
        $this->http->saveResponse($response);
        $this->http->sendResponse();
    }


    /**
     * @inheritDoc
     */
    public function getResult() : HandlerResult
    {
        $status = HandlerResult::STATUS_OK;
        $identifier = md5(random_bytes(65));
        $message = "Everything ok";

        return new BasicHandlerResult($status, $identifier, $message);
    }
}
