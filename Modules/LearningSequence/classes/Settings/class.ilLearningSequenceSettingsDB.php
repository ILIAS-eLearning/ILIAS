<?php declare(strict_types=1);

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
 
/**
 * Persistence for Settings (like abstract, extro)
 */
class ilLearningSequenceSettingsDB
{
    const TABLE_NAME = 'lso_settings';

    protected ilDBInterface $database;
    protected ilLearningSequenceFilesystem $ls_filesystem;

    public function __construct(ilDBInterface $database, ilLearningSequenceFilesystem $ls_filesystem)
    {
        $this->database = $database;
        $this->ls_filesystem = $ls_filesystem;
    }

    public function store(ilLearningSequenceSettings $settings) : void
    {
        $uploads = $settings->getUploads();
        foreach ($uploads as $pre => $info) {
            $settings = $this->ls_filesystem->moveUploaded($pre, $info, $settings);
        }

        $deletions = $settings->getDeletions();
        foreach ($deletions as $pre) {
            $settings = $this->ls_filesystem->delete_image($pre, $settings);
        }

        $where = [
            "obj_id" => ["integer", $settings->getObjId()]
        ];

        $values = [
            "abstract" => ["text", $settings->getAbstract()],
            "extro" => ["text", $settings->getExtro()],
            "abstract_image" => ["text", $settings->getAbstractImage()],
            "extro_image" => ["text", $settings->getExtroImage()],
            "gallery" => ["integer", $settings->getMembersGallery()]
        ];

        $this->database->update(static::TABLE_NAME, $values, $where);
    }

    public function delete(int $obj_id) : void
    {
        $settings = $this->getSettingsFor($obj_id);

        foreach ([ilLearningSequenceFilesystem::IMG_ABSTRACT, ilLearningSequenceFilesystem::IMG_EXTRO] as $pre) {
            $settings = $this->ls_filesystem->delete_image($pre, $settings);
        }

        $query =
              "DELETE FROM " . static::TABLE_NAME . PHP_EOL
            . "WHERE obj_id = " . $this->database->quote($obj_id, "integer") . PHP_EOL
        ;

        $this->database->manipulate($query);
    }

    public function getSettingsFor(int $lso_obj_id) : ilLearningSequenceSettings
    {
        $data = $this->select($lso_obj_id);

        if (count($data) == 0) {
            $settings = $this->buildSettings($lso_obj_id);
            $this->insert($settings);
        } else {
            $settings = $this->buildSettings(
                $lso_obj_id,
                $data['abstract'],
                $data['extro'],
                $data['abstract_image'],
                $data['extro_image'],
                (bool) $data['gallery']
            );
        }

        return $settings;
    }

    /**
     * @return array<string, mixed>
     */
    protected function select(int $obj_id) : array
    {
        $ret = [];
        $query =
              "SELECT abstract, extro, abstract_image, extro_image, gallery" . PHP_EOL
            . "FROM " . static::TABLE_NAME . PHP_EOL
            . "WHERE obj_id = " . $this->database->quote($obj_id, "integer") . PHP_EOL
        ;

        $result = $this->database->query($query);

        if ($this->database->numRows($result) !== 0) {
            // TODO PHP8 Review: Check array building, should be $ret[] = ... IMO
            $ret = $this->database->fetchAssoc($result);
        }

        return $ret;
    }

    protected function buildSettings(
        int $obj_id,
        string $abstract = '',
        string $extro = '',
        string $abstract_image = null,
        string $extro_image = null,
        bool $gallery = false
    ) : ilLearningSequenceSettings {
        return new ilLearningSequenceSettings(
            $obj_id,
            $abstract,
            $extro,
            $abstract_image,
            $extro_image,
            $gallery
        );
    }

    protected function insert(ilLearningSequenceSettings $settings) : void
    {
        $values = [
            "obj_id" => ["integer", $settings->getObjId()],
            "abstract" => ["text", $settings->getAbstract()],
            "extro" => ["text", $settings->getExtro()],
            "gallery" => ["integer", $settings->getMembersGallery()]
        ];
        $this->database->insert(static::TABLE_NAME, $values);
    }
}
