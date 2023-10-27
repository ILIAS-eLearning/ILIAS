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

namespace ILIAS\LegalDocuments\Legacy;

use Closure;
use ilConfirmationGUI;
use ilLanguage;

class Confirmation
{
    /** @var Closure(): ilConfirmationGUI */
    private readonly Closure $create;

    /**
     * @param Closure(): ilConfirmationGUI $create
     */
    public function __construct(private readonly ilLanguage $language, Closure $create = null)
    {
        $this->create = $create ?? static fn() => new ilConfirmationGUI();
    }

    /**
     * @param array<string, string> $items
     */
    public function render(string $link, string $command, string $cancel_command, string $message, array $items = []): string
    {
        $confirmation = ($this->create)();
        $confirmation->setFormAction($link);
        $confirmation->setConfirm($this->language->txt('confirm'), $command);
        $confirmation->setCancel($this->language->txt('cancel'), $cancel_command);
        $confirmation->setHeaderText($message);
        foreach ($items as $value => $label) {
            $value = is_int($value) ? (string) $value : $value;
            $confirmation->addItem('ids[]', $value, $label);
        }

        return $confirmation->getHTML();
    }
}
