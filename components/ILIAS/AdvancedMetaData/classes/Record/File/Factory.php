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

namespace ILIAS\AdvancedMetaData\Record\File;

use ilDBInterface;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\AdvancedMetaData\Record\File\Handler as File;
use ILIAS\AdvancedMetaData\Record\File\I\FactoryInterface as FileFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\HandlerInterface as FileInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\FactoryInterface as FileRepositoryFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\Repository\Factory as FileRepositoryFactory;
use ILIAS\ResourceStorage\Services as IRSS;

class Factory implements FileFactoryInterface
{
    protected ilDBInterface $db;
    protected IRSS $irss;
    protected DataFactory $data_factory;

    public function __construct()
    {
        global $DIC;
        $this->db = $DIC->database();
        $this->irss = $DIC->resourceStorage();
        $this->data_factory = new DataFactory();
    }

    public function handler(): FileInterface
    {
        return new File(
            $this,
            $this->irss,
            $this->data_factory
        );
    }

    public function repository(): FileRepositoryFactoryInterface
    {
        return new FileRepositoryFactory(
            $this->db,
            $this->irss
        );
    }
}
