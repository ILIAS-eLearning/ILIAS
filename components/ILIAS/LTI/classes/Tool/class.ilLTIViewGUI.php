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

use ILIAS\DI\UIServices;
use ILIAS\GlobalScreen\Services as GlobalScreen;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
 * The LTI view of a user who came from a platform: ILIAS shows the launched object without its own
 * navigation, with the styles of the platform and a button that ends the LTI session and returns to the
 * platform. ilLTIViewLayoutProvider shapes the page.
 *
 * The LTI component provides the view as ILIAS\LTI\View, see LTI.php; the legacy environment knows it as
 * $DIC['lti'], which ilFrameTargetInfo and ilLocatorGUI ask whether the view is active.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIViewGUI implements ILIAS\LTI\View
{
    private const string SESSION_CONTEXT_IDS = 'lti_context_ids';
    public const string CMD_EXIT = 'exit';
    public const string GS_EXIT_MODE = 'lti_exit_mode';
    public const string GS_PAGE_TITLE = 'lti_page_title';

    private const string CONTEXT_PARAM = 'lti_context_id';

    private bool $initialised = false;

    public function __construct(
        private readonly GlobalHttpState $http,
        private readonly Refinery $refinery,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly UIServices $ui,
        private readonly ilCtrlInterface $ctrl,
        private readonly ilObjUser $user,
        private readonly ilLanguage $lng,
        private readonly GlobalScreen $global_screen,
        private readonly ilAuthSession $auth_session,
    ) {
    }

    public function init(): void
    {
        $this->initialised = true;
        if (!$this->isActive()) {
            return;
        }

        $this->lng->loadLanguageModule('lti');
        $this->global_screen->tool()->context()->claim()->lti();

        $meta = $this->global_screen->layout()->meta();
        $meta->addCss('./assets/css/lti_view.css');
        $css = $this->getPostData()['launch_presentation_css_url'] ?? '';
        if ($css !== '') {
            $meta->addCss($css);
        }
        // inside the frame of the platform, links meant for the whole window stay in the frame
        $meta->addOnloadCode(
            'if (window.self !== window.top) { const update = (root) => root.querySelectorAll(\'a[target="_top"], form[target="_top"]\').forEach((e) => e.setAttribute("target", "_self")); update(document); new MutationObserver(() => update(document)).observe(document.body, {childList: true, subtree: true}); }'
        );
    }

    public function isActive(): bool
    {
        // before ilInitialisation has set up the user, and without a user, as in the cron job or the SOAP
        // server, there is no view
        if (!$this->initialised || !ilContext::hasUser()) {
            return false;
        }

        return str_starts_with((string) $this->user->getAuthMode(), ilAuthProviderLTI::AUTH_MODE_PREFIX);
    }

    public function executeCommand(): void
    {
        if ($this->ctrl->getCmd() === self::CMD_EXIT) {
            $this->exit();
        }
    }

    /**
     * What the LTI view needs of a launch, for the object the platform launched. After the login the user
     * goes on to the object, see getLaunchTarget().
     *
     * @param array $parameters
     */
    public static function rememberLaunch(int $ref_id, array $parameters): void
    {
        ilSession::set(self::SESSION_CONTEXT_IDS, [$ref_id]);
        ilSession::set(self::getLaunchSessionKey($ref_id), [
            'launch_presentation_return_url' => (string) ($parameters['launch_presentation_return_url'] ?? ''),
            'launch_presentation_css_url' => (string) ($parameters['launch_presentation_css_url'] ?? ''),
            'resource_link_title' => (string) ($parameters['resource_link_title'] ?? ''),
        ]);
        ilSession::set('lti_init_target', ilObject::_lookupType($ref_id, true) . '_' . $ref_id);
    }

    private static function getLaunchSessionKey(int $ref_id): string
    {
        return 'lti_' . $ref_id . '_post_data';
    }

    public function getLaunchTarget(): string
    {
        $ref_id = (int) (((array) ilSession::get(self::SESSION_CONTEXT_IDS))[0] ?? 0);
        if ($ref_id === 0) {
            return '';
        }

        return 'goto.php?target=' . ilObject::_lookupType($ref_id, true) . '_' . $ref_id
            . '&' . self::CONTEXT_PARAM . '=' . $ref_id;
    }

    /**
     * The object the platform launched, as the link tells or as the last launch of the session left it.
     */
    public function getContextId(): int
    {
        $query = $this->http->wrapper()->query();
        if ($query->has(self::CONTEXT_PARAM)) {
            $context_id = $query->retrieve(self::CONTEXT_PARAM, $this->refinery->kindlyTo()->int());
            if ($context_id > 0) {
                return $context_id;
            }
        }

        return (int) (((array) ilSession::get(self::SESSION_CONTEXT_IDS))[0] ?? 0);
    }

    /**
     * @return array what the launch told about the presentation, see ilAuthProviderLTI
     */
    public function getPostData(): array
    {
        return (array) ilSession::get(self::getLaunchSessionKey($this->getContextId()));
    }

    public function getTitle(): string
    {
        $title = $this->getPostData()['resource_link_title'] ?? '';

        return $title === '' ? 'LTI' : 'LTI - ' . $title;
    }

    public function getTitleForExitPage(): string
    {
        return $this->lng->txt('lti_exited');
    }

    /**
     * @throws ilCtrlException
     */
    public function getExitLink(): string
    {
        $this->ctrl->setParameterByClass(self::class, self::CONTEXT_PARAM, $this->getContextId());

        return $this->ctrl->getLinkTargetByClass([ilLTIRouterGUI::class, self::class], self::CMD_EXIT);
    }

    /**
     * Ends the LTI session and returns to the platform, or tells the user so when the platform gave no
     * return address.
     */
    private function exit(): void
    {
        $context_id = $this->getContextId();
        $return_url = (string) ($this->getPostData()['launch_presentation_return_url'] ?? '');

        ilSession::set(self::SESSION_CONTEXT_IDS, array_values(array_filter(
            (array) ilSession::get(self::SESSION_CONTEXT_IDS),
            static fn(mixed $id): bool => (int) $id !== $context_id
        )));
        ilSession::clear(self::getLaunchSessionKey($context_id));

        if ($return_url !== '') {
            $this->logout();
            $this->ctrl->redirectToURL($return_url);
        }

        $content = $this->ui_factory->messageBox()->info($this->lng->txt('lti_exited_info'));
        $this->logout();
        $this->printPage($this->getTitleForExitPage(), [$content]);
    }

    /**
     * A page of LTI without any navigation of ILIAS, for a user who is not or no longer logged in: the page
     * after the exit, and the page ILIAS as tool shows a Deep Linking request on.
     *
     * @param array $content the components of the page
     */
    public function printPage(string $title, array $content): void
    {
        $context = $this->global_screen->tool()->context();
        if (!$this->isActive()) {
            $context->claim()->lti();
        }
        $context->current()->addAdditionalData(self::GS_EXIT_MODE, true);
        $context->current()->addAdditionalData(self::GS_PAGE_TITLE, $title);

        $tpl = $this->ui->mainTemplate();
        $tpl->setTitle($title, true);
        $tpl->setContent($this->ui_renderer->render($content));
        $tpl->printToStdout();
    }

    /**
     * The ILIAS session ends with the last LTI session in it.
     */
    private function logout(): void
    {
        if ((array) ilSession::get(self::SESSION_CONTEXT_IDS) !== []) {
            return;
        }

        $this->auth_session->setExpired(true);
        session_destroy();
        ilLTISessionCookie::remove();
    }
}
