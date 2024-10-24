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

namespace ILIAS\AdvancedMetaData\Record\File\I\Repository;

use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\FactoryInterface as FileRepositoryElementFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\HandlerInterface as FileRepositoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Key\FactoryInterface as FileRepositoryKeyFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Stakeholder\FactoryInterface as FileRepositoryStakeholderFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Wrapper\FactoryInterface as FileRepositoryWrapperFactoryInterface;

interface FactoryInterface
{
    public function handler(): FileRepositoryInterface;

    public function element(): FileRepositoryElementFactoryInterface;

    public function key(): FileRepositoryKeyFactoryInterface;

    public function stakeholder(): FileRepositoryStakeholderFactoryInterface;

    public function wrapper(): FileRepositoryWrapperFactoryInterface;
}
