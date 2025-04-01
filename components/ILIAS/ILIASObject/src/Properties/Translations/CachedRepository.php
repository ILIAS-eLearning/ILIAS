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

namespace ILIAS\ILIASObject\Properties\Translations;

class CachedRepository
{
    private const OBJECT_TRANSLATIONS_TABLE = 'object_translation';
    private const COPAGE_TRANSLATIONS_TABLE = 'obj_content_master_lng';

    public static array $data_cache = [];

    public function __construct(
        private readonly \ilDBInterface $db
    ) {
    }

    public function getFor(int $object_id): Translations
    {
        if (!isset(self::$data_cache[$object_id])) {
            self::$data_cache[$object_id] = $this->buildDataForObjectId($object_id);
        }

        return self::$data_cache[$object_id];
    }

    private function buildDataForObjectId(int $object_id): Translations
    {
        if ($this->db->tableExists(self::COPAGE_TRANSLATIONS_TABLE)) {
            return $this->buildDataForObjectIdForLegacySetup($object_id);
        }

        $result = $this->db->query(
            'SELECT title, description, lang_code, lang_default, lang_master' . PHP_EOL
            . 'FROM ' . self::OBJECT_TRANSLATIONS_TABLE . PHP_EOL
            . 'WHERE obj_id = ' . $this->db->quote($object_id, 'integer') . PHP_EOL
        );

        $languages = [];
        $default_language = '';
        $master_language = null;
        while ($row = $this->db->fetchAssoc($result)) {
            $languages[$row['lang_code']] = new Language(
                $row['lang_code'],
                $row['title'] ?? '',
                $row['description'] ?? '',
                $row['lang_default'] === 1,
                $row['lang_master'] === 1
            );
            if ($row['lang_default'] === 1) {
                $default_language = $row['lang_code'];
            }

            if ($row['lang_master'] === 1) {
                $master_language = $row['lang_code'];
            }
        }

        return new Translations(
            $object_id,
            $languages,
            $default_language,
            $master_language
        );
    }

    /**
     * @todo: Remove with ILIAS 12
     */
    private function buildDataForObjectIdForLegacySetup(int $object_id): Translations
    {
        $master_lang = $this->db->fetchAssoc(
            $this->db->query(
                'SELECT obj_id, master_lang, fallback_lang' . PHP_EOL
                . 'FROM ' . self::COPAGE_TRANSLATIONS_TABLE . PHP_EOL
                . 'WHERE obj_id = ' . $this->db->quote($object_id, 'integer') . PHP_EOL
            )
        );

        $result = $this->db->query(
            'SELECT title, description, lang_code, lang_default' . PHP_EOL
            . 'FROM ' . self::OBJECT_TRANSLATIONS_TABLE . PHP_EOL
            . 'WHERE obj_id = ' . $this->db->quote($object_id, 'integer') . PHP_EOL
        );

        $languages = [];
        $object_translation_default_language = '';
        while ($row = $this->db->fetchAssoc($result)) {
            $languages[$row['lang_code']] = new Language(
                $row['lang_code'],
                $row['title'] ?? '',
                $row['description'] ?? '',
                $row['lang_code'] === ($master_lang['fallback_lang'] ?? '')
                    || $row['lang_default'] === 1 && $this->determineDefaultLanguage($row['lang_code'], $master_lang) === $row['lang_code'],
                isset($master_lang['master_lang']) && $master_lang['master_lang'] === $row['lang_code']
            );
            if ($row['lang_default'] === 1) {
                $object_translation_default_language = $row['lang_code'];
            }
        }

        return new Translations(
            $object_id,
            $languages,
            $this->determineDefaultLanguage($object_translation_default_language, $master_lang),
            $master_lang['master_lang'] ?? null,
            true
        );
    }

    /**
     * @todo: Remove with ILIAS 12
     */
    private function determineDefaultLanguage(
        string $object_translation_default_language,
        ?array $master_lang
    ): string {
        if (empty($master_lang['fallback_lang'])) {
            return $object_translation_default_language;
        }
        return $master_lang['fallback_lang'];
    }

    public function delete(int $obj_id): void
    {
        $this->db->manipulate(
            'DELETE FROM ' . self::OBJECT_TRANSLATIONS_TABLE . PHP_EOL
            . 'WHERE obj_id = ' . $this->db->quote($obj_id, 'integer')
        );
    }

    public function store(
        Translations $translations
    ): Translations {
        $this->delete($translations->getObjId());

        $values = array_reduce(
            $translations->getLanguages(),
            function (string $c, Language $v) use ($translations): string {
                if ($c !== '') {
                    $c .= ',';
                }

                return "{$c}("
                    . $this->db->quote($translations->getObjId(), \ilDBConstants::T_INTEGER) . ','
                    . $this->db->quote($v->getTitle(), \ilDBConstants::T_TEXT) . ','
                    . $this->db->quote($v->getDescription(), \ilDBConstants::T_TEXT) . ','
                    . $this->db->quote($v->getLanguageCode(), \ilDBConstants::T_TEXT) . ','
                    . $this->db->quote($v->isDefault() ? 1 : 0, \ilDBConstants::T_INTEGER) . ','
                    . $this->db->quote($v->isMaster() ? 1 : 0, \ilDBConstants::T_INTEGER)
                    . ")";
            },
            ''
        );

        $this->db->manipulate(
            'INSERT INTO ' . self::OBJECT_TRANSLATIONS_TABLE . PHP_EOL
            . '(obj_id, title, description, lang_code, lang_default, lang_master)' . PHP_EOL
            . 'VALUES ' . $values
        );

        self::$data_cache[$translations->getObjId()] = $translations;
        return $translations;
    }
}
