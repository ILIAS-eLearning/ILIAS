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

use ILIAS\DI\Container;
use ILIAS\Notifications\ilNotificationPushHandler;
use ILIAS\Notifications\Interfaces\InternalPushProvider;
use ILIAS\Notifications\Interfaces\PushProviderInterface;
use ILIAS\Notifications\Model\ilNotificationConfig;
use ILIAS\Notifications\Model\ilNotificationObject;
use ILIAS\Notifications\Model\Push\PushSubscription;
use ILIAS\Notifications\Provider\NotificationsPushProvider;
use ILIAS\Notifications\Repository\PushRepository;
use ILIAS\UI\Component\Input\Container\Form\Form;

/**
 * @ilCtrl_IsCalledBy ilPersonalNotificationsSettingsGUI: ilNotificationGUI
 */
readonly class ilPersonalNotificationsSettingsGUI
{
    protected Container $dic;
    protected PushRepository $repo;
    /** @var PushProviderInterface[] */
    protected array $pushProvider;

    public function __construct(?Container $dic = null)
    {
        if ($dic === null) {
            global $DIC;
            $dic = $DIC;
        }
        $this->dic = $dic;
        $this->repo = new PushRepository($this->dic->database(), $this->dic->user());
        $provider = [];
        foreach (require PushNotificationObjective::PATH() as $class) {
            $provider[] = new $class();
        }
        $this->pushProvider = $provider;
    }

    public function executeCommand(): void
    {
        if ((new ilSetting('notifications'))->get('enable_push') !== '1') {
            $this->dic->ui()->mainTemplate()->setOnScreenMessage(
                $this->dic->ui()->mainTemplate()::MESSAGE_TYPE_FAILURE,
                $this->dic->language()->txt('permission_denied')
            );
            $this->dic->ui()->mainTemplate()->printToStdout();
            return;
        }

        $this->dic->language()->loadLanguageModule('notifications_adm');
        $this->dic->ui()->mainTemplate()->setTitle($this->dic->language()->txt('push_settings'));
        $this->dic->ui()->mainTemplate()->setTitleIcon(ilUtil::getImagePath('standard/icon_nota.svg'));
        $this->dic->tabs()->addTab('client', $this->dic->language()->txt('client_settings'), $this->dic->ctrl()->getLinkTargetByClass(self::class, 'showClientSettings'));
        if ($this->pushProvider !== []) {
            $this->dic->tabs()->addTab('user', $this->dic->language()->txt('user_settings'), $this->dic->ctrl()->getLinkTargetByClass(self::class, 'showUserSettings'));
        }

        switch ($this->dic->ctrl()->getCmd()) {
            case 'showUserSettings':
                $this->dic->tabs()->activateTab('user');
                if ($this->pushProvider !== []) {
                    $this->dic->ui()->mainTemplate()->setContent($this->dic->ui()->renderer()->render($this->getForm()));
                    $this->dic->ui()->mainTemplate()->printToStdout();
                } else {
                    $this->dic->ctrl()->redirectByClass(self::class, 'showClientSettings');
                }
                break;
            case 'saveUserSettings':
                $this->dic->tabs()->activateTab('user');
                $form = $this->getForm()->withRequest($this->dic->http()->request());
                $data = $form->getData();
                if ($data !== null) {
                    $active = [];
                    foreach ($data['provider'] ?? [] as $key => $value) {
                        if ($value === true) {
                            $active[] = $key;
                        }
                    }
                    $this->dic->user()->setPref('push_notification_provider', json_encode($active));
                    $this->dic->user()->update();
                    $this->dic->ui()->mainTemplate()->setOnScreenMessage(
                        $this->dic->ui()->mainTemplate()::MESSAGE_TYPE_SUCCESS,
                        $this->dic->language()->txt('saved_successfully')
                    );
                }
                $this->dic->ui()->mainTemplate()->setContent($this->dic->ui()->renderer()->render($form));
                $this->dic->ui()->mainTemplate()->printToStdout();
                break;
            case 'addSubscription':
                $this->addSubscription();
                break;
            case 'removeSubscription':
                $this->removeSubscription();
                break;
            case 'showClientSettings':
            default:
                $this->dic->tabs()->activateTab('client');
                if (!($this->dic->http()->wrapper()->post()->has('auth') && $this->dic->http()->wrapper()->post()->has('perm'))) {
                    $this->fetchClientData();
                }
                $this->showClientSettings();
        }
    }

    protected function checkSubscription(string $auth): bool
    {
        if ($auth === '') {
            return true;
        }
        foreach ($this->repo->getUserSubscriptions() as $subscription) {
            if ($auth === $subscription->getAuth()) {
                return true;
            }
        }

        return false;
    }

    protected function addSubscription(): void
    {
        $data = json_decode(
            $this->dic->http()->wrapper()->post()->retrieve('subscription', $this->dic->refinery()->to()->string()),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        if (!$this->checkSubscription($data['keys']['auth'])) {
            $this->repo->addSubscription(new PushSubscription(
                $data['endpoint'],
                $data['keys']['auth'],
                $data['keys']['p256dh']
            ));
        }

        (new NotificationsPushProvider(new InternalPushProvider()))->push(
            $this->dic->user(),
            $this->dic->language()->txt('push_subscription_successfull'),
            $this->dic->language()->txt('push_subscription_successfull_desc')
        );
    }

    protected function removeSubscription(): void
    {
        $auth = $this->dic->http()->wrapper()->post()->retrieve('auth', $this->dic->refinery()->to()->string());
        if ($this->checkSubscription($auth)) {
            $this->repo->deleteSubscription($auth);
        }
    }

    protected function showClientSettings(): void
    {
        $this->dic->ui()->mainTemplate()->addJavaScript('assets/js/push-subscription.js');

        $auth = $this->dic->http()->wrapper()->post()->retrieve('auth', $this->dic->refinery()->kindlyTo()->string());
        $perm = $this->dic->http()->wrapper()->post()->retrieve('perm', $this->dic->refinery()->kindlyTo()->string());
        $agent = $this->dic->http()->request()->getServerParams()['HTTP_USER_AGENT'] ?? '';

        if ($perm === 'denied') {
            $this->dic->ui()->mainTemplate()->setOnScreenMessage(
                $this->dic->ui()->mainTemplate()::MESSAGE_TYPE_QUESTION,
                $this->dic->language()->txt('push_client_inactive')
            );
        }
        if (!$this->checkSubscription($auth)) {
            $this->dic->ui()->mainTemplate()->setOnScreenMessage(
                $this->dic->ui()->mainTemplate()::MESSAGE_TYPE_FAILURE,
                $this->dic->language()->txt('push_client_already_used')
            );
        }
        if ($perm !== 'granted' && preg_match('/(Edg\/)/', $agent)) {
            $this->dic->ui()->mainTemplate()->setOnScreenMessage(
                $this->dic->ui()->mainTemplate()::MESSAGE_TYPE_INFO,
                $this->dic->language()->txt('push_client_edge_case')
            );
        }
        if ($perm === '' && preg_match('/(Mac)/', $agent)) {
            $this->dic->ui()->mainTemplate()->setOnScreenMessage(
                $this->dic->ui()->mainTemplate()::MESSAGE_TYPE_INFO,
                $this->dic->language()->txt('push_client_ios_case')
            );
        }

        if ($this->checkSubscription($auth) && ($perm === 'granted' || $perm === 'default')) {
            $public_key = (new ilSetting('notifications'))->get('application_server_key');
            $target = $this->dic->ctrl()->getLinkTargetByClass(self::class, 'default');
            $toggle = $this->dic->ui()->factory()->button()->toggle($this->dic->language()->txt('activate'), '', '')
                ->withEngagedState($auth !== '')
                ->withAdditionalOnLoadCode(static fn($id) => "il.Notifications.initToggle($id, '$public_key', '$target')");
            $this->dic->ui()->mainTemplate()->setContent($this->dic->ui()->renderer()->render($toggle));
        }

        $this->dic->ui()->mainTemplate()->printToStdout();
    }

    /**
     * Respond a self-submitting form to fetch client data for push notifications
     */
    protected function fetchClientData(): never
    {
        $target = ILIAS_HTTP_PATH . '/' . $this->dic->ctrl()->getLinkTargetByClass(self::class, 'showClientSettings');

        $this->dic->ui()->mainTemplate()->setContent($this->dic->ui()->renderer()->render(
            $this->dic->ui()->factory()->legacy()->content('')->withOnLoadCode(
                static fn($id) => "
                    navigator.serviceWorker.ready.then((reg) => {
                        (reg.pushManager || {getSubscription: () => Promise.resolve(null)}).getSubscription().then((sub) => {
                            const form = document.createElement('form');
                            form.method = 'post';
                            form.action = '$target';
                            let auth = document.createElement('input');
                            auth.name = 'auth';
                            auth.value = (sub === null) ? '' : sub.toJSON().keys.auth;
                            form.appendChild(auth);
                            perm = document.createElement('input');
                            perm.name = 'perm';
                            perm.value = (typeof Notification === 'undefined') ? '' : Notification.permission;
                            form.appendChild(perm);
                            document.body.appendChild(form);
                            form.submit();
                        })
                    });
                "
            )
        ));
        $this->dic->ui()->mainTemplate()->printToStdout();
        $this->dic->http()->close();
    }

    public function getForm(): Form
    {
        $provider = [];
        $prefs = json_decode($this->dic->user()->getPref('push_notification_provider') ?? '[]');
        foreach ($this->pushProvider as $p) {
            $provider[$p->getIdentifier()] = $this->dic->ui()->factory()->input()->field()->checkbox(
                $p->getName($this->dic->language()),
                $p->getDescription($this->dic->language()),
            )->withValue(in_array($p->getIdentifier(), $prefs));
        }

        return $this->dic->ui()->factory()->input()->container()->form()->standard(
            $this->dic->ctrl()->getLinkTargetByClass(self::class, 'saveUserSettings'),
            [
                'provider' => $this->dic->ui()->factory()->input()->field()->section(
                    $provider,
                    $this->dic->language()->txt('available_providers'),
                )
            ]
        );
    }
}
