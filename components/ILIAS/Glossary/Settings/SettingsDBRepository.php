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

namespace ILIAS\Glossary\Settings;

use ilDBInterface;
use ILIAS\Glossary\InternalDataService;

class SettingsDBRepository
{
    public function __construct(
        protected ilDBInterface $db,
        protected InternalDataService $data
    ) {
    }

    public function update(Settings $settings): void
    {
        $this->db->update(
            'glossary',
            [
                'is_online' => ['text', $this->boolToText($settings->getOnline())],
                'virtual' => ['text', $settings->getVirtualMode()],
                'glo_menu_active' => ['text', $this->boolToText($settings->getActiveGlossaryMenu())],
                'pres_mode' => ['text', $settings->getPresentationMode()],
                'show_tax' => ['integer', $settings->getShowTaxonomy()],
                'snippet_length' => ['integer', $settings->getSnippetLength()],
                'flash_active' => ['text', $this->boolToText($settings->getActiveFlashcards())],
                'flash_mode' => ['text', $settings->getFlashcardsMode()]
            ],
            [
                'id' => ['integer', $settings->getId()]
            ]
        );
    }

    public function getById(int $id): ?Settings
    {
        $set = $this->db->queryF(
            'SELECT * FROM glossary WHERE id = %s',
            ['integer'],
            [$id]
        );

        $record = $this->db->fetchAssoc($set);
        if ($record) {
            return $this->getSettingsFromRecord($record);
        }

        return null;
    }

    protected function getSettingsFromRecord(array $record): Settings
    {
        return $this->data->settings(
            (int) $record['id'],
            $this->textToBool($record['is_online']),
            (string) $record['virtual'],
            $this->textToBool($record['glo_menu_active']),
            (string) $record['pres_mode'],
            (int) $record['show_tax'],
            (int) $record['snippet_length'],
            $this->textToBool($record['flash_active']),
            (string) $record['flash_mode']
        );
    }

    protected function boolToText(bool $value): string
    {
        return $value ? 'y' : 'n';
    }

    protected function textToBool(string $value): bool
    {
        return $value === 'y';
    }
}
