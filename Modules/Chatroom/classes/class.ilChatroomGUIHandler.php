<?php declare(strict_types=1);

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
 *
 *********************************************************************/

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\FileUpload;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\HTTP\Response\ResponseHeader;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
 * Class ilChatroomGUIHandler
 * @author  Jan Posselt <jposselt@databay.de>
 * @author  Thomas joußen <tjoussen@databay.de>
 * @version $Id$
 */
abstract class ilChatroomGUIHandler
{
    protected ilChatroomObjectGUI $gui;
    protected ilObjUser $ilUser;
    protected ilCtrlInterface $ilCtrl;
    protected ilLanguage $ilLng;
    protected Filesystem $webDirectory;
    protected ilObjectService $obj_service;
    protected FileUpload $upload;
    protected ilRbacSystem $rbacsystem;
    protected ilGlobalTemplateInterface $mainTpl;
    protected ILIAS $ilias;
    protected ilNavigationHistory $navigationHistory;
    protected ilTree $tree;
    protected ilTabsGUI $tabs;
    protected UIFactory $uiFactory;
    protected UIRenderer $uiRenderer;
    protected GlobalHttpState $http;
    protected Refinery $refinery;

    /**
     * @param ilChatroomObjectGUI $gui
     */
    public function __construct(ilChatroomObjectGUI $gui)
    {
        /** @var $DIC \ILIAS\DI\Container */
        global $DIC;

        $this->gui = $gui;
        $this->ilUser = $DIC->user();
        $this->ilCtrl = $DIC->ctrl();
        $this->ilLng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->mainTpl = $DIC->ui()->mainTemplate();
        $this->upload = $DIC->upload();
        $this->webDirectory = $DIC->filesystem()->web();
        $this->obj_service = $DIC->object();
        $this->ilias = $DIC['ilias'];
        $this->tabs = $DIC->tabs();
        $this->navigationHistory = $DIC['ilNavigationHistory'];
        $this->tree = $DIC['tree'];
        $this->uiFactory = $DIC->ui()->factory();
        $this->uiRenderer = $DIC->ui()->renderer();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    /**
     * @param string $key
     * @param Transformation $trafo
     * @param mixed $default
     * @return mixed|null
     */
    protected function getRequestValue(string $key, Transformation $trafo, $default = null)
    {
        if ($this->http->wrapper()->query()->has($key)) {
            return $this->http->wrapper()->query()->retrieve($key, $trafo);
        }

        if ($this->http->wrapper()->post()->has($key)) {
            return $this->http->wrapper()->post()->retrieve($key, $trafo);
        }

        return $default;
    }

    protected function hasRequestValue(string $key) : bool
    {
        if ($this->http->wrapper()->query()->has($key)) {
            return true;
        }

        return $this->http->wrapper()->post()->has($key);
    }

    public function execute(string $method) : void
    {
        $this->ilLng->loadLanguageModule('chatroom');

        if (method_exists($this, $method)) {
            $this->$method();
            return;
        }

        $this->executeDefault($method);
    }

    abstract public function executeDefault(string $requestedMethod) : void;

    /**
     * Checks for requested permissions and redirects if the permission check failed
     * @param string[]|string $permission
     */
    public function redirectIfNoPermission($permission) : void
    {
        if (!ilChatroom::checkUserPermissions($permission, $this->gui->getRefId())) {
            $this->ilCtrl->setParameterByClass(ilRepositoryGUI::class, 'ref_id', ROOT_FOLDER_ID);
            $this->ilCtrl->redirectByClass(ilRepositoryGUI::class);
        }
    }

    /**
     * Checks for success param in an json decoded response
     * @param string|false $response
     * @return bool
     */
    public function isSuccessful($response) : bool
    {
        if (!is_string($response)) {
            return false;
        }

        $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return $response !== null && array_key_exists('success', $response) && $response['success'];
    }

    protected function getRoomByObjectId(int $objectId) : ?ilChatroom
    {
        return ilChatroom::byObjectId($objectId);
    }

    /**
     * Checks if a ilChatroom exists. If not, it will send a json encoded response with success = false
     * @param ?ilChatroom $room
     */
    protected function exitIfNoRoomExists(?ilChatroom $room) : void
    {
        if (null === $room) {
            $this->sendResponse([
                'success' => false,
                'reason' => 'unknown room',
            ]);
        }
    }

    /**
     * Sends a json encoded response and exits the php process
     * @param mixed $response
     * @param bool $isJson
     */
    public function sendResponse($response, bool $isJson = false) : void
    {
        $this->http->saveResponse(
            $this->http->response()
                ->withHeader(ResponseHeader::CONTENT_TYPE, 'application/json')
                ->withBody(Streams::ofString($isJson ? $response : json_encode($response, JSON_THROW_ON_ERROR)))
        );
        $this->http->sendResponse();
        $this->http->close();
    }

    /**
     * Check if user can moderate a chatroom. If false it send a json decoded response with success = false
     * @param ilChatroom $room
     * @param int $subRoom
     * @param ilChatroomUser $chatUser
     */
    protected function exitIfNoRoomModeratePermission(ilChatroom $room, int $subRoom, ilChatroomUser $chatUser) : void
    {
        if (!$this->canModerate($room, $subRoom, $chatUser->getUserId())) {
            $this->sendResponse([
                'success' => false,
                'reason' => 'not owner of private room',
            ]);
        }
    }

    protected function canModerate(ilChatroom $room, int $subRoom, int $usrId) : bool
    {
        return (
            $this->isMainRoom($subRoom) ||
            $room->isOwnerOfPrivateRoom($usrId, $subRoom) ||
            $this->hasPermission('moderate')
        );
    }

    protected function isMainRoom(int $subRoomId) : bool
    {
        return $subRoomId === 0;
    }

    public function hasPermission(string $permission) : bool
    {
        return ilChatroom::checkUserPermissions($permission, $this->gui->getRefId());
    }
}
