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
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Notifications;

use Firebase\JWT\JWT;
use ilCurlConnection;
use ILIAS\HTTP\StatusCode;
use ILIAS\Notifications\Interfaces\InternalPushProvider;
use ILIAS\Notifications\Interfaces\PushProviderInterface;
use ILIAS\Notifications\Model\ilNotificationObject;
use ILIAS\Notifications\Model\Push\PushQueueResult;
use ILIAS\Notifications\Repository\PushRepository;
use ilLogger;
use ilObjUser;
use ilSetting;
use ilUtil;
use Random\Randomizer;

/**
 * This is the lowest common denominator of all popular browsers.
 * For more information see:
 * - https://web.dev/articles/push-notifications-web-push-protocol
 * - https://support.mozilla.org/kb/push-benachrichtigungen-firefox
 * - https://learn.microsoft.com/windows/apps/design/shell/tiles-and-notifications/windows-push-notification-services--wns--overview
 * - https://developer.apple.com/documentation/usernotifications
 */
class ilNotificationPushHandler extends ilNotificationHandler
{
    protected PushRepository $subscription_repo;
    protected ilLogger $logger;
    protected string $public_key;
    protected string $private_key;
    protected string $sub;
    protected bool $is_enabled;
    protected ?PushQueueResult $last_queue_result;
    private PushProviderInterface $provider;

    public function __construct(PushProviderInterface $provider)
    {
        global $DIC;
        $this->provider = $provider;
        $this->logger = $DIC->logger()->root();
        $this->sub = $DIC->settings()->get('admin_email');
        $this->subscription_repo = new PushRepository($DIC->database(), $DIC->user());
        $settings = new ilSetting('notifications');
        $this->public_key = $settings->get('application_server_key');
        $this->private_key = file_get_contents($settings->get('private_key_path'));
        $this->is_enabled = $settings->get('enable_push') === '1';
    }

    /**
     * @param bool $force The use of this parameter is explicitly not recommended! Forced notifications are distributed
     * to a user without agknowledgement of their preferences and should therefore be used with care!
     */
    public function notify(ilNotificationObject $notification): void
    {
        $this->resetLastQueueResult();

        if (!$this->is_enabled) {
            $this->logger->debug('Notifications are globaly disabled.');
            $this->setLastQueueResult(PushQueueResult::FAILED);
            return;
        }

        if (!$this->provider instanceof InternalPushProvider) {
            $id = $notification->handlerParams['setting']['user_pref'];
            if (!$this->validateForUser($notification->user, $id)) {
                $this->logger->debug('Notification for ' . $id . ' not send due to user preferences.');
                $this->setLastQueueResult(PushQueueResult::FAILED);
                return;
            }
        }

        $subscriptions = $this->subscription_repo->getUserSubscriptions($notification->user->getId());
        if ($subscriptions === []) {
            $this->logger->debug('User ' . $notification->user->getId() . ' has no active Subscriptions');
            $this->setLastQueueResult(PushQueueResult::FAILED);
            return;
        }

        $salt = (new Randomizer())->getBytes(16);
        $local_key = $this->base64UrlDecode($this->public_key);
        $content = $this->buildContent($notification);
        $ttl = $notification->handlerParams['setting']['ttl'] ?? 60;
        foreach ($subscriptions as $subscription) {
            $url_parts = parse_url($subscription->getEndpoint());
            $data = [
                'sub' => 'mailto:' . $this->sub,
                'aud' => $url_parts['scheme'] . '://' . $url_parts['host'],
                'exp' => time() + (int) $ttl
            ];

            $user_key = $this->base64UrlDecode($subscription->getP256dh());
            $pre_key = $this->hash(
                openssl_pkey_derive($this->publicVapidToPEM($user_key), $this->private_key, 256),
                $this->base64UrlDecode($subscription->getAuth())
            );
            $key = $this->hash($this->hash($user_key . $local_key, $pre_key, 32, 'WebPush: info'), $salt);
            $encryptedText = openssl_encrypt(
                $content,
                'aes-128-gcm',
                $this->hash('', $key, 16, 'Content-Encoding: aes128gcm'),
                OPENSSL_RAW_DATA,
                $this->hash('', $key, 12, 'Content-Encoding: nonce'),
                $tag
            );
            $post = $salt . $this->padKey($local_key) . $encryptedText . $tag;

            $curl = new ilCurlConnection($subscription->getEndpoint());
            $curl->init();
            $curl->setOpt(CURLOPT_HTTPHEADER, [
                'Content-Encoding: aes128gcm',
                'Authorization: vapid t=' . JWT::encode($data, $this->private_key, 'ES256') . ', k=' . $this->public_key,
                'Ttl: ' . $ttl
            ]);
            $curl->setOpt(CURLOPT_POST, 1);
            $curl->setOpt(CURLOPT_RETURNTRANSFER, 1);
            $curl->setOpt(CURLOPT_POSTFIELDS, $post);
            $response = $curl->exec();

            if ($response === false) {
                $this->logger->error('Push notification [' . $subscription->getAuth() . '] request failed.');
                $this->setLastQueueResult(PushQueueResult::FAILED);
            } else {
                $this->setLastQueueResult(
                    $this->handleResponse($curl->getInfo(CURLINFO_HTTP_CODE), $response, $subscription->getAuth())
                );
            }
            $curl->close();
        }
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }

