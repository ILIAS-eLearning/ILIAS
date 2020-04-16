<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Chatroom/classes/class.ilChatroom.php';

/**
 * Class ilChatroomGUIHandler
 * @author  Jan Posselt <jposselt@databay.de>
 * @author  Thomas joußen <tjoussen@databay.de>
 * @version $Id$
 */
abstract class ilChatroomGUIHandler
{
    /**
     * @var ilChatroomObjectGUI
     */
    protected $gui;

    /**
     * @var ilObjUser
     */
    protected $ilUser;

    /**
     * @var ilCtrl
     */
    protected $ilCtrl;

    /**
     * @var ilLanguage
     */
    protected $ilLng;

    /**
     * @var \ILIAS\Filesystem\Filesystem
     */
    protected $webDirectory;

    /**
     * @var \ILIAS\FileUpload\FileUpload
     */
    protected $upload;

    /**
     * @var \ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var \ilTemplate
     */
    protected $mainTpl;

    /**
     * @var \ILIAS
     */
    protected $ilias;

    /** @var ilNavigationHistory */
    protected $navigationHistory;

    /** @var ilTree */
    protected $tree;

    /** @var ilTabsGUI */
    protected $tabs;
    /**
     * @param ilChatroomObjectGUI $gui
     */
    public function __construct(ilChatroomObjectGUI $gui)
    {
        global $DIC;

        $this->gui = $gui;
        $this->ilUser = $DIC->user();
        $this->ilCtrl = $DIC->ctrl();
        $this->ilLng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->mainTpl = $DIC->ui()->mainTemplate();
        $this->upload = $DIC->upload();
        $this->webDirectory = $DIC->filesystem()->web();
        $this->ilias = $DIC['ilias'];
        $this->tabs = $DIC->tabs();
        $this->navigationHistory = $DIC['ilNavigationHistory'];
        $this->tree = $DIC['tree'];
    }

    /**
     * @param $objectId
     * @return ilChatroom
     */
    protected function getRoomByObjectId($objectId)
    {
        return ilChatroom::byObjectId($objectId);
    }

    /**
     * Checks if a ilChatroom exists. If not, it will send a json encoded response with success = false
     * @param ilChatroom $room
     */
    protected function exitIfNoRoomExists($room)
    {
        if (!$room) {
            $this->sendResponse(
                array(
                    'success' => false,
                    'reason' => 'unkown room',
                )
            );
        }
    }

    /**
     * Sends a json encoded response and exits the php process
     * @param array $response
     */
    public function sendResponse($response)
    {
        echo json_encode($response);
        exit;
    }

    /**
     * Check if user can moderate a chatroom. If false it send a json decoded response with success = false
     * @param ilChatroom     $room
     * @param int            $subRoom
     * @param ilChatroomUser $chat_user
     */
    protected function exitIfNoRoomPermission($room, $subRoom, $chat_user)
    {
        if (!$this->canModerate($room, $subRoom, $chat_user->getUserId())) {
            $this->sendResponse(
                array(
                    'success' => false,
                    'reason' => 'not owner of private room',
                )
            );
        }
    }

    /**
     * Checks if the user has permission to moderate a ilChatroom
     * @param ilChatroom $room
     * @param int        $subRoom
     * @param int        $user_id
     * @return bool
     */
    protected function canModerate($room, $subRoom, $user_id)
    {
        return $this->isMainRoom($subRoom) || $room->isOwnerOfPrivateRoom($user_id, $subRoom) || $this->hasPermission('moderate');
    }

    /**
     * @param int $subRoomId
     * @return bool
     */
    protected function isMainRoom($subRoomId)
    {
        return $subRoomId == 0;
    }

    /**
     * Checks for access with ilRbacSystem
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        return ilChatroom::checkUserPermissions($permission, $this->gui->ref_id);
    }

    /**
     * Executes given $method if existing, otherwise executes
     * executeDefault() method.
     * @param string $method
     * @return mixed
     */
    public function execute($method)
    {
        $this->ilLng->loadLanguageModule('chatroom');

        if (method_exists($this, $method)) {
            return $this->$method();
        } else {
            return $this->executeDefault($method);
        }
    }

    /**
     * @param string $requestedMethod
     * @return mixed
     */
    abstract public function executeDefault($requestedMethod);

    /**
     * Checks for requested permissions and redirects if the permission check failed
     * @param array|string $permission
     */
    public function redirectIfNoPermission($permission)
    {
        if (!ilChatroom::checkUserPermissions($permission, $this->gui->ref_id)) {
            $this->ilCtrl->setParameterByClass('ilrepositorygui', 'ref_id', ROOT_FOLDER_ID);
            $this->ilCtrl->redirectByClass('ilrepositorygui', '');
        }
    }

    /**
     * Checks for success param in an json decoded response
     * @param string $response
     * @return boolean
     */
    public function isSuccessful($response)
    {
        $response = json_decode($response, true);

        return $response !== null && array_key_exists('success', $response) && $response['success'];
    }
}
