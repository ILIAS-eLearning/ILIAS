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

namespace ILIAS\Questions\AnswerForm\Migration;

trait SanitizeLegacyText
{
    private ?MigrationPurifier $purifiery = null;
    private readonly bool $rte_used;

    private function sanitizeLegacyText(
        \ilDBInterface $db,
        string $text,
        bool $ilias_page_editor_text
    ): string {
        if ($this->purifiery === null) {
            $this->purifiery = new MigrationPurifier($db);
            $this->rte_used = $this->fetchIsRteUsedFromDb($db);
        }
        $cleaned_text = $this->purifiery->purify($text);

        if ($ilias_page_editor_text || !$this->rte_used) {
            $cleaned_text = nl2br($cleaned_text);
        }

        return \ilLegacyFormElementsUtil::prepareTextareaOutput(
            $cleaned_text,
            true
        );
    }

    private function fetchIsRteUsedFromDb(
        \ilDBInterface $db
    ): bool {
        return $db->fetchObject(
            $db->query(
                'SELECT value FROM settings' . PHP_EOL
                . 'WHERE module = "advanced_editing"' . PHP_EOL
                . 'AND keyword = "advanced_editing_javascript_editor"'
            )
        )?->value === 'tinymce';
    }
}
