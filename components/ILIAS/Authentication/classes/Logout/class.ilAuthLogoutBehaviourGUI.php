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
use ILIAS\components\Authentication\Logout\ConfigurableLogoutHandler;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;

/**
 * @ingroup           ServicesAuthentication
 * @ilCtrl_isCalledBy ilAuthLogoutBehaviourGUI: ilObjAuthSettingsGUI
 * @ilCtrl_Calls      ilAuthLogoutBehaviourGUI: ilLoginPageGUI
 */
class ilAuthLogoutBehaviourGUI
{
    private int $ref_id;
    private ilCtrl $ctrl;
    private ilLanguage $lng;
    private ilAccess $access;
    private HttpService $http;
    private Refinery $refinery;
    private ilSetting $settings;
    private UIFactory $ui_factory;
    private UIRenderer $ui_renderer;
    private ilGlobalTemplateInterface $tpl;

    public function __construct(int $ref_id)
    {
        global $DIC;
        $this->ref_id = $ref_id;
        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->refinery = $DIC->refinery();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->lng->loadLanguageModule('auth');
        $this->settings = new ilSetting('auth');
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd() . 'Cmd';
        if (method_exists($this, $cmd)) {
            $this->$cmd();
        }
        $this->showForm();
    }

    public function getForm(
        ServerRequestInterface $request = null,
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
                    fn($value) => ilObject::_exists($value, true) && !ilObject::_isInTrash($value),
                    fn(callable $txt, $value) => $txt('logout_behaviour_invalid_ref_id', $value)
                )
            )
            ->withAdditionalTransformation(
                $this->refinery->custom()->constraint(
                    fn($value) => $this->access->checkAccessOfUser(ANONYMOUS_USER_ID, 'read', '', $value),
                    fn(callable $txt, $value) => $txt('logout_behaviour_ref_id_no_access', $value)
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
                    fn($value) => filter_var($value, FILTER_VALIDATE_URL) !== false,
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
                    'logout_screen' => $logout_group,
                    'login_screen' => $login_group,
                    'internal_ressource' => $internal_group,
                    'external_ressource' => $external_group
                ],
                $this->lng->txt('destination_after_logout')
            )
            ->withValue($this->settings->get('logout_behaviour', 'logout_screen'));

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
        $this->tpl->setContent($this->ui_renderer->render($this->getForm()));
    }

    public function saveFormCmd(): void
    {
        $form = $this->getForm();
        $form = $form->withRequest($this->http->request());
        $section = $form->getInputs()['logout_behaviour'];
        $group = $section->getInputs()['logout_behaviour_settings'];
        $ref_id = $group->getInputs()[ConfigurableLogoutHandler::INTERNAL_RESSOURCE]->getInputs()['ref_id'];
        $url = $group->getInputs()[ConfigurableLogoutHandler::EXTERNAL_RESSOURCE]->getInputs()['url'];

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
                case ConfigurableLogoutHandler::LOGOUT_SCREEN:
                case ConfigurableLogoutHandler::LOGIN_SCREEN:
                    break;
                case ConfigurableLogoutHandler::INTERNAL_RESSOURCE:
                    $this->settings->set('logout_behaviour_ref_id', (string) ($data['logout_behaviour']['logout_behaviour_settings'][1]['ref_id'] ?? ''));

                    break;
                case ConfigurableLogoutHandler::EXTERNAL_RESSOURCE:
                    $url = $data['logout_behaviour']['logout_behaviour_settings'][1]['url'] ?? '';
                    $this->settings->set('logout_behaviour_url', (string) $url);

                    break;
            }
            $this->settings->set('logout_behaviour', $mode);
        }
        $this->ctrl->redirect($this, 'showForm');
    }
}
