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

namespace ILIAS\Export\ExportHandler\Consumer\ExportConfig;

use ILIAS\Export\ExportHandler\Consumer\ExportConfig\Collection as ExportConfigCollection;
use ILIAS\Export\ExportHandler\I\Consumer\ExportConfig\CollectionInterface as ExportConfigCollectionInterface;
use ILIAS\Export\ExportHandler\I\Consumer\ExportConfig\FactoryInterface as ExportConfigFactoryInterface;
use ILIAS\Export\ExportHandler\I\Consumer\ExportConfig\HandlerInterface as ExportConfigHandlerInterface;
use ILIAS\Export\Setup\BuildExportOptionsMapObjective as BuildExportOptionsMapObjective;

class Factory implements ExportConfigFactoryInterface
{
    public function collection(): ExportConfigCollectionInterface
    {
        return new ExportConfigCollection();
    }

    public function allExportConfigs(): ExportConfigCollectionInterface
    {
        $collection = $this->collection();
        $class_names = (include BuildExportOptionsMapObjective::PATH())['export_configs'];
        foreach ($class_names as $class_name) {
            /** @var ExportConfigHandlerInterface $export_config */
            $export_config = new ($class_name)();
            $collection = $collection->withElement($export_config);
        }
        return $collection;
    }

    public function exportConfigByComponent(string $component): ExportConfigHandlerInterface|null
    {
        # Expected component string structure: 'components/ILIAS/COPage'
        $component_parts = explode('/', $component);
        if (count($component_parts) === 3) {
            $class_name = 'il' . $component_parts[2] . 'ExportConfig';
            return $this->exportConfigByClassName($class_name);
        }
        return null;
    }

    public function exportConfigByClassName(string $class_name): ExportConfigHandlerInterface|null
    {
        return $this->allExportConfigs()[$class_name] ?? null;
    }
}
