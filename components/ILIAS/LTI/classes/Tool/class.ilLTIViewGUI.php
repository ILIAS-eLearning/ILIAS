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

/**
 * The LTI view of a user who came from a platform: ILIAS shows the launched object without its own
 * navigation, with the styles of the platform and a button that ends the LTI session and returns to the
 * platform. ilLTIViewLayoutProvider shapes the page.
 *
 * ilInitialisation keeps the instance as $DIC['lti'], which ilFrameTargetInfo asks whether the view is active.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIViewGUI
{
    public const string CMD_EXIT = 'exit';
    public const string GS_EXIT_MODE = 'lti_exit_mode';

    private const string CONTEXT_PARAM = 'lti_context_id';

    private readonly ILIAS\DI\Container $dic;

    public function __construct()
    {
        global $DIC;

        $this->dic = $DIC;
    }

    public static function getInstance(): self
    {
        global $DIC;

        return $DIC['lti'];
    }

    /**
     * Switches the page to the LTI view for a user who came from a platform.
     */
    public function init(): void
    {
        if (!$this->isActive()) {
            return;
        }

        $this->dic->language()->loadLanguageModule('lti');
        $this->dic->globalScreen()->tool()->context()->claim()->lti();

        $meta = $this->dic->globalScreen()->layout()->meta();
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
        $user = $this->dic->user();

        return $user instanceof ilObjUser && str_starts_with((string) $user->getAuthMode(), 'lti_');
    }

    public function executeCommand(): void
    {
        if ($this->dic->ctrl()->getCmd() === self::CMD_EXIT) {
            $this->exit();
        }
    }

    /**
     * The object the platform launched, as the link tells or as the last launch of the session left it.
     */
    public function getContextId(): int
    {
        $query = $this->dic->http()->wrapper()->query();
        if ($query->has(self::CONTEXT_PARAM)) {
            $context_id = $query->retrieve(self::CONTEXT_PARAM, $this->dic->refinery()->kindlyTo()->int());
            if ($context_id > 0) {
                return $context_id;
            }
        }

        return (int) (((array) ilSession::get('lti_context_ids'))[0] ?? 0);
    }

    /**
     * @return array what the launch told about the presentation, see ilAuthProviderLTI
     */
    public function getPostData(): array
    {
        return (array) ilSession::get('lti_' . $this->getContextId() . '_post_data');
    }

    public function getTitle(): string
    {
        $title = $this->getPostData()['resource_link_title'] ?? '';

        return $title === '' ? 'LTI' : 'LTI - ' . $title;
    }

    public function getTitleForExitPage(): string
    {
        return $this->dic->language()->txt('lti_exited');
    }

    /**
     * @throws ilCtrlException
     */
    public function getExitLink(): string
    {
        $this->dic->ctrl()->setParameterByClass(self::class, self::CONTEXT_PARAM, $this->getContextId());

        return $this->dic->ctrl()->getLinkTargetByClass([ilLTIRouterGUI::class, self::class], self::CMD_EXIT);
    }

    /**
     * Ends the LTI session and returns to the platform, or tells the user so when the platform gave no
     * return address.
     */
    private function exit(): void
    {
        $context_id = $this->getContextId();
        $return_url = (string) ($this->getPostData()['launch_presentation_return_url'] ?? '');

        ilSession::set('lti_context_ids', array_values(array_filter(
            (array) ilSession::get('lti_context_ids'),
            static fn(mixed $id): bool => (int) $id !== $context_id
        )));
        ilSession::clear('lti_' . $context_id . '_post_data');

        if ($return_url !== '') {
            $this->logout();
            $this->dic->ctrl()->redirectToURL($return_url);
        }

        $this->dic->globalScreen()->tool()->context()->current()->addAdditionalData(self::GS_EXIT_MODE, true);
        $tpl = $this->dic->ui()->mainTemplate();
        $tpl->setContent($this->dic->ui()->renderer()->render(
            $this->dic->ui()->factory()->messageBox()->info($this->dic->language()->txt('lti_exited_info'))
        ));
        $this->logout();
        $tpl->printToStdout();
    }

    /**
     * The ILIAS session ends with the last LTI session in it.
     */
    private function logout(): void
    {
        if ((array) ilSession::get('lti_context_ids') !== []) {
            return;
        }

        $this->dic['ilAuthSession']->setExpired(true);
        session_destroy();
        ilUtil::setCookie('ilClientId', '');
        ilUtil::setCookie('PHPSESSID', '');
    }
}
