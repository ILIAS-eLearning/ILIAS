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

use ILIAS\ILIASObject\LocalDIC;
use ILIAS\ResourceStorage\Services as ResourceStorage;
use ILIAS\ILIASObject\Properties\Aggregator;
use ILIAS\ILIASObject\Properties\Translations\Language;
use ILIAS\ILIASObject\Properties\Translations\CachedRepository as TranslationsRepository;

/**
 * Object data set class
 *
 * This class implements the following entities:
 * - transl_entry: data from object_translation
 * - transl: data from obj_content_master_lang
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilObjectDataSet extends ilDataSet
{
    protected LocalDIC $obj_dic;
    protected ResourceStorage $storage;
    protected Aggregator $properties_aggregator;
    protected TranslationsRepository $translations_repository;

    public function __construct()
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;
        $this->storage = $DIC->resourceStorage();

        $object_dic = LocalDIC::dic();
        $this->properties_aggregator = $object_dic['properties.aggregator'];
        $this->translations_repository = $object_dic['properties.translations.repository'];

        parent::__construct();
    }
    public function getSupportedVersions(): array
    {
        return ['4.4.0', '5.1.0', '5.2.0', '5.4.0'];
    }

    protected function getXmlNamespace(string $entity, string $schema_version): string
    {
        return 'http://www.ilias.de/xml/Services/Object/' . $entity;
    }

    /**
     * Get field types for entity
     */
    protected function getTypes(string $entity, string $version): array
    {
        if ($entity == 'transl_entry') {
            switch ($version) {
                case '4.4.0':
                case '5.1.0':
                case '5.2.0':
                case '5.4.0':
                    return [
                        'ObjId' => 'integer',
                        'Title' => 'text',
                        'Description' => 'text',
                        'LangCode' => 'text',
                        'LangDefault' => 'integer'
                    ];
            }
        }
        if ($entity == 'transl') {
            switch ($version) {
                case '4.4.0':
                case '5.1.0':
                case '5.2.0':
                case '5.4.0':
                    return [
                        'ObjId' => 'integer',
                        'MasterLang' => 'text'
                    ];
            }
        }
        if ($entity == 'service_settings') {
            switch ($version) {
                case '5.1.0':
                case '5.2.0':
                case '5.4.0':
                    return [
                        'ObjId' => 'integer',
                        'Setting' => 'text',
                        'Value' => 'text'
                    ];
            }
        }
        if ($entity == 'common') {
            if ($version == '5.4.0') {
                return [
                    'ObjId' => 'integer'
                ];
            }
        }
        if ($entity == 'icon') {
            if ($version == '5.4.0') {
                return [
                    'ObjId' => 'integer',
                    'Filename' => 'text',
                    'Dir' => 'directory'
                ];
            }
        }
        if ($entity == 'tile') {
            if ($version == '5.4.0') {
                return [
                    'ObjId' => 'integer',
                    'Dir' => 'directory'
                ];
            }
        }
        return [];
    }

    public function readData(string $entity, string $version, array $ids): void
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        if ($entity == 'transl_entry') {
            switch ($version) {
                case '4.4.0':
                case '5.1.0':
                case '5.2.0':
                case '5.4.0':
                    $this->getDirectDataFromQuery(
                        'SELECT obj_id, title, description, lang_code, lang_default' . PHP_EOL
                        . 'FROM object_translation' . PHP_EOL
                        . 'WHERE ' . $this->db->in('obj_id', $ids, false, 'integer') . PHP_EOL
                    );
                    break;
            }
        }

        if ($entity == 'transl') {
            switch ($version) {
                case '4.4.0':
                case '5.1.0':
                case '5.2.0':
                case '5.4.0':
                    $this->getDirectDataFromQuery(
                        'SELECT obj_id, lang_code' . PHP_EOL
                        . 'FROM object_translation' . PHP_EOL
                        . 'WHERE ' . $this->db->in('obj_id', $ids, false, 'integer') . PHP_EOL
                        . 'AND master_lang = 1'
                    );
                    break;
            }
        }

        if ($entity == 'service_settings') {
            switch ($version) {
                case '5.1.0':
                case '5.2.0':
                case '5.4.0':
                    $this->data = [];
                    foreach ($ids as $id) {
                        // info, news, custom metadata, tags, taxonomies, auto rating (all stored in container settings)
                        $settings = [
                            ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
                            ilObjectServiceSettingsGUI::NEWS_VISIBILITY,
                            ilObjectServiceSettingsGUI::CUSTOM_METADATA,
                            ilObjectServiceSettingsGUI::TAG_CLOUD,
                            ilObjectServiceSettingsGUI::TAXONOMIES,
                            ilObjectServiceSettingsGUI::AUTO_RATING_NEW_OBJECTS,
                            ilObjectServiceSettingsGUI::CALENDAR_VISIBILITY
                        ];
                        if ($version == '5.2.0') {
                            $settings[] = ilObjectServiceSettingsGUI::USE_NEWS;
                        }
                        foreach ($settings as $s) {
                            if (ilContainer::_hasContainerSetting((int) $id, $s)) {
                                $val = ilContainer::_lookupContainerSetting((int) $id, $s);
                                $this->data[] = [
                                    'ObjId' => $id,
                                    'Setting' => $s,
                                    'Value' => $val
                                ];
                            }
                        }
                    }
                    break;
            }
        }
        // common
        if ($entity == 'common') {
            $this->data = [];
            foreach ($ids as $id) {
                $this->data[] = [
                    'ObjId' => $id
                ];
            }
        }
        // tile images
        if ($entity == "tile") {
            $this->data = [];
            foreach ($ids as $id) {
                $rid = $this->properties_aggregator->getFor((int) $id)
                    ->getPropertyTileImage()->getTileImage()->getRid();
                if ($rid === null) {
                    continue;
                }

                $temp_dir = $this->copyTileToTempFolderForExport($rid);

                $this->data[] = [
                    "ObjId" => $id,
                    "Dir" => $temp_dir
                ];
            }
        }

        // icons
        if ($entity == 'icon') {
            $customIconFactory = $DIC['object.customicons.factory'];
            $this->data = [];
            foreach ($ids as $id) {
                /** @var ilObjectCustomIcon $customIcon */
                $customIcon = $customIconFactory->getByObjId((int) $id, ilObject::_lookupType((int) $id));
                if ($customIcon->exists()) {
                    $this->data[] = [
                        'ObjId' => $id,
                        'Filename' => pathinfo($customIcon->getFullPath(), PATHINFO_BASENAME),
                        'Dir' => dirname($customIcon->getFullPath())
                    ];
                }
            }
        }
    }

    private function copyTileToTempFolderForExport(string $rid): string
    {
        $i = $this->storage->manage()->find($rid);
        $stream = $this->storage->consume()->stream(
            $i
        );
        $title = $this->storage->manage()->getCurrentRevision($i)->getTitle();

        $temp_dir = implode(
            DIRECTORY_SEPARATOR,
            [ILIAS_DATA_DIR, CLIENT_ID, 'temp', uniqid('tmp')]
        );
        mkdir($temp_dir);
        file_put_contents($temp_dir . DIRECTORY_SEPARATOR . $title, $stream->getStream()->getContents());
        return $temp_dir;
    }
    /**
     * Determine the dependent sets of data
     */
    protected function getDependencies(
        string $entity,
        string $version,
        ?array $rec = null,
        ?array $ids = null
    ): array {
        $rec['ObjId'] = $rec['ObjId'] ?? null;
        switch ($entity) {
            case 'common':
                return [
                    'transl' => ['ids' => $rec['ObjId']],
                    'transl_entry' => ['ids' => $rec['ObjId']],
                    'service_settings' => ['ids' => $rec['ObjId']],
                    'tile' => ['ids' => $rec['ObjId']],
                    'icon' => ['ids' => $rec['ObjId']]
                ];
        }

        return [];
    }

    public function importRecord(
        string $entity,
        array $types,
        array $rec,
        ilImportMapping $mapping,
        string $schema_version
    ): void {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        switch ($entity) {
            case 'transl_entry':
                $new_id = $this->getNewObjId($mapping, $rec['ObjId']);
                if ($new_id > 0) {
                    $transl = $this->translations_repository->getFor($new_id);
                    $this->translations_repository->store(
                        $transl->withLanguage(
                            new Language(
                                $rec['LangCode'],
                                $rec['Title'],
                                $rec['Description'],
                                (bool) $rec['LangDefault']
                            )
                        )
                    );
                }
                break;

            case 'transl':
                $new_id = $this->getNewObjId($mapping, $rec['ObjId']);
                if ($new_id > 0) {
                    $transl = $this->translations_repository->getFor($new_id);
                    $this->translations_repository->store(
                        $transl->withMasterLanguage($rec['MasterLang'])
                    );
                }
                break;

            case 'service_settings':
                // info, news, custom metadata, tags, taxonomies, auto rating (all stored in container settings)
                $settings = [
                    ilObjectServiceSettingsGUI::INFO_TAB_VISIBILITY,
                    ilObjectServiceSettingsGUI::NEWS_VISIBILITY,
                    ilObjectServiceSettingsGUI::CUSTOM_METADATA,
                    ilObjectServiceSettingsGUI::TAG_CLOUD,
                    ilObjectServiceSettingsGUI::TAXONOMIES,
                    ilObjectServiceSettingsGUI::AUTO_RATING_NEW_OBJECTS,
                    ilObjectServiceSettingsGUI::CALENDAR_VISIBILITY,
                    ilObjectServiceSettingsGUI::USE_NEWS
                ];
                $new_id = (int) $this->getNewObjId($mapping, $rec['ObjId']);
                if ($new_id > 0) {
                    if (in_array($rec['Setting'], $settings)) {
                        ilContainer::_writeContainerSetting($new_id, $rec['Setting'], $rec['Value']);
                    }
                }
                break;

            case 'icon':
                $new_id = (int) $this->getNewObjId($mapping, $rec['ObjId']);
                $dir = str_replace('..', '', $rec['Dir']);
                if ($dir != '' && $this->getImportDirectory() != '') {
                    $source_dir = $this->getImportDirectory() . '/' . $dir;

                    $customIconFactory = $DIC['object.customicons.factory'];
                    $customIcon = $customIconFactory->getByObjId($new_id, ilObject::_lookupType($new_id));
                    $customIcon->createFromImportDir($source_dir);
                }
                break;

            case 'tile':
                $new_id = (int) $this->getNewObjId($mapping, $rec['ObjId']);
                $dir = str_replace('..', '', $rec['Dir']);
                if ($new_id > 0 && $dir != '' && $this->getImportDirectory() != '') {
                    $source_dir = $this->getImportDirectory() . '/' . $dir;
                    $object_properties = $this->properties_aggregator->getFor($new_id);
                    $ti = $object_properties->getPropertyTileImage()->getTileImage();
                    $ti->createFromImportDir($source_dir);
                    $object_properties->storePropertyTileImage(
                        $object_properties->getPropertyTileImage()->withTileImage($ti)
                    );
                }
                break;
        }
    }

    public function getNewObjId(ilImportMapping $mapping, string $old_id): int
    {
        global $DIC;

        /** @var ilObjectDefinition $objDefinition */
        $objDefinition = $DIC['objDefinition'];

        $new_id = $mapping->getMapping('components/ILIAS/Container', 'objs', $old_id);
        if (!$new_id) {
            $new_id = $mapping->getMapping('components/ILIAS/ILIASObject', 'objs', $old_id);
        }
        if (!$new_id) {
            $new_id = $mapping->getMapping('components/ILIAS/ILIASObject', 'obj', $old_id);
        }
        if (!$new_id) {
            foreach ($mapping->getAllMappings() as $k => $m) {
                if (substr($k, 0, 17) == 'components/ILIAS/') {
                    foreach ($m as $type => $map) {
                        if (!$new_id) {
                            if ($objDefinition->isRBACObject($type)) {
                                $new_id = $mapping->getMapping($k, $type, $old_id);
                            }
                        }
                    }
                }
            }
        }
        return (int) $new_id;
    }
}
