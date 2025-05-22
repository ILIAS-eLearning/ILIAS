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

namespace ILIAS\AdvancedMetaData\Record\File\Repository\Wrapper;

use ilDBInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\FactoryInterface as FileRepositoryElementFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Key\FactoryInterface as FileRepositoryKeyFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Wrapper\DB\FactoryInterface as FileRepositoryDBWrapperFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Wrapper\FactoryInterface as FileRepositoryFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\Repository\Wrapper\DB\Factory as FileRepositoryDBWrapperFactory;

class Factory implements FileRepositoryFactoryInterface
{
    protected ilDBInterface $db;
    protected FileRepositoryElementFactoryInterface $element_factory;
    protected FileRepositoryKeyFactoryInterface $key_factory;

    public function __construct(
        ilDBInterface $db,
        FileRepositoryElementFactoryInterface $element_factory,
        FileRepositoryKeyFactoryInterface $key_factory
    ) {
        $this->db = $db;
        $this->element_factory = $element_factory;
        $this->key_factory = $key_factory;
    }

    public function db(): FileRepositoryDBWrapperFactoryInterface
    {
        return new FileRepositoryDBWrapperFactory(
            $this->db,
            $this->element_factory,
            $this->key_factory
        );
    }
}
