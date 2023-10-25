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

declare(strict_types=1);

namespace ILIAS\Object\Setup;

use ILIAS\Setup\Migration;
use ILIAS\Setup\Environment;
use ILIAS\ResourceStorage\Flavour\FlavourBuilder;
use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectTileImageStakeholder;
use ILIAS\Object\Properties\CoreProperties\TileImage\ilObjectTileImageFlavourDefinition;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilObjectTileImageMigration implements Migration
{
    protected \ilResourceStorageMigrationHelper $helper;
    protected FlavourBuilder $flavour_builder;
    protected ilObjectTileImageFlavourDefinition $flavour_definition;

    public function getLabel(): string
    {
        return "Migration of Tile Images to the Resource Storage Service.";
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 10000;
    }

    public function getPreconditions(Environment $environment): array
    {
        return \ilResourceStorageMigrationHelper::getPreconditions();
    }

    public function prepare(Environment $environment): void
    {
        $this->helper = new \ilResourceStorageMigrationHelper(
            new ilObjectTileImageStakeholder(),
            $environment
        );
        $this->flavour_builder = $this->helper->getFlavourBuilder();
        $this->flavour_definition = new ilObjectTileImageFlavourDefinition();
    }

    public function step(Environment $environment): void
    {
        $query_select = $this->helper->getDatabase()->query('
            SELECT
                cs.id,
                cs.value AS extension,
                o.owner
            FROM container_settings AS cs
            INNER JOIN object_data AS o
                ON cs.id = o.obj_id
            WHERE cs.keyword = "tile_image"
            LIMIT 1;
        ');
        $next_record = $this->helper->getDatabase()->fetchObject($query_select);

        $path = $this->getFullPath($next_record->id, $next_record->extension);

        if (is_readable(dirname(dirname($path)))
            && (!file_exists(dirname($path))
                || is_readable(dirname($path)) && !file_exists($path))) {
            $this->deleteTileImageInfoFromContainerSettings($next_record->id);
            return;
        }

        $rid = $this->helper->movePathToStorage(
            $path,
            $next_record->owner
        );

        $this->flavour_builder->get($rid, $this->flavour_definition, true);

        $this->helper->getDatabase()->update(
            'object_data',
            ['tile_image_rid' => ['text', $rid->serialize()]],
            ['obj_id' => ['integer', $next_record->id],]
        );

        rmdir(dirname($path));
        $this->deleteTileImageInfoFromContainerSettings($next_record->id);
    }

    private function getFullPath(int $object_id, string $extension): string
    {
        return implode(
            DIRECTORY_SEPARATOR,
            [
                CLIENT_WEB_DIR,
                'obj_data',
                'tile_image',
                'tile_image_' . $object_id,
                'tile_image.' . $extension
            ]
        );
    }

    private function deleteTileImageInfoFromContainerSettings(int $id): void
    {
        $query_delete = $this->helper->getDatabase()->queryF('
            DELETE FROM container_settings
            WHERE keyword = "tile_image" AND id = %s
        ', ['integer'], [$id]);
        $this->helper->getDatabase()->execute($query_delete);
    }

    public function getRemainingAmountOfSteps(): int
    {
        $query = $this->helper->getDatabase()->query('
            SELECT
                count(container_settings.id) AS amount
            FROM container_settings
            WHERE container_settings.keyword = "tile_image"
        ');
        $r = $this->helper->getDatabase()->fetchObject($query);

        return (int) $r->amount;
    }
}
