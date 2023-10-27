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

use ILIAS\LegalDocuments\Provide;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\UI\Component\Component;

final class ShowOnLoginPage
{
    public function __construct(
        private readonly Provide $legal_documents,
        private readonly UI $ui
    ) {
    }

    /**
     * @return list<Component>
     */
    public function __invoke(): array
    {
        if ($this->legal_documents->document()->repository()->countAll() === 0) {
            return [];
        }

        return [$this->ui->create()->link()->standard($this->ui->txt('usr_agreement'), $this->legal_documents->publicPage()->url())];
    }
}
