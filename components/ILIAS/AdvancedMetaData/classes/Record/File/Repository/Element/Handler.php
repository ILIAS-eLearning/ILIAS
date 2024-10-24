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

namespace ILIAS\AdvancedMetaData\Record\File\Repository\Element;

use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\HandlerInterface as FileRepositoryElementInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\Wrapper\IRSS\HandlerInterface as FileRepositoryElementIRSSWrapperInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\Wrapper\IRSS\FactoryInterface as FileRepositoryElementIRSSWrapperFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Key\HandlerInterface as FileRepositoryKeyInterface;

class Handler implements FileRepositoryElementInterface
{
    protected FileRepositoryKeyInterface $key;
    protected FileRepositoryElementIRSSWrapperFactoryInterface $irss_wrapper_factory;

    public function __construct(
        FileRepositoryElementIRSSWrapperFactoryInterface $irss_wrapper_factory
    ) {
        $this->irss_wrapper_factory = $irss_wrapper_factory;
    }

    public function withKey(
        FileRepositoryKeyInterface $key
    ): FileRepositoryElementInterface {
        $clone = clone $this;
        $clone->key = $key;
        return $clone;
    }

    public function getKey(): FileRepositoryKeyInterface
    {
        return $this->key;
    }

    public function getIRSS(): FileRepositoryElementIRSSWrapperInterface
    {
        return $this->irss_wrapper_factory->handler()
            ->withResourceIdSerialized($this->key->getResourceIdSerialized());
    }
}
