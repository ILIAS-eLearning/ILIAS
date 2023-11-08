<?php

declare(strict_types=1);

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

namespace ILIAS\Wiki\Setup;

use ILIAS\Setup;
use ILIAS\Setup\Environment;

class AccessRBACOperationClonedObjective extends \ilAccessRBACOperationClonedObjective
{
    protected string $src_ops;
    protected string $dest_ops;

    public function __construct(string $type, string $src_ops, string $dest_ops)
    {
        parent::__construct($type, 0, 0);
        $this->src_ops = $src_ops;
        $this->dest_ops = $dest_ops;
    }

    public function getLabel(): string
    {
        return "Clone rbac operation from $this->src_ops to $this->dest_ops";
    }

    public function achieve(Environment $environment): Environment
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $this->src_id = \ilRbacReview::_getCustomRBACOperationId($this->src_ops, $db);
        $this->dest_id = \ilRbacReview::_getCustomRBACOperationId($this->dest_ops, $db);
        ;
        $env = parent::achieve($environment);
        $db->insert("settings", [
            "module" => ["text", $this->type],
            "keyword" => ["text", $this->getSettingsKeyword()],
            "value" => ["text", "1"]
        ]);

        return $env;
    }

    protected function getSettingsKeyword(): string
    {
        return "copied_perm_" . $this->src_ops . "_" . $this->dest_ops;
    }

    public function isApplicable(Environment $environment): bool
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);

        $set = $db->queryF(
            "SELECT value FROM settings " .
            " WHERE module = %s AND keyword = %s",
            ["text", "text"],
            [$this->type, $this->getSettingsKeyword()]
        );
        if ($rec = $db->fetchAssoc($set)) {
            if ($rec["value"] === "1") {
                return false;
            }
        }

        return true;
    }

}
