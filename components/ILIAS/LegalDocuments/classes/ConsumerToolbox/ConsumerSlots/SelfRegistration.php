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

use ILIAS\LegalDocuments\ConsumerSlots\SelfRegistration as SelfRegistrationInterface;
use ILIAS\LegalDocuments\Value\Document;
use ILIAS\LegalDocuments\ConsumerToolbox\User;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ilFormSectionHeaderGUI;
use ilCustomInputGUI;
use ilCheckboxInputGUI;
use ilPropertyFormGUI;
use ilObjUser;
use ILIAS\LegalDocuments\Provide;
use ILIAS\Data\Result\Ok;
use Closure;

final class SelfRegistration implements SelfRegistrationInterface
{
    private readonly Closure $create_input;

    /**
     * @param Closure(ilObjUser $user): User
     * @param Closure(list<Component>|Component): string $render
     * @param Closure(ilObjUser): User $build_user
     * @param Closure(string): object $create_input
     */
    public function __construct(
        private readonly string $id,
        private readonly UI $ui,
        private readonly User $user,
        private readonly Provide $legal_documents,
        private readonly Closure $render,
        private readonly Closure $build_user,
        ?Closure $create_input = null
    ) {
        $this->create_input = $create_input ?? fn(string $class, ...$args) => new $class(...$args);
    }

    public function legacyInputGUIs(): array
    {
        return $this->user->matchingDocument()->map($this->guis(...))->except(fn() => new Ok([]))->value();
    }

    public function saveLegacyForm(ilPropertyFormGUI $form): bool
    {
        if ($this->user->matchingDocument()->isError()) {
            $this->ui->loadLanguageModule($this->id);
            $this->ui->loadLanguageModule('ldoc');
            $this->ui->mainTemplate()->setOnScreenMessage('failure', sprintf(
                $this->ui->txt('account_reg_not_possible'),
                'mailto:' . \ilLegacyFormElementsUtil::prepareFormOutput(\ilSystemSupportContacts::getMailsToAddress())
            ), true);
            return false;
        }
        $input = $form->getItemByPostVar($this->checkboxVariableName());
        if ($input && !$form->getInput($this->checkboxVariableName())) {
            $input->setAlert($this->ui->txt('force_accept_usr_agreement'));
            return false;
        }

        return true;
    }

    public function userCreation(ilObjUser $user): void
    {
        // This will accept the document as the USER and NOT as anonymous. If the document is different thats not handled.
        ($this->build_user)($user)->acceptMatchingDocument();
    }

    private function guis(Document $document): array
    {
        $header = ($this->create_input)(ilFormSectionHeaderGUI::class);
        $header->setTitle($this->ui->txt('usr_agreement'));

        $doc = ($this->create_input)(ilCustomInputGUI::class);
        $document_content = ($this->render)($this->legal_documents->document()->contentAsComponent($document->content()));
        $doc->setHtml(sprintf('<div id="%s_agreement">%s</div>', htmlentities($this->id), $document_content));

        $checkbox = ($this->create_input)(ilCheckboxInputGUI::class, $this->ui->txt('accept_usr_agreement'), $this->checkboxVariableName());
        $checkbox->setRequired(true);
        $checkbox->setValue('1');

        return [$header, $doc, $checkbox];
    }

    private function checkboxVariableName(): string
    {
        return 'accept_' . $this->id;
    }
}
