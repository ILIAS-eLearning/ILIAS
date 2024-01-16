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

namespace ILIAS\LegalDocuments\ConsumerToolbox\ConsumerSlots;

use ILIAS\LegalDocuments\ConsumerSlots\Agreement as AgreementInterface;
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\LegalDocuments\Provide;
use ILIAS\UI\Component\Component;
use ILIAS\LegalDocuments\PageFragment;
use ILIAS\LegalDocuments\PageFragment\PageContent;
use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ilLegacyFormElementsUtil;
use ilSystemSupportContacts;
use Psr\Http\Message\RequestInterface;
use ilSession;
use ILIAS\LegalDocuments\ConsumerToolbox\Settings;
use ILIAS\LegalDocuments\ConsumerToolbox\User;
use ILIAS\LegalDocuments\ConsumerToolbox\Marshal;
use ILIAS\LegalDocuments\ConsumerToolbox\Setting;
use ILIAS\LegalDocuments\ConsumerToolbox\Routing;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use Closure;

final class Agreement implements AgreementInterface
{
    /**
     * @param Closure(Form, Closure(array): void): Form $with_request
     */
    public function __construct(
        private readonly User $user,
        private readonly Settings $settings,
        private readonly UI $ui,
        private readonly Routing $routing,
        private readonly Closure $with_request
    ) {
    }

    public function showAgreement(string $gui, string $cmd): PageFragment
    {
        return (new PageContent($this->ui->txt('usr_agreement'), [$this->showDocument()]));
    }

    public function showAgreementForm(string $gui, string $cmd): PageFragment
    {
        $form = $this->user->matchingDocument()->isOk() ?
              $this->agreementForm($gui, $cmd) :
              $this->ui->create()->divider()->horizontal();

        return (new PageContent($this->ui->txt('accept_usr_agreement'), [
            $this->showDocument(),
            $form,
            $this->logoutLink(),
        ]))->withOnScreenMessage('info', $this->ui->txt('accept_usr_agreement_intro'));
    }

    public function needsToAgree(): bool
    {
        return !$this->user->cannotAgree()
            && ($this->user->neverAgreed() || $this->user->needsToAcceptNewDocument());
    }

    private function showDocument(): Component
    {
        return $this->user
            ->matchingDocument()
            ->map(fn(Document $document) => $this->ui->create()->legacy($document->content()->value()))
            ->except(fn() => new Ok($this->ui->create()->legacy(sprintf(
                $this->ui->txt('no_agreement_description'),
                'mailto:' . ilLegacyFormElementsUtil::prepareFormOutput(ilSystemSupportContacts::getMailsToAddress())
            ))))->value();
    }

    private function agreementForm(string $gui, string $cmd): Component
    {
        $url = $this->routing->ctrl()->getFormActionByClass($gui, $cmd);
        $form = $this->ui->create()->input()->container()->form()->standard($url, [
            'accept?' => $this->ui->create()->input()->field()->radio($this->ui->txt('accept_usr_agreement'))
                                  ->withOption('yes', $this->ui->txt('accept_usr_agreement_btn'))
                                  ->withOption('no', $this->ui->txt('deny_usr_agreement_btn'))
        ]);

        return ($this->with_request)($form, function (array $data) {
            $accept = $data['accept?'] ?? '';
            if ($accept === 'no') {
                $this->routing->ctrl()->redirectToURL($this->routing->logoutUrl());
            } elseif ($accept === 'yes') {
                $this->user->acceptMatchingDocument();
                $this->routing->redirectToOriginalTarget();
            }
        });
    }

    private function logoutLink(): Component
    {
        return $this->ui->create()->button()->standard($this->ui->txt('logout'), $this->routing->logoutUrl());
    }
}
