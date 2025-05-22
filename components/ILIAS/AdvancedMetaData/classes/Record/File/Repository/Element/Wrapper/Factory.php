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

namespace ILIAS\AdvancedMetaData\Record\File\Repository\Element\Wrapper;

use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\Wrapper\FactoryInterface as FileRepositoryElementFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\Wrapper\IRSS\FactoryInterface as FileRepositoryElementIRSSWrapperFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\Repository\Element\Wrapper\IRSS\Factory as FileRepositoryElementIRSSWrapperFactory;
use ILIAS\ResourceStorage\Services as IRSS;

class Factory implements FileRepositoryElementFactoryInterface
{
    protected IRSS $irss;

    public function __construct(
        IRSS $irss
    ) {
        $this->irss = $irss;
    }

    public function irss(): FileRepositoryElementIRSSWrapperFactoryInterface
    {
        return new FileRepositoryElementIRSSWrapperFactory(
            $this->irss
        );
    }
}
