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
 
use ILIAS\Filesystem\Provider\FlySystem\FlySystemFilesystemFactory;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\StorageHandler\FileSystemStorageHandler;
use ILIAS\Filesystem\Provider\Configuration\LocalConfig;
use ILIAS\FileUpload\Location;
use ILIAS\ResourceStorage\Revision\Repository\RevisionARRepository;
use ILIAS\ResourceStorage\Resource\Repository\ResourceARRepository;
use ILIAS\ResourceStorage\Information\Repository\InformationARRepository;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderARRepository;
use ILIAS\ResourceStorage\Lock\LockHandlerilDB;
use ILIAS\Filesystem\Stream\Streams;

/**
 * Bibliographic dataset class
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class ilBibliographicDataSet extends ilDataSet
{

    /**
     * @var \ILIAS\ResourceStorage\Services
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
    protected array $import_temp_refs = array();
    protected array $import_temp_refs_props = array();


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


    public function getSupportedVersions() : array
    {
        return array('4.5.0');
    }


    public function getXmlNamespace(string $a_entity, string $a_schema_version) : string
    {
        return 'http://www.ilias.de/xml/Modules/Bibliographic/' . $a_entity;
    }


    public function importRecord(
        string $a_entity,
        array $a_types,
        array $a_rec,
        ilImportMapping $a_mapping,
        string $a_schema_version
    ) : void {
        switch ($a_entity) {
            case 'bibl':
                if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['id'])) {
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
                $new_obj->setOnline(false);
                if (!$new_obj->getId()) {
                    $new_obj->create();
                }
                $this->import_bib_object = $new_obj;
                $a_mapping->addMapping('Modules/Bibliographic', 'bibl', $a_rec['id'], $new_obj->getId());
                $this->importLibraryFile($a_mapping);
                break;
        }
    }


    /**
     * Map XML attributes of entities to datatypes (text, integer...)
     */
    protected function getTypes(string $a_entity, string $a_version) : array
    {
        switch ($a_entity) {
            case 'bibl':
                return array(
                    "id" => "integer",
                    "title" => "text",
                    "description" => "text",
                    "filename" => "text",
                    "is_online" => "integer",
                );
            default:
                return array();
        }
    }


    /**
     * Return dependencies form entities to other entities (in our case these are all the DB
     * relations)
     */
    protected function getDependencies(
        string $a_entity,
        string $a_version,
        ?array $a_rec = null,
        ?array $a_ids = null
    ) : array {
        return [];
    }


    public function readData(string $a_entity, string $a_version, array $a_ids) : void
    {
        $this->data = array();
        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }
        $this->_readData($a_entity, $a_ids);
    }


    /**
     * Build data array, data is read from cache except bibl object itself
     */
    protected function _readData(string $a_entity, array $a_ids) : void
    {
        switch ($a_entity) {
            case 'bibl':
                foreach ($a_ids as $bibl_id) {
                    if (ilObject::_lookupType($bibl_id) === 'bibl') {
                        $obj = new ilObjBibliographic($bibl_id);
                        $data = array(
                            'id' => $bibl_id,
                            'title' => $obj->getTitle(),
                            'description' => $obj->getDescription(),
                            'fileName' => $obj->getFilename(),
                            'is_online' => $obj->getOnline(),
                        );
                        $this->data[] = $data;
                    }
                }
                break;
            default:
        }
    }


    public function exportLibraryFile(int $a_id) : void
    {
        $obj = new ilObjBibliographic($a_id);
        $fileAbsolutePath = $obj->getLegacyAbsolutePath();
        copy($fileAbsolutePath, $this->absolute_export_dir . "/" . $obj->getFilename());
    }


    /**
     * @param ilImportMapping $a_mapping (what's it for?)
     */
    public function importLibraryFile(\ilImportMapping $a_mapping) : void
    {
        $bib_id = $this->import_bib_object->getId();
        $filename = $this->import_bib_object->getFilename();
        $import_path = $this->getImportDirectory() . "/Modules/Bibliographic/set_1/expDir_1/" . $filename;

        // create new resource from stream
        $stream = Streams::ofResource(@fopen($import_path, 'rb'));
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
    }
}
