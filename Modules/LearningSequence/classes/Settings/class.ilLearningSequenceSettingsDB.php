<?php declare(strict_types=1);

/* Copyright (c) 2021 - Nils Haagen <nils.haagen@concepts-and-training.de> - Extended GPL, see LICENSE */

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
        if (count($uploads) > 0) {
            foreach ($uploads as $pre => $info) {
                $settings = $this->ls_filesystem->moveUploaded($pre, $info, $settings);
            }
        }

        $deletions = $settings->getDeletions();
        if (count($deletions) > 0) {
            foreach ($deletions as $pre) {
                $settings = $this->ls_filesystem->delete_image($pre, $settings);
            }
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
