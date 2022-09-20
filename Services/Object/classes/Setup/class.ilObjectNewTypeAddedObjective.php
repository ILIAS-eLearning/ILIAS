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

use ILIAS\Setup;
use ILIAS\Setup\Environment;

class ilObjectNewTypeAddedObjective implements Setup\Objective
{
    protected string $type;
    protected string $type_title;

    public function __construct(string $type, string $type_title)
    {
        $this->type = $type;
        $this->type_title = $type_title;
    }

    public function getHash(): string
    {
        return hash("sha256", self::class);
    }

    public function getLabel(): string
    {
        return "Add new type $this->type to object data";
    }

    public function isNotable(): bool
    {
        return true;
    }

    /**
     * @return \ilDatabaseInitializedObjective[]
     */
    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseInitializedObjective()
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);

        $id = $db->nextId("object_data");

        $values = [
            'obj_id' => ['integer', $id],
            'type' => ['text', 'typ'],
            'title' => ['text', $this->type],
            'description' => ['text', $this->type_title],
            'owner' => ['integer', -1],
            'create_date' => ['timestamp', date("Y-m-d H:i:s")],
            'last_update' => ['timestamp', date("Y-m-d H:i:s")]
        ];

        $db->insert("object_data", $values);

        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        if (is_null(ilObject::_getObjectTypeIdByTitle($this->type))) {
            return true;
        }
        return false;
    }
}
