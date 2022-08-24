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

/**
 * A bookable ressource
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBookingObject
{
    protected ilDBInterface $db;
    protected int $id = 0;
    protected int $pool_id = 0;
    protected string $title = "";
    protected string $description = "";
    protected int $nr_of_items = 0;
    protected ?int $schedule_id = null;
    protected string $info_file = "";
    protected string $post_text = "";
    protected string $post_file = "";

    public function __construct(
        int $a_id = null
    ) {
        global $DIC;

        $this->db = $DIC->database();
        $this->id = (int) $a_id;
        $this->read();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setDescription(string $a_value): void
    {
        $this->description = $a_value;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setPoolId(int $a_pool_id): void
    {
        $this->pool_id = $a_pool_id;
    }

    public function getPoolId(): int
    {
        return $this->pool_id;
    }

    public function setScheduleId(?int $a_schedule_id): void
    {
        $this->schedule_id = $a_schedule_id;
    }

    public function getScheduleId(): ?int
    {
        return $this->schedule_id;
    }

    public function setNrOfItems(int $a_value): void
    {
        $this->nr_of_items = $a_value;
    }

    public function getNrOfItems(): int
    {
        return $this->nr_of_items;
    }

    public function setFile(string $a_value): void
    {
        $this->info_file = $a_value;
    }

    public function getFile(): string
    {
        return $this->info_file;
    }

    public function getFileFullPath(): string
    {
        if ($this->id && $this->info_file) {
            $path = self::initStorage($this->id, "file");
            return $path . $this->info_file;
        }
        return "";
    }

    /**
     * Upload new info file
     */
    public function uploadFile(array $a_upload): bool
    {
        if (!$this->id) {
            return false;
        }

        $this->deleteFile();

        $path = self::initStorage($this->id, "file");
        $original = $a_upload["name"];
        if (ilFileUtils::moveUploadedFile($a_upload["tmp_name"], $original, $path . $original)) {
            chmod($path . $original, 0770);

            $this->setFile($original);
            return true;
        }
        return false;
    }

    // remove existing info file
    public function deleteFile(): void
    {
        if ($this->id) {
            $path = $this->getFileFullPath();
            if ($path) {
                unlink($path);
                $this->setFile(null);
            }
        }
    }

    public function setPostText(string $a_value): void
    {
        $this->post_text = $a_value;
    }

    public function getPostText(): string
    {
        return $this->post_text;
    }

    public function setPostFile(string $a_value): void
    {
        $this->post_file = $a_value;
    }

    public function getPostFile(): string
    {
        return $this->post_file;
    }

    public function getPostFileFullPath(): string
    {
        if ($this->id && $this->post_file) {
            $path = self::initStorage($this->id, "post");
            return $path . $this->post_file;
        }
        return "";
    }

    /**
     * Upload new post file
     */
    public function uploadPostFile(array $a_upload): bool
    {
        if (!$this->id) {
            return false;
        }

        $this->deletePostFile();

        $path = self::initStorage($this->id, "post");
        $original = $a_upload["name"];

        if (ilFileUtils::moveUploadedFile($a_upload["tmp_name"], $original, $path . $original)) {
            chmod($path . $original, 0770);

            $this->setPostFile($original);
            return true;
        }
        return false;
    }

    // remove existing post file
    public function deletePostFile(): void
    {
        if ($this->id) {
            $path = $this->getPostFileFullPath();
            if ($path) {
                unlink($path);
                $this->setPostFile(null);
            }
        }
    }

    public function deleteFiles(): void
    {
        if ($this->id) {
            $storage = new ilFSStorageBooking($this->id);
            $storage->delete();

            $this->setFile(null);
            $this->setPostFile(null);
        }
    }

    /**
     * Init file system storage
     */
    public static function initStorage(
        int $a_id,
        string $a_subdir = ""
    ): string {
        $storage = new ilFSStorageBooking($a_id);
        $storage->create();

        $path = $storage->getAbsolutePath() . "/";

        if ($a_subdir) {
            $path .= $a_subdir . "/";

            if (!is_dir($path) && !mkdir($path)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
            }
        }

        return $path;
    }

    protected function read(): void
    {
        $ilDB = $this->db;

        if ($this->id) {
            $set = $ilDB->query('SELECT *' .
                ' FROM booking_object' .
                ' WHERE booking_object_id = ' . $ilDB->quote($this->id, 'integer'));
            $row = $ilDB->fetchAssoc($set);
            $this->setTitle((string) $row['title']);
            $this->setDescription((string) $row['description']);
            $this->setPoolId((int) $row['pool_id']);
            $this->setScheduleId($row['schedule_id']);
            $this->setNrOfItems((int) $row['nr_items']);
            $this->setFile((string) $row['info_file']);
            $this->setPostText((string) $row['post_text']);
            $this->setPostFile((string) $row['post_file']);
        }
    }

    /**
     * @return string[][]
     */
    protected function getDBFields(): array
    {
        return array(
            'title' => array('text', $this->getTitle()),
            'description' => array('text', $this->getDescription()),
            'schedule_id' => array('text', $this->getScheduleId()),
            'nr_items' => array('text', $this->getNrOfItems()),
            'info_file' => array('text', $this->getFile()),
            'post_text' => array('text', $this->getPostText()),
            'post_file' => array('text', $this->getPostFile())
        );
    }

    public function save(): ?int
    {
        $ilDB = $this->db;

        if ($this->id) {
            return null;
        }

        $this->id = $ilDB->nextId('booking_object');

        $fields = $this->getDBFields();
        $fields['booking_object_id'] = array('integer', $this->id);
        $fields['pool_id'] = array('integer', $this->getPoolId());

        return $ilDB->insert('booking_object', $fields);
    }

    public function update(): ?int
    {
        $ilDB = $this->db;

        if (!$this->id) {
            return null;
        }

        $fields = $this->getDBFields();

        return $ilDB->update(
            'booking_object',
            $fields,
            array('booking_object_id' => array('integer', $this->id))
        );
    }

    /**
     * Get list of booking objects
     */
    public static function getList(
        int $a_pool_id,
        string $a_title = null
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $sql = 'SELECT *' .
            ' FROM booking_object' .
            ' WHERE pool_id = ' . $ilDB->quote($a_pool_id, 'integer');

        if ($a_title) {
            $sql .= ' AND (' . $ilDB->like('title', 'text', '%' . $a_title . '%') .
                ' OR ' . $ilDB->like('description', 'text', '%' . $a_title . '%') . ')';
        }

        $sql .= ' ORDER BY title';

        $set = $ilDB->query($sql);
        $res = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row;
        }
        return $res;
    }

    /**
     * Get number of booking objects for given booking pool id.
     */
    public static function getNumberOfObjectsForPool(
        int $a_pool_id
    ): int {
        global $DIC;

        $ilDB = $DIC->database();

        $sql = 'SELECT count(*) as count' .
            ' FROM booking_object' .
            ' WHERE pool_id = ' . $ilDB->quote($a_pool_id, 'integer');
        $set = $ilDB->query($sql);
        $rec = $ilDB->fetchAssoc($set);

        return (int) $rec["count"];
    }

    /**
     * Get all booking pool object ids from an specific booking pool.
     */
    public static function getObjectsForPool(
        int $a_pool_id
    ): array {
        global $DIC;
        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT booking_object_id" .
            " FROM booking_object" .
            " WHERE pool_id = " . $ilDB->quote($a_pool_id, 'integer'));

        $objects = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $objects[] = $row['booking_object_id'];
        }

        return $objects;
    }


    /**
     * Delete single entry
     */
    public function delete(): int
    {
        $ilDB = $this->db;

        if ($this->id) {
            $this->deleteFiles();

            return $ilDB->manipulate('DELETE FROM booking_object' .
                ' WHERE booking_object_id = ' . $ilDB->quote($this->id, 'integer'));
        }
        return 0;
    }

    /**
     * Get nr of available items for a set of object ids
     * @param int[] $a_obj_ids
     * @return int[]
     */
    public static function getNrOfItemsForObjects(
        array $a_obj_ids
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $map = array();

        $set = $ilDB->query("SELECT booking_object_id,nr_items" .
            " FROM booking_object" .
            " WHERE " . $ilDB->in("booking_object_id", $a_obj_ids, "", "integer"));
        while ($row = $ilDB->fetchAssoc($set)) {
            $map[(int) $row["booking_object_id"]] = (int) $row["nr_items"];
        }

        return $map;
    }

    public function doClone(
        int $a_pool_id,
        array $a_schedule_map = null
    ): void {
        $new_obj = new self();
        $new_obj->setPoolId($a_pool_id);
        $new_obj->setTitle($this->getTitle());
        $new_obj->setDescription($this->getDescription());
        $new_obj->setNrOfItems($this->getNrOfItems());
        $new_obj->setFile($this->getFile());
        $new_obj->setPostText($this->getPostText());
        $new_obj->setPostFile($this->getPostFile());

        if ($a_schedule_map) {
            $schedule_id = $this->getScheduleId();
            if ($schedule_id) {
                $new_obj->setScheduleId($a_schedule_map[$schedule_id]);
            }
        }

        $new_obj->save();

        // files
        $source = self::initStorage($this->getId());
        $target = $new_obj::initStorage($new_obj->getId());
        ilFileUtils::rCopy($source, $target);
    }

    public static function lookupPoolId(int $object_id): int
    {
        global $DIC;

        $db = $DIC->database();
        $set = $db->queryF(
            "SELECT pool_id FROM booking_object " .
            " WHERE booking_object_id = %s ",
            array("integer"),
            array($object_id)
        );
        $rec = $db->fetchAssoc($set);
        return (int) $rec["pool_id"];
    }

    public static function lookupTitle(int $object_id): string
    {
        global $DIC;

        $db = $DIC->database();
        $set = $db->queryF(
            "SELECT title FROM booking_object " .
            " WHERE booking_object_id = %s ",
            array("integer"),
            array($object_id)
        );
        $rec = $db->fetchAssoc($set);
        return $rec["title"];
    }
}
