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

namespace ILIAS\LegalDocuments;

use ILIAS\LegalDocuments\PageFragment;
use ILIAS\DI\Container;
use ILIAS\LegalDocuments\ConsumerSlots\SelfRegistration;
use ILIAS\LegalDocuments\ConsumerSlots\SelfRegistration\Bundle;
use Closure;
use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\Refinery\Transformation;
use ilStartUpGUI;
use ilObjUser;
use Exception;
use ILIAS\Data\Factory as DataFactory;
use ilLegalDocumentsAgreementGUI;
use ilInitialisation;
use ILIAS\LegalDocuments\ConsumerToolbox\SelectSetting;
use ILIAS\LegalDocuments\ConsumerToolbox\KeyValueStore\SessionStore;
use ILIAS\LegalDocuments\ConsumerToolbox\Marshal;
use ILIAS\LegalDocuments\ConsumerToolbox\Routing;

class Conductor
{
    private readonly Internal $internal;
    private readonly Routing $routing;

    public function __construct(private readonly Container $container, ?Internal $internal = null, Routing $routing = null)
    {
        $this->internal = $internal ?? $this->createInternal();
        $this->routing = $routing ?? new Routing(
            $this->container->ctrl(),
            new SelectSetting(new SessionStore(), new Marshal($this->container->refinery())),
            ilInitialisation::redirectToStartingPage(...),
            ilStartUpGUI::logoutUrl(...)
        );
    }

    public function provide(string $id): Provide
    {
        return new Provide($id, $this->internal, $this->container);
    }

    public function onLogout(string $gui): void
    {
        try {
            $id = $this->container->http()->wrapper()->query()->retrieve('withdraw_consent', $this->container->refinery()->to()->string());
        } catch (Exception) {
            return;
        }

        $logout = $this->internal->get('logout', $id);
        if (null !== $logout) {
            $this->container->ctrl()->setParameterByClass($gui, 'withdraw_from', $id);
            $logout();
        }
    }

    public function loginPageHTML(string $id): string
    {
        $create = $this->internal->get('show-on-login-page', $id);
        if (!$create) {
            return '';
        }
        return $this->container->ui()->renderer()->render($create());
    }

    public function logoutText(): string
    {
        try {
            $id = $this->container->http()->wrapper()->query()->retrieve('withdraw_from', $this->container->refinery()->to()->string());
        } catch (Exception) {
            return '';
        }

        $logout_text = $this->internal->get('logout-text', $id);

        return null === $logout_text ? '' : $this->container->ui()->renderer()->render($logout_text());
    }

    public function modifyFooter(Footer $footer): Footer
    {
        return array_reduce($this->internal->all('footer'), fn($footer, Closure $proc) => $proc($footer), $footer);
    }

    public function agree(string $gui, string $cmd): void
    {
        $this->setMainTemplateContent($this->agreeContent($gui, $cmd));
    }

    public function agreeContent(string $gui, string $cmd): string
    {
        $key = ilLegalDocumentsAgreementGUI::class === $gui ? 'agreement-form' : 'public-page';
        $result = $this->byQueryParams($gui, $cmd, $key)->then($this->renderPageFragment($gui, $cmd));

        if (!$result->isOk() && $result->error() === 'Not available.') {
            $this->routing->redirectToOriginalTarget();
        }

        return $result->value();
    }

    public function withdraw(string $gui, string $cmd): void
    {
        $this->setMainTemplateContent($this->byQueryParams($gui, $cmd, 'withdraw')->then($this->renderPageFragment($gui, $cmd))->value());
    }

    /**
     * @param list<int> $users
     * @return list<int>
     */
    public function usersWithHiddenOnlineStatus(array $users): array
    {
        $filters = $this->internal->all('filter-online-users');

        $visible_users = array_reduce(
            $filters,
            fn($users, $only_visible_users) => $only_visible_users($users),
            $users,
        );

        return array_values(array_diff($users, $visible_users));
    }

    public function userCanReadInternalMail(): Transformation
    {
        return $this->container->refinery()->in()->series(array_values($this->internal->all('constrain-internal-mail')));
    }

    public function canUseSoapApi(): Transformation
    {
        return $this->container->refinery()->in()->series(array_values($this->internal->all('use-soap-api')));
    }

    public function afterLogin(): void
    {
        array_map(fn($proc) => $proc(), $this->internal->all('after-login'));
    }

    public function findGotoLink(string $goto_target): Result
    {
        return $this->find(
            fn($goto_link) => $goto_link->name() === $goto_target,
            $this->internal->all('goto')
        )->map(fn($goto_link) => $goto_link->target());
    }

    public function intercepting(): array
    {
        return $this->internal->all('intercept');
    }

    public function selfRegistration(): SelfRegistration
    {
        return new Bundle($this->internal->all('self-registration'));
    }

    public function userManagementFields(ilObjUser $user): array
    {
        return array_reduce(
            $this->internal->all('user-management-fields'),
            static fn(array $prev, callable $f): array => [...$prev, ...$f($user)],
            []
        );
    }

    /**
     * @template A
     *
     * @param Closure(A): bool $predicate
     * @param list<A> $array
     *
     * @return Result<A>
     */
    private function find(Closure $predicate, array $array): Result
    {
        foreach ($array as $x) {
            if ($predicate($x)) {
                return new Ok($x);
            }
        }

        return new Error('Not found.');
    }

    /**
     * @template A
     * @param Closure(A): bool $predicate
     * @param list<A> $array
     */
    private function any(Closure $predicate, array $array): bool
    {
        return $this->find($predicate, $array)->isOk();
    }

    /**
     * @template A
     * @param Closure(A): bool $predicate
     * @param list<A> $array
     */
    private function all(Closure $predicate, array $array): bool
    {
        return !$this->any(static fn($x) => !$predicate($x), $array);
    }

    private function byQueryParams(string $gui, string $cmd, string $key): Result
    {
        try {
            $id = $this->container->http()->wrapper()->query()->retrieve('id', $this->container->refinery()->to()->string());
        } catch (Exception) {
            return new Error('No provider ID given.');
        }

        $this->container->ctrl()->setParameterByClass($gui, 'id', $id);

        $value = $this->internal->get($key, $id);

        if (null === $value) {
            return new Error('Field not defined.');
        }

        return new Ok($value);
    }

    /**
     * @return Closure(Closure(string, string): Result<PageFragment>): string
     */
    private function renderPageFragment(string $gui, string $cmd): Closure
    {
        return fn(Closure $proc) => $proc($gui, $cmd)->map(fn($fragment) => $fragment->render(
            $this->container->ui()->mainTemplate(),
            $this->container->ui()->renderer()
        ));
    }

    private function setMainTemplateContent(string $content): void
    {
        $this->container->ui()->mainTemplate()->setContent($content);
    }

    private function createInternal(): Internal
    {
        $clock = (new DataFactory())->clock()->system();
        $action = new UserAction($this->container->user(), $clock);

        return new Internal($this->provide(...), fn(string $id) => new Wiring(new SlotConstructor(
            $id,
            $this->container,
            $action
        )));
    }
}