    private function publicVapidToPEM(string $key): string
    {
        $der_key = pack('H*', '3059301306072a8648ce3d020106082a8648ce3d030107034200') . $key;
        return "-----BEGIN PUBLIC KEY-----\n" .
            chunk_split(base64_encode($der_key), 64, "\n") .
            "-----END PUBLIC KEY-----\n";
    }

    /**
     * @param string $preffix If a info preffix is set it is required by the endpoint to be padded
     */
    private function hash(string $data, string $key, int $length = PHP_INT_MAX, ?string $preffix = null): string
    {
        if ($preffix !== null) {
            $data = $preffix . \chr(0) . $data . \chr(1);
        }
        return mb_substr(hash_hmac('sha256', $data, $key, true), 0, $length, '8bit');
    }

    /**
     * The key needs to be preffixed for the request to fit the endpoints requirement. This might be due to the key
     * conversion and url encoding.
     */
    private function padKey(string $key): string
    {
        return pack('N*', 4096) . pack('C*', mb_strlen($key, '8bit')) . $key;
    }

    /**
     * @return string The endpoints expect an encrypted payload that end on an start of text (STX) character.
     * This might be because of an obligation of the special encoding requirement including ECDH.
     */
    protected function buildContent(ilNotificationObject $notification): string
    {
        $actions = [];
        foreach ($notification->links as $link) {
            $actions[] = [
                'title' => $link->getTitle(),
                'action' => $link->getUrl(),
            ];
        }

        return base64_encode(json_encode([
            $notification->title,
            [
                'data' => ['action' => $notification->action ?? '/'],
                'icon' => $notification->iconPath ?: ilUtil::getImagePath('logo/HeaderIconResponsive.svg'),
                'body' => $notification->shortDescription,
                'actions' => $actions
            ]
        ], JSON_THROW_ON_ERROR)) . \chr(2);
    }

    protected function handleResponse(int $http_code, string $response, string $auth): PushQueueResult
    {
        if ($response !== '') {
            $this->logger->info("Push notification [$auth] response: $response");
        }
        switch ($http_code) {
            case StatusCode::HTTP_OK:
            case StatusCode::HTTP_CREATED:
                $this->logger->debug("Push notification [$auth] successful.");
                return PushQueueResult::SUCCEEDED;
            case StatusCode::HTTP_BAD_REQUEST:
            case StatusCode::HTTP_UNAUTHORIZED:
            case StatusCode::HTTP_FORBIDDEN:
                $this->logger->error("Push notification [$auth] request was invalid.");
                return PushQueueResult::FAILED;
            case StatusCode::HTTP_NOT_FOUND:
            case StatusCode::HTTP_GONE:
                $this->subscription_repo->deleteSubscription($auth);
                $this->logger->debug("Push notification [$auth] endpoint outdated. Subscription removed.");
                return PushQueueResult::FAILED;
            case StatusCode::HTTP_REQUEST_ENTITY_TOO_LARGE:
            case StatusCode::HTTP_TOO_MANY_REQUESTS:
                $this->logger->debug("Push notification [$auth] endpoint blocked due to heavy usage or spam.");
                return PushQueueResult::FAILED;
            default:
                $this->logger->info("Push notification [$auth] went into unkown/browser-specific handling.");
                return PushQueueResult::UNKNOWN;
        }
    }

    protected function resetLastQueueResult(): void
    {
        $this->last_queue_result = null;
    }

    protected function setLastQueueResult(PushQueueResult $result): void
    {
        if (
            $this->last_queue_result === null ||
            $this->last_queue_result === PushQueueResult::FAILED ||
            $result === PushQueueResult::SUCCEEDED
        ) {
            $this->last_queue_result = $result;
        }
    }

    protected function validateForUser(ilObjUser $user, string $id): bool
    {
        return \in_array($id, json_decode($user->getPref('push_notification_provider') ?? '[]'), true);
    }

    public function getLastQueueResult(): PushQueueResult
    {
        return $this->last_queue_result;
    }
}
