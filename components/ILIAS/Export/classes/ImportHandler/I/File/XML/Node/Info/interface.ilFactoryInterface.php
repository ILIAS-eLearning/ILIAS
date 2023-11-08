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

namespace ImportHandler\I\File\XML\Node\Info;

use ImportHandler\I\File\XML\Node\Info\ilHandlerInterface as ilXMLFileNodeInfoInterface;
use ImportHandler\I\File\XML\Node\Info\ilCollectionInterface as ilXMLFileNodeInfoCollectionInterface;
use ImportHandler\I\File\XML\Node\Info\ilTreeInterface as ilXMLFileNodeInfoTreeInterface;
use ImportHandler\I\File\XML\Node\Info\Attribute\ilFactoryInterface as ilXMLFileNodeInfoAttributeFactoryInterface;

interface ilFactoryInterface
{
    public function handler(): ilXMLFileNodeInfoInterface;

    public function collection(): ilXMLFileNodeInfoCollectionInterface;

    public function tree(): ilXMLFileNodeInfoTreeInterface;

    public function attribute(): ilXMLFileNodeInfoAttributeFactoryInterface;
}
