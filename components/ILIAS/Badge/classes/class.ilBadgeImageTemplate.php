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

use ILIAS\FileUpload\FileUpload;
use ILIAS\ResourceStorage\Services;
use ILIAS\FileUpload\Exception\IllegalStateException;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

class ilBadgeImageTemplate
{
    protected ilDBInterface $db;
    protected int $id = 0;
    protected string $title = '';
    protected ?string $image_rid = null;

    /** @var string[] */
    protected ?array $types = null;
    protected Services $resource_storage;
    protected FileUpload $upload_service;
    protected ilGlobalTemplateInterface $main_template;
    protected ilLogger $log;

    public function __construct(?int $a_id = null)
    {
        global $DIC;

        $this->resource_storage = $DIC->resourceStorage();
        $this->upload_service = $DIC->upload();
        $this->main_template = $DIC->ui()->mainTemplate();
        $this->log = $DIC->logger()->forComponent('badge');
        $this->db = $DIC->database();
        if ($a_id) {
            $this->read($a_id);
        }
    }

    /**
     * @return self[]
     */
    public static function getInstances(): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $res = [];

        $types = [];
        $set = $ilDB->query('SELECT * FROM badge_image_templ_type');
        while ($row = $ilDB->fetchAssoc($set)) {
            $types[$row['tmpl_id']][] = $row['type_id'];
        }

        $set = $ilDB->query('SELECT * FROM badge_image_template' .
            ' ORDER BY title');
        while ($row = $ilDB->fetchAssoc($set)) {
            $row['types'] = (array) ($types[$row['id']] ?? null);

            $obj = new self();
            $obj->importDBRow($row);
            $res[] = $obj;
        }

        return $res;
    }

    /**
     * @return self[]
     */
    public static function getInstancesByType(string $a_type_unique_id): array
    {
        $res = [];

        foreach (self::getInstances() as $tmpl) {
            if (!count($tmpl->getTypes()) || in_array($a_type_unique_id, $tmpl->getTypes(), true)) {
                $res[] = $tmpl;
            }
        }

        return $res;
    }

    //
    // setter/getter
    //

    protected function setId(int $a_id): void
    {
        $this->id = $a_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setTitle(string $a_value): void
    {
        $this->title = trim($a_value);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string[]
     */
    public function getTypes(): ?array
    {
        return $this->types;
    }

    public function setTypes(?array $types = null): void
    {
        $this->types = is_array($types)
            ? array_unique($types)
            : null;
    }

    public function processImageUpload(ilBadgeImageTemplate $badge): void
    {
        try {
            if (!$this->upload_service->hasBeenProcessed()) {
                $this->upload_service->process();
            }
            if ($this->upload_service->hasUploads()) {
                $array_result = $this->upload_service->getResults();
                $array_result = array_pop($array_result);
                if ($array_result->getName() !== '') {
                    if ($badge->getImageRid()) {
                        $this->resource_storage->manage()->remove(new ResourceIdentification($badge->getImageRid()), new ilBadgeFileStakeholder());
                    }
                    $stakeholder = new ilBadgeFileStakeholder();
                    $identification = $this->resource_storage->manage()->upload($array_result, $stakeholder);
                    $badge->setImageRid($identification);
                    $badge->update();
                }
            }
        } catch (IllegalStateException $e) {
            $this->main_template->setOnScreenMessage('failure', $e->getMessage(), true);
        }
    }

    //
    // crud
    //

    protected function read(int $a_id): void
    {
        $ilDB = $this->db;

        $set = $ilDB->query('SELECT * FROM badge_image_template' .
            ' WHERE id = ' . $ilDB->quote($a_id, 'integer'));
        if ($ilDB->numRows($set)) {
            $row = $ilDB->fetchAssoc($set);
            $row['types'] = $this->readTypes($a_id);
            $this->importDBRow($row);
        }
    }

    protected function readTypes(int $a_id): ?array
    {
        $ilDB = $this->db;

        $res = [];

        $set = $ilDB->query('SELECT * FROM badge_image_templ_type WHERE tmpl_id = ' . $ilDB->quote($a_id, 'integer'));
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row['type_id'];
        }

        if (!count($res)) {
            $res = null;
        }

        return $res;
    }

    protected function importDBRow(array $a_row): void
    {
        $this->setId($a_row['id']);
        $this->setTitle($a_row['title']);
        if (isset($a_row['image_rid'])) {
            $this->setImageRid($a_row['image_rid']);
        }
        $this->setTypes($a_row['types']);
    }

    public function create(): void
    {
        $ilDB = $this->db;

        if ($this->getId()) {
            $this->update();

            return;
        }

        $id = $ilDB->nextId('badge_image_template');
        $this->setId($id);

        $fields = $this->getPropertiesForStorage();
        $fields['id'] = ['integer', $id];

        $ilDB->insert('badge_image_template', $fields);

        $this->saveTypes();
    }

    public function update(): void
    {
        $ilDB = $this->db;

        if (!$this->getId()) {
            $this->create();

            return;
        }

        $fields = $this->getPropertiesForStorage();

        $ilDB->update(
            'badge_image_template',
            $fields,
            ['id' => ['integer', $this->getId()]]
        );

        $this->saveTypes();
    }

    public function delete(): void
    {
        $ilDB = $this->db;

        if (!$this->getId()) {
            return;
        }

        $image_rid = $this->getImageRid();
        if ($image_rid !== null) {
            try {
                $this->resource_storage->manage()->remove(
                    new ResourceIdentification($image_rid),
                    new ilBadgeFileStakeholder()
                );
            } catch (Exception $e) {
                $this->log->warning(sprintf(
                    'There was an exception, while deleting the badge image template with id %s. Exception: %s',
                    $this->getId(),
                    $e->getMessage()
                ));
            }
        }

        $ilDB->manipulate('DELETE FROM badge_image_template' .
            ' WHERE id = ' . $ilDB->quote($this->getId(), 'integer'));
    }

    /**
     * @return array<string, array>
     */
    protected function getPropertiesForStorage(): array
    {
        return [
            'title' => ['text', $this->getTitle()],
            'image_rid' => ['text', $this->getImageRid()]
        ];
    }

    protected function saveTypes(): void
    {
        $ilDB = $this->db;

        if ($this->getId()) {
            $ilDB->manipulate('DELETE FROM badge_image_templ_type' .
                ' WHERE tmpl_id = ' . $ilDB->quote($this->getId(), 'integer'));

            if ($this->getTypes()) {
                foreach ($this->getTypes() as $type) {
                    $fields = [
                        'tmpl_id' => ['integer', $this->getId()],
                        'type_id' => ['text', $type]
                    ];
                    $ilDB->insert('badge_image_templ_type', $fields);
                }
            }
        }
    }

    public function getImageRid(): ?string
    {
        // '-' was written by the removed filesystem migration for templates without an image.
        if ($this->image_rid === null || $this->image_rid === '' || $this->image_rid === '-') {
            return null;
        }

        return $this->image_rid;
    }

    public function setImageRid(?string $image_rid = null): void
    {
        $this->image_rid = $image_rid;
    }

    public function getImageFromResourceId(): string
    {
        $image_rid = $this->getImageRid();
        if ($image_rid === null) {
            return '';
        }

        $identification = $this->resource_storage->manage()->find($image_rid);
        if ($identification === null) {
            return '';
        }

        return $this->resource_storage->consume()->src($identification)->getSrc();
    }
}
