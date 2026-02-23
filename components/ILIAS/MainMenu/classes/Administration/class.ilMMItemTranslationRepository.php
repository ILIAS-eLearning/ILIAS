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

use ILIAS\GlobalScreen\GUI\I18n\MultiLanguage\TranslationsRepository;
use ILIAS\GlobalScreen\GUI\I18n\MultiLanguage\Translation;
use ILIAS\GlobalScreen\GUI\I18n\MultiLanguage\Translations;
use ILIAS\GlobalScreen\GUI\I18n\MultiLanguage\TranslatableItem;
use ILIAS\GlobalScreen\GUI\I18n\MultiLanguage\TranslationDTO;
use ILIAS\GlobalScreen\GUI\Pons;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilMMItemTranslationRepository implements TranslationsRepository
{
    /**
     * @var string
     */
    private const TABLE_NAME = 'il_mm_translation';

    public function __construct(private ilDBInterface $db)
    {
    }

    public function store(Translations $translations): Translations
    {
        foreach ($translations->get() as $translation) {
            $this->storeSingleTranslation(
                $translations->getId(),
                $translation->getLanguageCode(),
                $translation->getTranslation()
            );
        }

        return $translations;
    }

    private function has(string $identification, string $language_code): bool
    {
        $language_identification = "{$identification}|$language_code";

        return $this->db->queryF(
            'SELECT id FROM ' . self::TABLE_NAME . ' WHERE id = %s',
            ['text'],
            [$language_identification]
        )->numRows() > 0;
    }

    private function storeSingleTranslation(
        string $identification,
        string $language_code,
        string $translation
    ): void {
        $language_identification = "{$identification}|$language_code";
        if ($this->has($identification, $language_code)) {
            $this->db->update(
                self::TABLE_NAME,
                [
                    'translation' => ['text', $translation],
                ],
                [
                    'id' => ['text', $language_identification]
                ]
            );
        } else {
            $this->db->insert(
                self::TABLE_NAME,
                [
                    'id' => ['text', $language_identification],
                    'identification' => ['text', $identification],
                    'translation' => ['text', $translation],
                    'language_key' => ['text', $language_code],
                ]
            );
        }
    }

    public function get(TranslatableItem $item): Translations
    {
        $identification = $item->getId();
        $r = $this->db->queryF(
            'SELECT * FROM ' . self::TABLE_NAME . ' WHERE identification = %s',
            ['text'],
            [$identification]
        );
        $translations = [];
        while ($row = $this->db->fetchAssoc($r)) {
            if (empty($row['translation'])) {
                continue;
            }

            $translations[] = new TranslationDTO(
                $row['identification'],
                $row['language_key'],
                $row['translation']
            );
        }

        return new Translations('en', $item, ...$translations);
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

    public function retrieveCurrent(Pons $pons): TranslatableItem
    {
        $pons->tabs()->structure()->current();

        $id = $pons->in()->getFirstFromRequest('top_id');
        return (new ilMMItemRepository())->getItemFacadeForIdentificationString($id);
    }

}
