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

use ILIAS\LegalDocuments\Value\DocumentContent;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\ConsumerToolbox\User;
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\Data\Result\Ok;
use ILIAS\LegalDocuments\Provide;
use ilTemplate;
use Closure;

final class ModifyFooter
{
    /** @var Closure(string): ilTemplate */
    private readonly Closure $create_template;

    /**
     * @param Closure(list<Component>|Component): string $render
     * @param null|Closure(string): ilTemplate $create_template
     */
    public function __construct(
        private readonly UI $ui,
        private readonly User $user,
        private readonly Provide $legal_documents,
        private readonly Closure $render,
        ?Closure $create_template = null
    ) {
        $this->create_template = $create_template ?? fn(string $name) => new ilTemplate($name, true, true, 'components/ILIAS/LegalDocuments');
    }

    public function __invoke(Footer $footer): Footer
    {
        return $this->user->acceptedDocument()->map(
            $this->renderModal($footer)
        )->except(
            fn() => new Ok($footer)
        )->value();
    }

    public function renderModal(Footer $footer): Closure
    {
        return fn(DocumentContent $content): Footer => $footer->withAdditionalModalAndTrigger($this->ui->create()->modal()->roundtrip($content->title(), [
            $this->ui->create()->legacy($this->ui->txt('usr_agreement_footer_intro')),
            $this->ui->create()->divider()->horizontal(),
            $this->legal_documents->document()->contentAsComponent($content),
            $this->ui->create()->divider()->horizontal(),
            $this->withdrawalButton(),
        ]), $this->ui->create()->button()->shy($this->ui->txt('usr_agreement'), ''));
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

        return $this->ui->create()->legacy($template->get());
    }
}
