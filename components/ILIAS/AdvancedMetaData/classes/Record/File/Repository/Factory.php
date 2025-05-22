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

namespace ILIAS\AdvancedMetaData\Record\File\Repository;

use ilDBInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\FactoryInterface as FileRepositoryElementFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\FactoryInterface as FileRepositoryFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\HandlerInterface as FileRepositoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Key\FactoryInterface as FileRepositoryKeyFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Stakeholder\FactoryInterface as FileRepositoryStakeholderFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\Repository\Stakeholder\Factory as FileRepositoryStakeholderFactory;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Wrapper\FactoryInterface as FileRepositoryWrapperFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\Repository\Element\Factory as FileRepositoryElementFactory;
use ILIAS\AdvancedMetaData\Record\File\Repository\Handler as FileRepository;
use ILIAS\AdvancedMetaData\Record\File\Repository\Key\Factory as FileRepositoryKeyFactory;
use ILIAS\AdvancedMetaData\Record\File\Repository\Wrapper\Factory as FileRepositoryWrapperFactory;
use ILIAS\ResourceStorage\Services as IRSS;

class Factory implements FileRepositoryFactoryInterface
{
    protected ilDBInterface $db;
    protected IRSS $irss;

    public function __construct(
        ilDBInterface $db,
        IRSS $irss
    ) {
        $this->db = $db;
        $this->irss = $irss;
    }

    public function handler(): FileRepositoryInterface
    {
        return new FileRepository(
            $this->wrapper()->db()->handler()
        );
    }

    public function element(): FileRepositoryElementFactoryInterface
    {
        return new FileRepositoryElementFactory(
            $this->irss
        );
    }

    public function key(): FileRepositoryKeyFactoryInterface
    {
        return new FileRepositoryKeyFactory();
    }

    public function stakeholder(): FileRepositoryStakeholderFactoryInterface
    {
        return new FileRepositoryStakeholderFactory();
    }

    public function wrapper(): FileRepositoryWrapperFactoryInterface
    {
        return new FileRepositoryWrapperFactory(
            $this->db,
            $this->element(),
            $this->key()
        );
    }
}
