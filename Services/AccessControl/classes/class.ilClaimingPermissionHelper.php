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
 * Claiming permission helper base class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
abstract class ilClaimingPermissionHelper
{
    protected int $user_id = 0;
    protected int $ref_id = 0;
    protected array $map = [];
    protected array $context_ids = [];
    protected array $plugins = [];
    protected static array $instances = [];

    protected function __construct(int $a_user_id, int $a_ref_id)
    {
        $this->setUserId($a_user_id);
        $this->setRefId($a_ref_id);
        $this->map = $this->buildPermissionMap();
        $this->reset();
    }

    public static function getInstance(int $a_user_id, int $a_ref_id): self
    {
        if (!isset(self::$instances[$a_user_id][$a_ref_id])) {
            self::$instances[$a_user_id][$a_ref_id] = new static($a_user_id, $a_ref_id);
        }
        return self::$instances[$a_user_id][$a_ref_id];
    }

    /**
     * Reset (internal caches)
     */
    public function reset(): void
    {
        $this->context_ids = array();
    }


    // properties

    protected function setUserId(int $a_value): void
    {
        $this->user_id = $a_value;
    }

    protected function getUserId(): int
    {
        return $this->user_id;
    }

    protected function setRefId(int $a_value): void
    {
        $this->ref_id = $a_value;
    }

    protected function getRefId(): int
    {
        return $this->ref_id;
    }


    // caching

    /**
     * Get all context ids for context type (from DB, is cached)
     */
    abstract protected function readContextIds(int $a_context_type): array;


    // permissions

    /**
     * Build map of context and actions
     */
    abstract protected function buildPermissionMap(): array;

    /**
     * Check if given combination of context and action is valid
     */
    protected function isValidContextAndAction(
        int $a_context_type,
        string $a_context_id,
        int $a_action_id,
        ?int $a_action_sub_id = null
    ): bool {
        $valid = false;

        if (array_key_exists($a_context_type, $this->map)) {
            if (!$a_action_sub_id) {
                if (in_array($a_action_id, $this->map[$a_context_type]["actions"])) {
                    $valid = true;
                }
            } else {
                if (array_key_exists($a_action_id, $this->map[$a_context_type]["subactions"]) &&
                    in_array($a_action_sub_id, $this->map[$a_context_type]["subactions"][$a_action_id])) {
                    $valid = true;
                }
            }
        }

        if ($valid &&
            $a_context_id &&
            !in_array($a_context_id, $this->getValidContextIds($a_context_type))) {
            $valid = false;
        }

        if (DEVMODE && !$valid) {
            trigger_error("INVALID permission context - " . $a_context_type . ":" . $a_context_id . ":" . $a_action_id . ":" . $a_action_sub_id, E_USER_WARNING);
        }

        return $valid;
    }

    /**
     * Get context ids for context type (uses cache)
     *
     * @see self::readContextIds()
     */
    protected function getValidContextIds(int $a_context_type): array
    {
        if (!array_key_exists($a_context_type, $this->context_ids)) {
            $this->context_ids[$a_context_type] = $this->readContextIds($a_context_type);
        }
        return (array) $this->context_ids[$a_context_type];
    }

    /**
     * Check permission
     */
    public function hasPermission(
        int $a_context_type,
        string $a_context_id,
        int $a_action_id,
        ?int $a_action_sub_id = null
    ): bool {
        if ($this->isValidContextAndAction($a_context_type, $a_context_id, $a_action_id, $a_action_sub_id)) {
            return $this->checkPermission($a_context_type, $a_context_id, $a_action_id, $a_action_sub_id);
        }
        // :TODO: exception?
        return false;
    }

    /**
     * Check permissions
     */
    public function hasPermissions(int $a_context_type, string $a_context_id, array $a_action_ids): array
    {
        $res = array();

        foreach ($a_action_ids as $action_id) {
            if (is_array($action_id)) {
                $action_sub_id = $action_id[1];
                $action_id = $action_id[0];

                $res[$action_id][$action_sub_id] = $this->hasPermission($a_context_type, $a_context_id, $action_id, $action_sub_id);
            } else {
                $res[$action_id] = $this->hasPermission($a_context_type, $a_context_id, $action_id);
            }
        }

        return $res;
    }

    /**
     * Check permission (helper: rbac, plugins)
     */
    protected function checkPermission(
        int $a_context_type,
        string $a_context_id,
        int $a_action_id,
        ?int $a_action_sub_id = null
    ): bool {
        return ($this->checkRBAC() &&
            $this->checkPlugins($a_context_type, (string) $a_context_id, $a_action_id, $a_action_sub_id));
    }

    /**
     * Check permission against RBAC
     */
    protected function checkRBAC(): bool
    {
        global $DIC;
        $ilAccess = $DIC->access();

        // we are currently only supporting write operations
        return $ilAccess->checkAccessOfUser($this->getUserId(), "write", "", $this->getRefId());
    }

    /**
     * Get active plugins (for current slot)
     */
    abstract protected function getActivePlugins(): array;

    /**
     * Check permission against plugins
     */
    protected function checkPlugins(
        int $a_context_type,
        string $a_context_id,
        int $a_action_id,
        ?int $a_action_sub_id = null
    ): bool {
        $valid = true;

        if (!is_array($this->plugins)) {
            $this->plugins = $this->getActivePlugins();
        }

        foreach ($this->plugins as $plugin) {
            if (!$plugin->checkPermission($this->getUserId(), $a_context_type, $a_context_id, $a_action_id, $a_action_sub_id)) {
                $valid = false;
                break;
            }
        }

        return $valid;
    }

    /**
     * @return array of object type strings
     */
    public function getAllowedObjectTypes(): array
    {
        $accepted_types = ['cat','crs','sess','grp','iass', 'exc'];

        $obj_def = new ilObjectDefinition();
        $adv_md_types = $obj_def->getAdvancedMetaDataTypes();

        $valid_accepted_types = array();
        foreach ($adv_md_types as $value) {
            if (in_array($value['obj_type'], $accepted_types) || in_array($value['sub_type'], $accepted_types)) {
                array_push($valid_accepted_types, $value['obj_type']);
            }
        }

        return $valid_accepted_types;
    }
}
