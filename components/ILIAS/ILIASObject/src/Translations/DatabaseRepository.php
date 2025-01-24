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

namespace ILIAS\ILIASObject\Translations;

class DatabaseRepository
{
    private const OBJECT_TRANSLATIONS_TABLE = 'object_translations';
    private const COPAGE_TRANSLATIONS_TABLE = 'obj_content_master_lng';

    public function __construct(
        private readonly \ilDBInterface $db
    ) {
    }

    public function getFor(int $obj_id): Translation
    {
        $master_lang = $this->db->fetchAssoc(
            $this->db->query(
                'SELECT obj_id, master_lang, fallback_lang' . PHP_EOL
                . 'FROM ' . self::COPAGE_TRANSLATIONS_TABLE . PHP_EOL
                . 'WHERE obj_id = ' . $this->db->quote($obj_id, 'integer') . PHP_EOL
            )
        );

        $result = $this->db->query(
            'SELECT title, description, lang_code, lang_default' . PHP_EOL
            . 'FROM ' . self::OBJECT_TRANSLATIONS_TABLE . PHP_EOL
            . 'WHERE obj_id = ' . $this->db->quote($obj_id, 'integer') . PHP_EOL
        );

        $languages = [];
        $object_translation_default_language = '';
        while ($row = $this->db->fetchAssoc($result)) {
            $languages[$row['lang_code']] = new Language(
                $row['lang_code'],
                $row['title'] ?? '',
                $row['description'] ?? '',
                $row['lang_default'] === 1
            );
            if ($row['lang_default'] === 1) {
                $object_translation_default_language = $row['lang_code'];
            }
        }

        return new Translation(
            $obj_id,
            $master_lang !== null,
            $languages,
            $this->determineDefaultLanguage($object_translation_default_language, $master_lang),
            $master_lang['master_lang'] ?? null
        );
    }

    private function determineDefaultLanguage(
        string $object_translation_default_language,
        ?string $master_lang
    ): string {
        if ($master_lang === null
            || empty($master_lang['fallback_lang'])) {
            return $object_translation_default_language;
        }
        return $master_lang['fallback_lang'];
    }

    public function delete(int $obj_id): void
    {
        $this->db->manipulate(
            'DELETE FROM ' . self::COPAGE_TRANSLATIONS_TABLE . PHP_EOL
            . 'WHERE obj_id = ' . $this->db->quote($obj_id, 'integer')
        );
        $this->db->manipulate(
            'DELETE FROM ' . self::OBJECT_TRANSLATIONS_TABLE . PHP_EOL
            . 'WHERE obj_id = ' . $this->db->quote($obj_id, 'integer')
        );
    }

    public function deactivateContentTranslationFor(int $obj_id): void
    {
        $this->db->manipulate(
            'DELETE FROM ' . self::COPAGE_TRANSLATIONS_TABLE . PHP_EOL
            . 'WHERE obj_id = ' . $this->db->quote($obj_id, 'integer')
        );
    }

    public function store(
        Translation $translation
    ): void {
        $this->delete($translation->getObjId());

        if ($translation->getCOPageTranslationActivated()) {
            $this->storeCOPageTranslation($translation);
        }

        $values = array_reduce(
            $translation->getLanguages(),
            function (string $c, Language $v) use ($translation): string {
                if ($c !== '') {
                    $c .= ',';
                }

                return "{$c}("
                    . $this->db->quote($translation->getObjId(), \ilDBConstants::T_INTEGER) . ','
                    . $this->db->quote($v->getTitle(), \ilDBConstants::T_TEXT) . ','
                    . $this->db->quote($v->getDescription(), \ilDBConstants::T_TEXT) . ','
                    . $this->db->quote($v->getLanguageCode(), \ilDBConstants::T_TEXT) . ','
                    . $this->db->quote($v->isDefault() ? 1 : 0, \ilDBConstants::T_INTEGER)
                    . ")";
            },
            ''
        );

        $this->db->manipulate(
            'INSERT INTO ' . self::OBJECT_TRANSLATIONS_TABLE . PHP_EOL
            . '(obj_id, title, description, lang_code, lang_default)' . PHP_EOL
            . 'VALUES ' . $values
        );
    }

    private function storeCOPageTranslation(
        Translation $translation
    ): void {
        $this->db->insert(
            self::COPAGE_TRANSLATIONS_TABLE,
            [
                'obj_id' => [
                    \ilDBConstants::T_INTEGER,
                    $translation->getObjId()
                ],
                'master_lang' => [
                    \ilDBConstants::T_TEXT,
                    $translation->getMasterLanguage()
                ],
                'fallback_lang' => [
                    \ilDBConstants::T_TEXT,
                    $translation->getMasterLanguage() === $translation->getDefaultLanguage()
                        ? null
                        : $translation->getDefaultLanguage()
                ]
            ]
        );
    }
}
