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

use ILIAS\LegalDocuments\ConsumerToolbox\Mail;
use ILIAS\LegalDocuments\ConsumerSlots\WithdrawProcess as WithdrawProcessInterface;
use ILIAS\LegalDocuments\Provide;
use ILIAS\LegalDocuments\PageFragment;
use ILIAS\LegalDocuments\PageFragment\PageContent;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\ConsumerToolbox\User;
use ilSession;
use ilInitialisation;
use ILIAS\UI\Component\Component;
use ILIAS\LegalDocuments\ConsumerToolbox\Routing;
use Closure;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\LegalDocuments\ConsumerToolbox\Settings;
use ilStartUpGUI;

final class WithdrawProcess implements WithdrawProcessInterface
{
    /**
     * @param Closure(string, Closure(Refinery): Transformation): mixed $query_parameter
     * @param Closure(): void $after_user_withdrawal
     */
    public function __construct(
        private readonly User $user,
        private readonly UI $ui,
        private readonly Routing $routing,
        private readonly Settings $settings,
        private readonly Closure $query_parameter,
        private readonly Provide $legal_documents,
        private readonly Closure $after_user_withdrawal
    ) {
    }

    public function showValidatePasswordMessage(): array
    {
        $status = $this->query('withdrawal_relogin_content');
        $lng = 'withdraw_consent_description_' . ($status === 'external' ? 'external' : 'internal');

        return [
            $this->ui->create()->divider()->horizontal(),
            $this->ui->create()->legacy($this->ui->txt($lng))
        ];
    }

    public function isOnGoing(): bool
    {
        return $this->user->withdrawalRequested()->value();
    }

    public function withdrawalRequested(): void
    {
        if ($this->user->cannotAgree() || $this->user->neverAgreed()) {
            return;
        }

        $this->user->withdrawalRequested()->update(true);

        $external = $this->user->isExternalAccount();

        $this->routing->ctrl()->setParameterByClass(ilStartUpGUI::class, 'withdrawal_relogin_content', $external ? 'external' : 'internal');
    }

    public function withdrawalFinished(): void
    {
        $type = $this->query('tos_withdrawal_type');

        $this->ui->mainTemplate()->setOnScreenMessage('info', match ($type) {
            'delete_user' => $this->ui->txt('withdrawal_complete_deleted'),
            'external' => $this->ui->txt('withdrawal_complete_redirect'),
            null, 'default' => $this->ui->txt('withdrawal_complete'),
        });
    }

    public function showWithdraw(string $gui, string $cmd): PageFragment
    {
        return match ($cmd) {
            'indeed' => $this->withdraw(),
            'cancel' => $this->cancelWithdrawal(),
            '' => $this->showWithdrawConfirmation($gui, $cmd),
        };
    }

    private function withdraw(): void
    {
        $this->user->agreeDate()->update(null);
        $this->user->withdrawalRequested()->update(false);

        $withdrawal_type = 'default';
        if ($this->user->isLDAPUser()) {
            $this->sendMail();
            $withdrawal_type = 'external';
        } elseif ($this->settings->deleteUserOnWithdrawal()->value()) {
            $this->user->raw()->delete();
            $withdrawal_type = 'delete_user';
        }

        ($this->after_user_withdrawal)();

        $this->legal_documents->withdrawal()->finishAndLogout(['tos_withdrawal_type' => $withdrawal_type]);
    }

    private function cancelWithdrawal(): void
    {
        $this->user->withdrawalRequested()->update(false);
        $this->routing->redirectToOriginalTarget();
    }

    private function showWithdrawConfirmation(string $gui, string $cmd): PageFragment
    {
        $title = $this->ui->txt('refuse_acceptance');

        if ($this->user->isLDAPUser()) {
            return new PageContent($title, [
                $this->ui->create()->panel()->standard($this->ui->txt('withdraw_usr_agreement'), [
                    $this->confirmation($gui),
                    $this->ui->create()->divider()->horizontal(),
                    $this->ui->create()->legacy(nl2br($this->user->format($this->ui->txt('withdrawal_mail_info') . $this->ui->txt('withdrawal_mail_text'))))
                ])
            ]);
        }

        $deletion = $this->settings->deleteUserOnWithdrawal()->value() ? '_deletion' : '';
        return new PageContent($title, [$this->confirmation($gui, $deletion)]);
    }

    private function confirmation(string $gui, string $add_to_question = ''): Component
    {
        $lng_suffix = $this->user->neverAgreed() ? '_no_consent_yet' : '';
        $question = 'withdrawal_sure_account' . $add_to_question . $lng_suffix;

        return $this->ui->create()->messageBox()->confirmation($this->ui->txt($question))->withButtons([
            $this->ui->create()->button()->standard(
                $this->ui->txt('confirm'),
                $this->routing->ctrl()->getLinkTargetByClass($gui, 'indeed')
            ),
            $this->ui->create()->button()->standard(
                $this->ui->txt('cancel'),
                $this->routing->ctrl()->getLinkTargetByClass($gui, 'cancel')
            ),
        ]);
    }

    private function sendMail(): void
    {
        $mail = new Mail();
        $mail->setRecipients([$this->settings->adminEmail()->value()]);
        $mail->sendGeneric($this->ui->txt('withdrawal_mail_subject'), $this->user->format($this->ui->txt('withdrawal_mail_text')));
    }

    private function refuseContent($components): PageFragment
    {
        return new PageContent($this->ui->txt('refuse_tos_acceptance'), $components);
    }

    private function query(string $query_parameter): ?string
    {
        return ($this->query_parameter)($query_parameter, fn(Refinery $r) => $r->byTrying([
            $r->null(),
            $r->to()->string(),
        ]));
    }
}
