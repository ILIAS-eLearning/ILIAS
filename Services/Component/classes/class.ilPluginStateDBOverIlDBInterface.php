<?php declare(strict_types=1);

use ILIAS\Data;
use ILIAS\Data\Version;

/**
 * Implementation of ilPluginStateDB over ilDBInterface.
 */
class ilPluginStateDBOverIlDBInterface implements ilPluginStateDB
{
    protected Data\Factory $data_factory;
    protected \ilDBInterface $db;

    protected bool $has_data = false;
    protected ?array $data = null;

    public function __construct(Data\Factory $data_factory, \ilDBInterface $db)
    {
        $this->data_factory = $data_factory;
        $this->db = $db;
    }

    protected function getData() : void
    {
        if ($this->has_data) {
            return;
        }

        $res = $this->db->query("SELECT * FROM il_plugin");
        $this->data = [];
        foreach ($this->db->fetchAll($res) as $data) {
            $this->data[$data["plugin_id"]] = [
                (bool)$data["active"],
                $data["last_update_version"] ? $this->data_factory->version($data["last_update_version"]) : null,
                $data["db_version"] ? (int)$data["db_version"] : null
            ];
        }
        $this->has_data = true;
    }

    public function isPluginActivated(string $id) : bool
    {
        $this->getData();
        return $this->data[$id][0] ?? false;
    }

    public function getCurrentPluginVersion(string $id) : ?Version
    {
        $this->getData();
        return $this->data[$id][1] ?? null;
    }

    public function getCurrentPluginDBVersion(string $id) : ?int
    {
        $this->getData();
        return $this->data[$id][2] ?? null;
    }
}
