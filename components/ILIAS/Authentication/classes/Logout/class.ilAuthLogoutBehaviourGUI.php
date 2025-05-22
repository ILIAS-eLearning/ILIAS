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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\HTTP\Services as HttpService;
use ILIAS\Refinery\Factory as Refinery;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Authentication\Logout\LogoutDestinations;
use ILIAS\components\Authentication\Logout\ConfigurableLogoutTarget;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;

/**
 * @ilCtrl_isCalledBy ilAuthLogoutBehaviourGUI: ilObjAuthSettingsGUI
 * @ilCtrl_Calls      ilAuthLogoutBehaviourGUI: ilLoginPageGUI
 */
class ilAuthLogoutBehaviourGUI
{
    private ilCtrl $ctrl;
    private ilLanguage $lng;
    private HttpService $http;
    private Refinery $refinery;
    private ilSetting $settings;
    private UIFactory $ui_factory;
    private UIRenderer $ui_renderer;
    private ilGlobalTemplateInterface $tpl;
    private ConfigurableLogoutTarget $configurable_logout_target;

    public function __construct()
    {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->lng = $DIC->language();
        $this->refinery = $DIC->refinery();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->lng->loadLanguageModule('auth');
        $this->settings = new ilSetting('auth');
        $this->configurable_logout_target = new ConfigurableLogoutTarget(
            $this->ctrl,
            $this->settings,
            $DIC->access()
        );
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        if (method_exists($this, $cmd)) {
            $this->$cmd();
        }
        $this->showForm();
    }

    public function getForm(
        ?ServerRequestInterface $request = null,
        array $errors = []
    ): StandardForm {
        $logout_group = $this->ui_factory->input()->field()
            ->group(
                [],
                $this->lng->txt('destination_logout_screen')
            );

        $login_group = $this->ui_factory->input()->field()
            ->group(
                [],
                $this->lng->txt('destination_login_screen'),
                $this->lng->txt('destination_login_screen_info')
            );

        $ref_id = $this->ui_factory->input()->field()
            ->numeric($this->lng->txt('destination_internal_ressource_ref_id'))
            ->withAdditionalTransformation(
                $this->refinery->custom()->constraint(
                    fn($value) => $this->configurable_logout_target->isInRepository($value),
                    fn(callable $txt, $value) => $txt('logout_behaviour_invalid_ref_id', $value)
                )
            )
            ->withAdditionalTransformation(
                $this->refinery->custom()->constraint(
                    fn($value) => $this->configurable_logout_target->isAnonymousAccessible(
                        $value
                    ),
                    fn(callable $txt, $value) => $txt(
                        'logout_behaviour_ref_id_no_access',
                        $value
                    )
                )
            )
            ->withValue($this->settings->get('logout_behaviour_ref_id', ''));
        if (isset($errors['ref_id'])) {
            $ref_id = $ref_id->withError($errors['ref_id']);
        }

        $internal_group = $this->ui_factory->input()->field()
            ->group(
                ['ref_id' => $ref_id],
                $this->lng->txt('destination_internal_ressource')
            );

        $url = $this->settings->get('logout_behaviour_url', '');
        $html = $this->ui_factory->input()->field()
            ->url($this->lng->txt('destination_external_ressource_url'))
            ->withAdditionalTransformation(
                $this->refinery->custom()->constraint(
                    fn($value) => $this->configurable_logout_target->isValidExternalResource(
                        (string) $value
                    ),
                    fn(callable $txt, $value) => $txt('logout_behaviour_invalid_url', $value)
                )
            )
            ->withValue($url);
        if (isset($errors['url'])) {
            $html = $html->withError($errors['url']);
        }

        $external_group = $this->ui_factory->input()->field()
            ->group(
                ['url' => $html],
                $this->lng->txt('destination_external_ressource')
            );

        $logout_behaviour_switchable_group = $this->ui_factory->input()->field()
            ->switchableGroup(
                [
                    LogoutDestinations::LOGOUT_SCREEN->value => $logout_group,
                    LogoutDestinations::LOGIN_SCREEN->value => $login_group,
                    ConfigurableLogoutTarget::INTERNAL_RESSOURCE => $internal_group,
                    ConfigurableLogoutTarget::EXTERNAL_RESSOURCE => $external_group
                ],
                $this->lng->txt('destination_after_logout')
            )
            ->withValue(
                $this->settings->get(
                    'logout_behaviour',
                    LogoutDestinations::LOGOUT_SCREEN->value
                )
            );

        $section = $this->ui_factory->input()->field()
            ->section(
                ['logout_behaviour_settings' => $logout_behaviour_switchable_group],
                $this->lng->txt('logout_behaviour_settings')
            );

        $form = $this->ui_factory->input()->container()->form()
            ->standard(
                $this->ctrl->getFormAction($this, 'saveForm'),
                ['logout_behaviour' => $section]
            );
        if ($request) {
            $form = $form->withRequest($request);
        }

        return $form;
    }

    public function showForm(): void
    {
        $mode = $this->settings->get('logout_behaviour', LogoutDestinations::LOGOUT_SCREEN->value);
        $ref_id = (int) $this->settings->get('logout_behaviour_ref_id', '');
        $content = '';
        if ($mode === ConfigurableLogoutTarget::INTERNAL_RESSOURCE &&
            !$this->configurable_logout_target->isValidInternalResource($ref_id)) {
            $content .= $this->ui_renderer->render(
                $this->ui_factory->messageBox()->failure(
                    $this->lng->txt('logout_behaviour_ref_id_valid_status_changed')
                )
            );
        }
        $content .= $this->ui_renderer->render($this->getForm());

        $this->tpl->setContent($content);
    }

    public function saveForm(): void
    {
        $form = $this->getForm();
        $form = $form->withRequest($this->http->request());
        $section = $form->getInputs()['logout_behaviour'];
        $group = $section->getInputs()['logout_behaviour_settings'];
        $ref_id = $group->getInputs()[ConfigurableLogoutTarget::INTERNAL_RESSOURCE]->getInputs()['ref_id'];
        $url = $group->getInputs()[ConfigurableLogoutTarget::EXTERNAL_RESSOURCE]->getInputs()['url'];

        $data = $form->getData();
        if (!$data || $form->getError() || $ref_id->getError() || $url->getError()) {
            $errors = [];
            if ($ref_id->getError()) {
                $errors['ref_id'] = $ref_id->getError();
            }
            if ($url->getError()) {
                $errors['url'] = $url->getError();
            }
            $this->tpl->setContent($this->ui_renderer->render($this->getForm($this->http->request(), $errors)));
            $this->tpl->printToStdout();

            $this->http->close();
        }
        if (isset($data['logout_behaviour']['logout_behaviour_settings'][0])) {
            $mode = $data['logout_behaviour']['logout_behaviour_settings'][0];

            switch ($mode) {
                case LogoutDestinations::LOGIN_SCREEN->value:
                case LogoutDestinations::LOGOUT_SCREEN->value:
                    break;

                case ConfigurableLogoutTarget::INTERNAL_RESSOURCE:
                    $this->settings->set(
                        'logout_behaviour_ref_id',
                        (string) ($data['logout_behaviour']['logout_behaviour_settings'][1]['ref_id'] ?? '')
                    );
                    break;
                case ConfigurableLogoutTarget::EXTERNAL_RESSOURCE:

                    $url = $data['logout_behaviour']['logout_behaviour_settings'][1]['url'] ?? '';
                    $this->settings->set('logout_behaviour_url', (string) $url);
                    break;
            }
            $this->settings->set('logout_behaviour', $mode);
        }
        $this->ctrl->redirect($this, 'showForm');
    }
}
