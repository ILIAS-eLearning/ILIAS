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

namespace ILIAS\GlobalScreen\UI\Footer\Translation;

class TranslationsRepositoryDB implements TranslationsRepository
{
    /**
     * @var string
     */
    private const TABLE_NAME = 'gs_item_translation';
    private string $default_language;

    public function __construct(
        private readonly \ilDBInterface $db
    ) {
        $this->default_language = $this->db->queryF(
            'SELECT value FROM settings WHERE keyword = %s AND module = %s',
            ['text', 'text'],
            ['language', 'common']
        )->fetchAssoc()['value'] ?? 'en';
    }

    public function store(Translations $translations): Translations
    {
        $this->db->manipulateF(
            'UPDATE ' . self::TABLE_NAME . ' SET status = 0 WHERE id = %s',
            ['text'],
            [$translations->getId()]
        );

        foreach ($translations->get() as $translation) {
            $this->db->manipulateF(
                'REPLACE INTO ' . self::TABLE_NAME . ' (id, language_code, translation, status) VALUES (%s, %s, %s, 1)',
                ['text', 'text', 'text'],
                [$translation->getId(), $translation->getLanguageCode(), $translation->getTranslation()]
            );
        }

        // remove empty translations
        $this->db->manipulateF(
            'DELETE FROM ' . self::TABLE_NAME . ' WHERE id = %s AND translation = ""',
            ['text'],
            [$translations->getId()]
        );

        return $translations;
    }

    public function get(TranslatableItem $item): Translations
    {
        $r = $this->db->queryF(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE id = %s AND status = 1',
            ['text'],
            [$item->getId()]
        );
        $translations = [];

        while ($row = $this->db->fetchAssoc($r)) {
            if (empty($row['translation'])) {
                continue;
            }

            $translations[] = new TranslationDTO(
                $row['id'],
                $row['language_code'],
                $row['translation']
            );
        }

        return new Translations($this->default_language, $item, ...$translations);
    }

    public function blank(TranslatableItem $item, string $language_code, string $translation): Translation
    {
        return new TranslationDTO(
            $item->getId(),
            $language_code,
            $translation
        );
    }

    public function reset(): void
    {
        $this->db->manipulate('TRUNCATE TABLE ' . self::TABLE_NAME);
    }

}
