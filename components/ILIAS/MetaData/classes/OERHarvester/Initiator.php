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

namespace ILIAS\MetaData\OERHarvester;

use ILIAS\DI\Container as GlobalContainer;
use ILIAS\MetaData\Services\InternalServices;
use ILIAS\MetaData\OERHarvester\RepositoryObjects\Handler as ObjectHandler;
use ILIAS\MetaData\OERHarvester\Export\Handler as ExportHandler;
use ILIAS\MetaData\OERHarvester\ExposedRecords\DatabaseRepository;
use ILIAS\MetaData\OERHarvester\XML\Writer;
use ILIAS\MetaData\OERHarvester\Settings\SettingsInterface;
use ILIAS\Export\ExportHandler\Factory as ExportService;
use ILIAS\Data\Factory as DataFactory;

class Initiator
{
    protected InternalServices $services;

    public function __construct(
        GlobalContainer $dic
    ) {
        $this->services = new InternalServices($dic);
    }

    public function harvester(): Harvester
    {
        /*
         * This should be replaced by a proper export API
         * when it is available.
         */
        $export_service = new ExportService();

        return new Harvester(
            $this->services->OERHarvester()->settings(),
            new ObjectHandler($this->services->dic()->repositoryTree()),
            new ExportHandler(
                $this->services->dic()->user(),
                $export_service,
                new DataFactory()
            ),
            $this->services->OERHarvester()->statusRepository(),
            new DatabaseRepository($this->services->dic()->database()),
            $this->services->copyright()->searcherFactory(),
            $this->services->repository()->repository(),
            new Writer(
                $this->services->repository()->repository(),
                $this->services->xml()->simpleDCWriter()
            ),
            $this->services->dic()->logger()->meta()
        );
    }

    public function settings(): SettingsInterface
    {
        return $this->services->OERHarvester()->settings();
    }
}
