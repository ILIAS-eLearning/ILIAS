<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Cache class. The cache class stores key/value pairs. Since the primary
 * key is only one text field. It's sometimes necessary to combine parts
 * like "100:200" for user id 100 and ref_id 200.
 *
 * However sometimes records should be deleted by pars of the main key. For
 * this reason up to two optional additional optional integer and two additional
 * optional text fields can be stored. A derived class may delete records
 * based on the content of this additional keys.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilCache
{
    protected string $entry;
    protected string $last_access;
    protected int $expires_after;
    protected bool $use_long_content;
    protected string $name;
    protected string $component;

    public function __construct(
        string $a_component,
        string $a_cache_name,
        bool $a_use_long_content = false
    ) {
        $this->setComponent($a_component);
        $this->setName($a_cache_name);
        $this->setUseLongContent($a_use_long_content);
    }

    /**
     * Check if cache is disabled
     * Forced if member view is active
     */
    public function isDisabled(): bool
    {
        return ilMemberViewSettings::getInstance()->isActive();
    }

    public function setComponent(string $a_val): void
    {
        $this->component = $a_val;
    }

    protected function getComponent(): string
    {
        return $this->component;
    }

    protected function setName(string $a_val): void
    {
        $this->name = $a_val;
    }

    protected function getName(): string
    {
        return $this->name;
    }

    protected function setUseLongContent(bool $a_val): void
    {
        $this->use_long_content = $a_val;
    }

    protected function getUseLongContent(): bool
    {
        return $this->use_long_content;
    }

    /**
     * Set expires after x seconds
     */
    public function setExpiresAfter(int $a_val): void
    {
        $this->expires_after = $a_val;
    }

    public function getExpiresAfter(): int
    {
        return $this->expires_after;
    }

    final public function getEntry(string $a_id): ?string
    {
        if ($this->readEntry($a_id)) {	// cache hit
            $this->last_access = "hit";
            return $this->entry;
        }
        $this->last_access = "miss";
        return null;
    }

    protected function readEntry(string $a_id): bool
    {
        global $ilDB;

        $table = $this->getUseLongContent()
            ? "cache_clob"
            : "cache_text";

        $query = "SELECT value FROM $table WHERE " .
            "component = " . $ilDB->quote($this->getComponent(), "text") . " AND " .
            "name = " . $ilDB->quote($this->getName(), "text") . " AND " .
            "expire_time > " . $ilDB->quote(time(), "integer") . " AND " .
            "ilias_version = " . $ilDB->quote(ILIAS_VERSION_NUMERIC, "text") . " AND " .
            "entry_id = " . $ilDB->quote($a_id, "text");

        $set = $ilDB->query($query);

        if ($rec = $ilDB->fetchAssoc($set)) {
            $this->entry = $rec["value"];
            return true;
        }

        return false;
    }

    public function getLastAccessStatus(): string
    {
        return $this->last_access;
    }

    public function storeEntry(
        string $a_id,
        string $a_value,
        ?int $a_int_key1 = null,
        ?int $a_int_key2 = null,
        ?string $a_text_key1 = null,
        ?string $a_text_key2 = null
    ): void {
        global $ilDB;

        $table = $this->getUseLongContent()
            ? "cache_clob"
            : "cache_text";
        $type = $this->getUseLongContent()
            ? "clob"
            : "text";

        // do not store values, that do not fit into the text field
        if (strlen($a_value) > 4000 && $type == "text") {
            return;
        }

        $set = $ilDB->replace($table, array(
            "component" => array("text", $this->getComponent()),
            "name" => array("text", $this->getName()),
            "entry_id" => array("text", $a_id)
            ), array(
            "value" => array($type, $a_value),
            "int_key_1" => array("integer", $a_int_key1),
            "int_key_2" => array("integer", $a_int_key2),
            "text_key_1" => array("text", $a_text_key1),
            "text_key_2" => array("text", $a_text_key2),
            "expire_time" => array("integer", (time() + $this->getExpiresAfter())),
            "ilias_version" => array("text", ILIAS_VERSION_NUMERIC)
            ));

        // In 1/2000 times, delete old entries
        $random = new \ilRandom();
        $num = $random->int(1, 2000);
        if ($num == 500) {
            $ilDB->manipulate(
                "DELETE FROM $table WHERE " .
                " ilias_version <> " . $ilDB->quote(ILIAS_VERSION_NUMERIC, "text") .
                " OR expire_time < " . $ilDB->quote(time(), "integer")
            );
        }
    }

    public function deleteByAdditionalKeys(
        ?int $a_int_key1 = null,
        ?int $a_int_key2 = null,
        ?string $a_text_key1 = null,
        ?string $a_text_key2 = null
    ): void {
        global $ilDB;

        $table = $this->getUseLongContent()
            ? "cache_clob"
            : "cache_text";

        $q = "DELETE FROM $table WHERE " .
            "component = " . $ilDB->quote($this->getComponent(), "text") .
            " AND name = " . $ilDB->quote($this->getName(), "text");

        $fds = array("int_key_1" => array("v" => $a_int_key1, "t" => "integer"),
            "int_key_2" => array("v" => $a_int_key2, "t" => "integer"),
            "text_key_1" => array("v" => $a_text_key1, "t" => "text"),
            "text_key_2" => array("v" => $a_text_key2, "t" => "text"));
        $sep = " AND";
        foreach ($fds as $k => $fd) {
            if (!is_null($fd["v"])) {
                $q .= $sep . " " . $k . " = " . $ilDB->quote($fd["v"], $fd["t"]);
                $set = " AND";
            }
        }
        $ilDB->manipulate($q);
    }

    public function deleteAllEntries(): void
    {
        global $ilDB;

        $table = $this->getUseLongContent()
            ? "cache_clob"
            : "cache_text";

        $q = "DELETE FROM $table WHERE " .
            "component = " . $ilDB->quote($this->getComponent(), "text") .
            " AND name = " . $ilDB->quote($this->getName(), "text");
        $ilDB->manipulate($q);
    }

    public function deleteEntry(string $a_id): void
    {
        global $ilDB;

        $table = $this->getUseLongContent()
            ? "cache_clob"
            : "cache_text";

        $ilDB->manipulate("DELETE FROM " . $table . " WHERE "
            . " entry_id = " . $ilDB->quote($a_id, "text")
            . " AND component = " . $ilDB->quote($this->getComponent(), "text") .
            " AND name = " . $ilDB->quote($this->getName(), "text"));
    }
}
