<?php declare(strict_types=1);

use ILIAS\Data\Version;

/**
 * Repository interface for plugin state data.
 */
interface ilPluginStateDB
{
    public function isPluginActivated(string $id) : bool;
    public function setActivation(string $id, bool $activated) : void;
    public function getCurrentPluginVersion(string $id) : ?Version;
    public function getCurrentPluginDBVersion(string $id) : ?int;
    public function setCurrentPluginVersion(string $id, Version $version, int $db_version) : void;
    public function remove(string $id);
}
