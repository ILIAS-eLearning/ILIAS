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

use ilLink;
use ILIAS\LegalDocuments\Value\DocumentContent;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\ConsumerToolbox\User;
use ILIAS\Data\Result\Ok;
use ILIAS\LegalDocuments\Provide;
use ilTemplate;
use Closure;
use ILIAS\UI\Component\Modal\Modal;

final class ModifyFooter
{
    /**
     * @param Closure(list<Component>|Component): string $render
     * @param Closure(string): ilTemplate $create_template
     */
    public function __construct(
        private readonly UI $ui,
        private readonly User $user,
        private readonly Provide $legal_documents,
        private readonly Closure $render,
        private readonly Closure $create_template,
        private readonly ?Closure $goto_link,
    ) {
    }

    public function __invoke(Closure $footer): Closure
    {
        return $this->user->acceptedVersion()->map(
            fn($document) => $this->footer($footer, $this->renderModal($document))
        )->except(
            fn() => new Ok(
                !$this->goto_link || $this->user->isLoggedIn() ?
                    $footer :
                    $this->footer($footer, ($this->goto_link)())
            )
        )->value();
    }

    public function renderModal(DocumentContent $content): Modal
    {
        return $this->ui->create()->modal()->roundtrip($content->title(), [
            $this->ui->create()->legacy()->content($this->ui->txt('usr_agreement_footer_intro')),
            $this->ui->create()->divider()->horizontal(),
            $this->legal_documents->document()->contentAsComponent($content),
            $this->ui->create()->divider()->horizontal(),
            $this->withdrawalButton(),
        ]);
    }

    public function withdrawalButton(): Component
    {
        $template = ($this->create_template)('withdrawal-section.html');
        $template->setVariable('TXT_WITHDRAWAL_HEADLINE', $this->ui->txt('withdraw_consent_header'));
        $template->setVariable('TXT_WITHDRAWAL', $this->ui->txt('withdraw_consent_description'));
        $template->setVariable(
            'BTN_WITHDRAWAL',
            ($this->render)(
                $this->ui->create()->button()->standard($this->ui->txt('withdraw_consent'), $this->legal_documents->withdrawal()->beginProcessURL())
            )
        );

        return $this->ui->create()->legacy()->content($template->get());
    }

    /**
     * @param URI|Modal $value
     */
    private function footer(Closure $footer, object $value): Closure
    {
        return $footer($this->legal_documents->id(), $this->ui->txt('usr_agreement'), $value);
    }
}
