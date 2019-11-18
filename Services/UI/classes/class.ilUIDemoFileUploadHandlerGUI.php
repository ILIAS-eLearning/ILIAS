<?php

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\FileUpload;
use ILIAS\FileUpload\Handler\BasicHandlerResult;
use ILIAS\FileUpload\Handler\ilCtrlAwareUploadHandler;
use ILIAS\UI\Component\Input\Field\HandlerResult;
use ILIAS\UI\Component\Input\Field\UploadHandler;

/**
 * Class ilUIDemoFileUploadHandlerGUI
 *
 * @ilCtrl_isCalledBy ilUIDemoFileUploadHandlerGUI: ilUIPluginRouterGUI
 */
class ilUIDemoFileUploadHandlerGUI implements UploadHandler, ilCtrlAwareUploadHandler
{

    private const CMD_UPLOAD = 'upload';
    private const CMD_REMOVE = 'remove';
    // private const FILE_ID = UploadHandler::DEFAULT_FILE_ID_PARAMETER;
    private const FILE_ID = 'my_file_id';
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

        return $this->ctrl->getLinkTargetByClass([ilUIPluginRouterGUI::class, self::class], self::CMD_UPLOAD);
    }


    /**
     * @inheritDoc
     */
    public function getFileIdentifierParameterName() : string
    {
        return self::FILE_ID;
    }


    /**
     * @inheritDoc
     */
    public function getFileRemovalURL() : string
    {
        $this->ctrl->initBaseClass(ilUIPluginRouterGUI::class);

        return $this->ctrl->getLinkTargetByClass([ilUIPluginRouterGUI::class, self::class], self::CMD_REMOVE);
    }


    public function executeCommand() : void
    {

        switch ($this->ctrl->getCmd()) {
            case self::CMD_UPLOAD:
                $content = json_encode($this->getUploadResult());
                break;
            case self::CMD_REMOVE:
                $file_identifier = $this->http->request()->getQueryParams()[$this->getFileIdentifierParameterName()];
                $content = json_encode($this->getRemoveResult($file_identifier));
                break;
            default:
                $content = '';
                break;
        }
        $response = $this->http->response()->withBody(Streams::ofString($content));
        $this->http->saveResponse($response);
        $this->http->sendResponse();
    }


    /**
     * @inheritDoc
     */
    private function getUploadResult() : HandlerResult
    {
        $status = HandlerResult::STATUS_OK;
        $identifier = md5(random_bytes(65));
        $message = 'Everything ok';

        return new BasicHandlerResult($this->getFileIdentifierParameterName(), $status, $identifier, $message);
    }


    public function getRemoveResult(string $identifier) : HandlerResult
    {
        $status = HandlerResult::STATUS_OK;
        $message = 'File Deleted';

        return new BasicHandlerResult($this->getFileIdentifierParameterName(), $status, $identifier, $message);
    }
}
