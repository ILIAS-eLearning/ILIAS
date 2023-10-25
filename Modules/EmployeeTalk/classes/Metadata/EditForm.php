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

namespace ILIAS\EmployeeTalk\Metadata;

class EditForm implements EditFormInterface
{
    protected \ilPropertyFormGUI $form;
    protected \ilAdvancedMDRecordGUI $md;
    protected bool $disabled;

    public function __construct(
        \ilAdvancedMDRecordGUI $md,
        bool $disabled,
        string $form_action,
        string $submit_command,
        string $submit_label
    ) {
        $this->md = $md;
        $this->disabled = $disabled;
        $this->form = $this->initForm(
            $form_action,
            $submit_command,
            $submit_label
        );
    }

    protected function initForm(
        string $form_action,
        string $submit_command,
        string $submit_label
    ): \ilPropertyFormGUI {
        $form = new \ilPropertyFormGUI();
        $form->setFormAction($form_action);

        $this->md->setPropertyForm($form);
        $this->md->parse();

        if (!$this->disabled) {
            $form->addCommandButton($submit_command, $submit_label);
            return $form;
        }

        // this is necessary to disable the md fields
        foreach ($form->getInputItemsRecursive() as $item) {
            if ($item instanceof \ilCombinationInputGUI) {
                $item->__call('setValue', ['']);
                $item->__call('setDisabled', [true]);
            }
            if (method_exists($item, 'setDisabled')) {
                /** @var $item \ilFormPropertyGUI */
                $item->setDisabled(true);
            }
        }

        return $form;
    }

    public function importFromPostAndValidate(): bool
    {
        /**
         * checkInput must be called before importEditFormPostValues,
         * and ImportEditFormPostValues must always be called, so that
         * input persists through the error handling.
         */
        $valid = $this->form->checkInput();
        $post_imported = $this->md->importEditFormPostValues();

        return $post_imported && $valid && !$this->disabled;
    }

    public function updateMetadata(): void
    {
        $this->md->writeEditForm();
    }

    public function render(): string
    {
        return $this->form->getHTML();
    }
}
