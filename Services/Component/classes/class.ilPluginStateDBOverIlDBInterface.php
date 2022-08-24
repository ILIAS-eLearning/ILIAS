<?php

declare(strict_types=1);

use ILIAS\Data;
use ILIAS\Data\Version;

/**
 * Implementation of ilPluginStateDB over ilDBInterface.
 */
class ilPluginStateDBOverIlDBInterface implements ilPluginStateDB
{
    protected const TABLE_NAME = "il_plugin";

    protected Data\Factory $data_factory;
    protected \ilDBInterface $db;

    protected bool $has_data = false;
    protected ?array $data = null;

    public function __construct(Data\Factory $data_factory, \ilDBInterface $db)
    {
        $this->data_factory = $data_factory;
        $this->db = $db;
    }

    protected function getData(): void
    {
        if ($this->has_data) {
            return;
        }

        $res = $this->db->query("SELECT * FROM " . self::TABLE_NAME);
        $this->data = [];
        foreach ($this->db->fetchAll($res) as $data) {
            $this->data[$data["plugin_id"]] = [
                (bool) $data["active"],
                $data["last_update_version"] ? $this->data_factory->version($data["last_update_version"]) : null,
                $data["db_version"] ? (int) $data["db_version"] : null
            ];
        }
        $this->has_data = true;
    }

    public function isPluginActivated(string $id): bool
    {
        $this->getData();
        return $this->data[$id][0] ?? false;
    }

    public function setActivation(string $id, bool $activated): void
    {
        $this->getData();
        if (!isset($this->data[$id])) {
            throw new \InvalidArgumentException(
                "Unknown plugin '$id'"
            );
        }
        $this->db->update(
            self::TABLE_NAME,
            [
                "active" => ["integer", $activated ? 1 : 0]
            ],
            [
                "plugin_id" => ["text", $id]
            ]
        );
        $this->has_data = false;
    }

    public function getCurrentPluginVersion(string $id): ?Version
    {
        $this->getData();
        return $this->data[$id][1] ?? null;
    }

    public function getCurrentPluginDBVersion(string $id): ?int
    {
        $this->getData();
        return $this->data[$id][2] ?? null;
    }

    public function setCurrentPluginVersion(string $id, Version $version, int $db_version): void
    {
        $this->getData();
        if (isset($this->data[$id])) {
            $this->db->update(
                self::TABLE_NAME,
                [
                    "last_update_version" => ["text", (string) $version],
                    "db_version" => ["integer", $db_version]
                ],
                [
                    "plugin_id" => ["text", $id]
                ]
            );
        } else {
            $this->db->insert(
                self::TABLE_NAME,
                [
                    "plugin_id" => ["text", $id],
                    "active" => ["integer", 0],
                    "last_update_version" => ["text", (string) $version],
                    "db_version" => ["integer", $db_version]
                ]
            );
        }
        $this->has_data = false;
    }

    public function remove(string $id): void
    {
        $this->db->manipulate(
            "DELETE FROM " . self::TABLE_NAME . " WHERE plugin_id = " . $this->db->quote($id, "text")
        );
        $this->has_data = false;
    }
}
