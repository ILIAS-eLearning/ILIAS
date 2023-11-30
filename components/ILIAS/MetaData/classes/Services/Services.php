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

namespace ILIAS\MetaData\Services;

use ILIAS\MetaData\Services\Manipulator\ManipulatorInterface;
use ILIAS\MetaData\Services\DataHelper\DataHelperInterface;
use ILIAS\MetaData\Services\DataHelper\DataHelper;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Services\Paths\PathsInterface;
use ILIAS\MetaData\Services\Reader\ReaderInterface;
use ILIAS\MetaData\Services\Reader\Reader;
use ILIAS\MetaData\Services\Paths\Paths;
use ILIAS\MetaData\Services\Manipulator\Manipulator;
use ILIAS\DI\Container as GlobalContainer;

class Services implements ServicesInterface
{
    protected InternalServices $internal_services;

    protected PathsInterface $paths;
    protected DataHelperInterface $data_helper;

    public function __construct(GlobalContainer $dic)
    {
        $this->internal_services = new InternalServices($dic);
    }

    public function read(
        int $obj_id,
        int $sub_id,
        string $type,
        PathInterface $limited_to = null
    ): ReaderInterface {
        if ($sub_id === 0) {
            $sub_id = $obj_id;
        }

        $repo = $this->internal_services->repository()->repository();
        if (isset($limited_to)) {
            $set = $repo->getMDOnPath($limited_to, $obj_id, $sub_id, $type);
        } else {
            $set = $repo->getMD($obj_id, $sub_id, $type);
        }
        return new Reader(
            $this->internal_services->paths()->navigatorFactory(),
            $set
        );
    }

    public function manipulate(int $obj_id, int $sub_id, string $type): ManipulatorInterface
    {
        $repo = $this->internal_services->repository()->repository();
        $set = $repo->getMD($obj_id, $sub_id, $type);
        return new Manipulator(
            $this->internal_services->manipulator()->manipulator(),
            $set
        );
    }

    public function paths(): PathsInterface
    {
        if (isset($this->paths)) {
            return $this->paths;
        }
        return new Paths(
            $this->internal_services->paths()->pathFactory()
        );
    }

    public function dataHelper(): DataHelperInterface
    {
        if (isset($this->data_helper)) {
            return $this->data_helper;
        }
        return new DataHelper(
            $this->internal_services->dataHelper()->dataHelper(),
            $this->internal_services->presentation()->data()
        );
    }
}
