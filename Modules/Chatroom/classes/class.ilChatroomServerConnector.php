<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomServerConnector
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomServerConnector
{
    protected static ?bool $connection_status = null;

    private ilChatroomServerSettings $settings;

    public function __construct(ilChatroomServerSettings $settings)
    {
        $this->settings = $settings;
    }

    public static function checkServerConnection(bool $use_cache = true) : bool
    {
        if ($use_cache && self::$connection_status !== null) {
            return self::$connection_status;
        }

        $connector = new self(ilChatroomAdmin::getDefaultConfiguration()->getServerSettings());
        self::$connection_status = $connector->isServerAlive();

        return self::$connection_status;
    }

    public function isServerAlive() : bool
    {
        $response = $this->file_get_contents(
            $this->settings->getURL('Heartbeat'),
            [
                'http' => [
                    'timeout' => 2
                ],
                'https' => [
                    'timeout' => 2
                ]
            ]
        );

        if (false === $response) {
            return false;
        }

        $responseObject = json_decode($response, false, 512, JSON_THROW_ON_ERROR);

        return $responseObject instanceof stdClass && ((int) $responseObject->status) === 200;
    }

    /**
     * Creates connect URL using given $scope and $userId and returns it.
     * @param int $scope
     * @param int $userId
     * @return string|false
     */
    public function connect(int $scope, int $userId)
    {
        return $this->file_get_contents(
            $this->settings->getURL('Connect', (string) $scope) . '/' . $userId
        );
    }

    /**
     * @param string $url
     * @param array $stream_context_params
     * @return string|false
     */
    protected function file_get_contents(string $url, ?array $stream_context_params = null)
    {
        $credentials = $this->settings->getAuthKey() . ':' . $this->settings->getAuthSecret();
        $header =
            "Connection: close\r\n" .
            "Content-Type: application/json; charset=utf-8\r\n" .
            "Authorization: Basic " . base64_encode($credentials);

        $ctx = [
            'http' => [
                'method' => 'GET',
                'header' => $header
            ],
            'https' => [
                'method' => 'GET',
                'header' => $header
            ]
        ];

        if (is_array($stream_context_params)) {
            $ctx = array_merge_recursive($ctx, $stream_context_params);
        }

        set_error_handler(static function ($severity, $message, $file, $line) : void {
            throw new ErrorException($message, $severity, $severity, $file, $line);
        });

        try {
            $response = file_get_contents($url, false, stream_context_create($ctx));
            return $response;
        } catch (Exception $e) {
            ilLoggerFactory::getLogger('chatroom')->alert($e->getMessage());
        } finally {
            restore_error_handler();
        }

        return false;
    }

    /**
     * @param int $scope
     * @param int $subScope
     * @param int $user
     * @param string $title
     * @return string|false
     */
    public function sendCreatePrivateRoom(int $scope, int $subScope, int $user, string $title)
    {
        return $this->file_get_contents(
            $this->settings->getURL('CreatePrivateRoom', (string) $scope) .
            '/' . $subScope . '/' . $user . '/' . rawurlencode($title)
        );
    }

    /**
     * @param int $scope
     * @param int $subScope
     * @param int $user
     * @return string|false
     */
    public function sendDeletePrivateRoom(int $scope, int $subScope, int $user)
    {
        return $this->file_get_contents(
            $this->settings->getURL('DeletePrivateRoom', (string) $scope) . '/' . $subScope . '/' . $user
        );
    }

    /**
     * @param int $scope
     * @param int $subScope
     * @param int $user
     * @return string|false
     * @deprecated Please use sendEnterPrivateRoom instead
     */
    public function enterPrivateRoom(int $scope, int $subScope, int $user)
    {
        return $this->sendEnterPrivateRoom($scope, $subScope, $user);
    }

    /**
     * @param int $scope
     * @param int $subScope
     * @param int $user
     * @return string|false
     */
    public function sendEnterPrivateRoom(int $scope, int $subScope, int $user)
    {
        return $this->file_get_contents(
            $this->settings->getURL('EnterPrivateRoom', (string) $scope) . '/' . $subScope . '/' . $user
        );
    }

    /**
     * @param int $scope
     * @param int $subScope
     * @param int $user
     * @return string|false
     */
    public function sendClearMessages(int $scope, int $subScope, int $user)
    {
        return $this->file_get_contents(
            $this->settings->getURL('ClearMessages', (string) $scope) . '/' . $subScope . '/' . $user
        );
    }

    /**
     * @param int $scope
     * @param int $subScope
     * @param int $user
     * @return string|false
     */
    public function leavePrivateRoom(int $scope, int $subScope, int $user)
    {
        return $this->sendLeavePrivateRoom($scope, $subScope, $user);
    }

    /**
     * @param int $scope
     * @param int $subScope
     * @param int $user
     * @return string|false
     */
    public function sendLeavePrivateRoom(int $scope, int $subScope, int $user)
    {
        return $this->file_get_contents(
            $this->settings->getURL('LeavePrivateRoom', (string) $scope) . '/' . $subScope . '/' . $user
        );
    }

    /**
     * @param int $scope
     * @param int $subScope
     * @param int $user
     * @return string|false
     */
    public function sendKick(int $scope, int $subScope, int $user)
    {
        return $this->kick($scope, $subScope, $user);
    }

    /**
     * Returns kick URL
     * Creates kick URL using given $scope and $query and returns it.
     * @param int $scope
     * @param int $subScope
     * @param int $user
     * @return string|false
     */
    public function kick(int $scope, int $subScope, int $user)
    {
        return $this->file_get_contents(
            $this->settings->getURL('Kick', (string) $scope) . '/' . $subScope . '/' . $user
        );
    }

    /**
     * @param int $scope
     * @param int $subScope
     * @param int $user
     * @return string|false
     */
    public function sendBan(int $scope, int $subScope, int $user)
    {
        return $this->file_get_contents(
            $this->settings->getURL('Ban', (string) $scope) . '/' . $subScope . '/' . $user
        );
    }

    public function getSettings() : ilChatroomServerSettings
    {
        return $this->settings;
    }

    /**
     * @param int $scope
     * @param int $subScope
     * @param int $user
     * @param int $invited_id
     * @return string|false
     */
    public function sendInviteToPrivateRoom(int $scope, int $subScope, int $user, int $invited_id)
    {
        return $this->file_get_contents(
            $this->settings->getURL('InvitePrivateRoom', (string) $scope) .
            '/' . $subScope . '/' . $user . '/' . $invited_id
        );
    }

    /**
     * @param string $message
     * @return string|false
     */
    public function sendUserConfigChange(string $message)
    {
        $query = http_build_query(['message' => $message]);

        return $this->file_get_contents(
            $this->settings->getURL('UserConfigChange', null) . '?' . $query
        );
    }
}
