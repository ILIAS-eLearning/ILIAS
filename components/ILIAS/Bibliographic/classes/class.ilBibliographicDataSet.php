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

use ILIAS\ResourceStorage\Services;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\ILIASObject\Properties\CoreProperties\Online;

/**
 * Bibliographic dataset class
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class ilBibliographicDataSet extends ilDataSet
{
    /**
     * @var string
     */
    private const EXP_DIRECTORY = "/Modules/Bibliographic/set_1/expDir_1/";
    /**
     * @var string
     */
    private const EXP_DIRECTORY_NEW = "/components/ILIAS/Bibliographic/set_1/expDir_1/";
    /**
     * @var Services
     */
    protected $storage;
    protected \ilObjBibliographicStakeholder $stakeholder;
    /**
     * @var ilObjBibliographic
     */
    protected $import_bib_object;
    /**
     * @var ilObjUser
     */
    protected $user;
    protected array $import_temp_refs = [];
    protected array $import_temp_refs_props = [];


    public function __construct()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        $IRSS = $DIC['resource_storage'];
        parent::__construct();
        $this->db = $ilDB;
        $this->user = $ilUser;
        $this->storage = $IRSS;
        $this->stakeholder = new ilObjBibliographicStakeholder();
    }



    public function getSupportedVersions(): array
    {
        return ['4.5.0'];
    }


    public function getXmlNamespace(string $a_entity, string $a_schema_version): string
    {
        return 'http://www.ilias.de/xml/Modules/Bibliographic/' . $a_entity;
    }


    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ): void {
        if ($a_entity === 'bibl') {
            if ($new_id = $a_mapping->getMapping('components/ILIAS/Container', 'objs', $a_rec['id'])) {
                // container content
                $new_obj = ilObjectFactory::getInstanceByObjId($new_id, false);
            } else {
                $new_obj = new ilObjBibliographic();
            }
            /**
             * @var $new_obj ilObjBibliographic
             */
            $new_obj->setTitle($a_rec['title']);
            $new_obj->setDescription($a_rec['description']);
            $new_obj->setFilename($a_rec['fileName']);
            if ($new_obj->getId() === 0) {
                $new_obj->create();
            }
            $new_obj->getObjectProperties()->storePropertyIsOnline(
                new Online(false)
            );
            $this->import_bib_object = $new_obj;
            $a_mapping->addMapping('components/ILIAS/Bibliographic', 'bibl', $a_rec['id'], $new_obj->getId());
            $this->importLibraryFile($a_mapping);
        }
    }


    /**
     * Map XML attributes of entities to datatypes (text, integer...)
     */
    protected function getTypes(string $a_entity, string $a_version): array
    {
        return match ($a_entity) {
            'bibl' => ["id" => "integer", "title" => "text", "description" => "text", "filename" => "text"],
            default => [],
        };
    }


    /**
     * Return dependencies form entities to other entities (in our case these are all the DB
     * relations)
     */
    #[\Override]
    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ): array {
        return [];
    }


    public function readData(string $a_entity, string $a_version, array $a_ids): void
    {
        $this->data = [];
        if (!is_array($a_ids)) {
            $a_ids = [$a_ids];
        }
        $this->_readData($a_entity, $a_ids);
    }


    /**
     * Build data array, data is read from cache except bibl object itself
     */
    protected function _readData(string $a_entity, array $a_ids): void
    {
        switch ($a_entity) {
            case 'bibl':
                foreach ($a_ids as $bibl_id) {
                    if (ilObject::_lookupType($bibl_id) === 'bibl') {
                        $obj = new ilObjBibliographic($bibl_id);
                        $data = ['id' => $bibl_id, 'title' => $obj->getTitle(), 'description' => $obj->getDescription(), 'fileName' => $obj->getFilename()];
                        $this->data[] = $data;
                    }
                }
                break;
            default:
        }
    }


    public function exportLibraryFile(int $a_id, string $absolute_export_dir): void
    {
        $obj = new ilObjBibliographic($a_id);
        if (($rid = $obj->getResourceId()) === null) {
            return;
        }
        $fileAbsolutePath = $this->irss->consume()->stream($rid)->getStream()->getMetadata()['uri'] ?? null;
        ilFileUtils::makeDirParents($absolute_export_dir . self::EXP_DIRECTORY_NEW);
        copy($fileAbsolutePath, $absolute_export_dir . self::EXP_DIRECTORY_NEW . $obj->getFilename());
    }


    /**
     * @param ilImportMapping $a_mapping (what's it for?)
     */
    public function importLibraryFile(\ilImportMapping $a_mapping): void
    {
        $bib_id = $this->import_bib_object->getId();
        $filename = $this->import_bib_object->getFilename();
        $import_path_legacy = $this->getImportDirectory() . self::EXP_DIRECTORY . $filename;
        $import_path_new = $this->getImportDirectory() . self::EXP_DIRECTORY_NEW . $filename;
        if (file_exists($import_path_legacy)) {
            $import_path = $import_path_legacy;
        } elseif (file_exists($import_path_new)) {
            $import_path = $import_path_new;
        } else {
            return;
        }

        // create new resource from stream
        $resource = @fopen($import_path, 'rb');

        $stream = Streams::ofResource($resource);
        $identification = $this->storage->manage()->stream($stream, $this->stakeholder, $filename);

        // insert rid of the new resource into the data table
        $this->db->manipulateF(
            'UPDATE `il_bibl_data` SET `rid` = %s WHERE `id` = %s;',
            ['text', 'integer'],
            [
                $identification->serialize(),
                $bib_id,
            ]
        );
        $this->import_bib_object->setResourceId($identification);
        $this->import_bib_object->setMigrated(true);
        $this->import_bib_object->update();
        $this->import_bib_object->parseFileToDatabase();
    }
}
