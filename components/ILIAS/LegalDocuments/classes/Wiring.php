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

use Closure;
use Exception;
use ILIAS\Data\Result;
use ILIAS\Data\Result\Error;
use ILIAS\Data\Result\Ok;
use ILIAS\LegalDocuments\Intercept\ConditionalIntercept;
use ILIAS\LegalDocuments\GotoLink\ConditionalGotoLink;
use ILIAS\LegalDocuments\ConsumerSlots\Agreement;
use ILIAS\LegalDocuments\ConsumerSlots\SelfRegistration;
use ILIAS\LegalDocuments\ConsumerSlots\WithdrawProcess;
use ILIAS\LegalDocuments\ConsumerSlots\PublicApi;
use ILIAS\LegalDocuments\Provide\Document;
use ILIAS\LegalDocuments\Provide\History;
use ILIAS\LegalDocuments\Value\Target;
use ILIAS\LegalDocuments\Repository\DatabaseDocumentRepository as DocumentRepository;
use ILIAS\LegalDocuments\Repository\DatabaseHistoryRepository as HistoryRepository;
use ILIAS\Refinery\Constraint;
use ILIAS\UI\Component\MainControls\Footer;
use ilSession;
use ilDashboardGUI;
use ilPersonalProfileGUI;
use ilLegalDocumentsWithdrawalGUI;
use ilLegalDocumentsAgreementGUI;
use ilStartUpGUI;

class Wiring implements UseSlot
{
    private readonly Map $map;

    public function __construct(private readonly SlotConstructor $slot, Map $map = null)
    {
        $this->map = $map ?? new Map();
    }

    public function afterLogin(callable $after_login): self
    {
        return $this->addTo('after-login', Closure::fromCallable($after_login));
    }

    public function showInFooter(callable $show): self
    {
        return $this->addTo('footer', $this->slot->id(), Closure::fromCallable($show));
    }

    public function canWithdraw(WithdrawProcess $withdraw_process): self
    {
        $withdraw = $this->protect($withdraw_process->showWithdraw(...), $withdraw_process->isOnGoing(...));

        return $this->addTo('withdraw', $this->slot->id(), $withdraw)
                    ->addTo('logout', $this->slot->id(), $withdraw_process->withdrawalRequested(...))
                    ->addTo('intercept', new ConditionalIntercept($withdraw_process->isOnGoing(...), $this->slot->id(), new Target($this->path(ilLegalDocumentsWithdrawalGUI::class))))
                    ->addTo('logout-text', $this->slot->id(), $withdraw_process->showValidatePasswordMessage(...))
                    ->addTo('show-on-login-page', $this->slot->withdrawalFinished($withdraw_process->withdrawalFinished(...)));
    }

    public function showOnLoginPage(callable $show): self
    {
        return $this->addTo('show-on-login-page', $this->slot->id(), Closure::fromCallable($show));
    }

    public function hasPublicPage(callable $public_page): self
    {
        return $this->addTo('public-page', $this->slot->id(), fn(...$args) => new Ok($public_page(...$args)));
    }

    public function hasAgreement(Agreement $on_login, ?string $goto_name = null): self
    {
        $public_target = new Target(ilStartUpGUI::class, 'showLegalDocuments');
        $agreement_target = new Target($this->path(ilLegalDocumentsAgreementGUI::class));


        $wiring = $this->addTo('public-page', $this->slot->id(), fn(...$args) => new Ok($on_login->showAgreement(...$args)))
                       ->addTo('agreement-form', $this->slot->id(), $this->protect($on_login->showAgreementForm(...), $on_login->needsToAgree(...)))
                       ->addTo('intercept', new ConditionalIntercept($on_login->needsToAgree(...), $this->slot->id(), $agreement_target));

        return null === $goto_name ?
                    $wiring :
                    $wiring->addTo('goto', new ConditionalGotoLink($goto_name, fn() => $on_login->needsToAgree() ? $public_target : $agreement_target));
    }

    public function hasHistory(): self
    {
        $document = $this->map->value()['document'][$this->slot->id()] ?? $this->error('Cannot have a history without documents.');
        return $this->addTo('history', $this->slot->id(), $this->slot->history($document));
    }

    public function onSelfRegistration(SelfRegistration $self_registration): self
    {
        return $this->addTo('self-registration', $self_registration);
    }

    public function hasOnlineStatusFilter(callable $only_visible_users): self
    {
        return $this->addTo('filter-online-users', $only_visible_users);
    }

    public function canReadInternalMails(Constraint $constraint): self
    {
        return $this->addTo('constrain-internal-mail', $constraint);
    }

    public function canUseSoapApi(Constraint $constraint): self
    {
        return $this->addTo('use-soap-api', $constraint);
    }

    public function hasDocuments(array $content_as_component = [], ?SelectionMap $available_conditions = null): self
    {
        $available_conditions ??= new SelectionMap();
        $repository = $this->slot->documentRepository();
        $document = $this->slot->document($this->slot->readOnlyDocuments($repository), $available_conditions, $content_as_component);

        return $this->addTo('document', $this->slot->id(), $document)
                    ->addTo('writable-document', $this->slot->id(), $this->slot->document($repository, $available_conditions, $content_as_component));
    }

    public function hasUserManagementFields(callable $field_value): self
    {
        return $this->addTo('user-management-fields', $this->slot->id(), $field_value);
    }

    public function hasPublicApi(PublicApi $api): self
    {
        return $this->addTo('public-api', $this->slot->id(), $api);
    }

    public function map(): Map
    {
        return $this->map;
    }

    private function error($message): void
    {
        throw new Exception($message);
    }

    private function addTo(string $name, $id_or_value, $value = null)
    {
        $map = $this->map;
        if ($value !== null) {
            if ($this->map->has($name, $id_or_value)) {
                throw new Exception('Duplicated entry. Key ' . $id_or_value . ' already exists for ' . $name);
            }
            $map = $map->set($name, $id_or_value, $value);
        } else {
            $map = $map->add($name, $id_or_value);
        }

        return new self($this->slot, $map);
    }

    /**
     * @param Closure(A ...): B $to_be_protected
     * @param Closure(): bool $protector
     * @return Closure(A ...): Result<B>
     */
    private function protect(Closure $to_be_protected, Closure $protector): Closure
    {
        return static fn(...$args): Result => $protector() ? new Ok($to_be_protected(...$args)) : new Error('Not available.');
    }

    private function path(string $class): array
    {
        return [ilDashboardGUI::class, ilPersonalProfileGUI::class, $class];
    }
}
