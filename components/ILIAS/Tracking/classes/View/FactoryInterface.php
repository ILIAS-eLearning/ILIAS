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

declare(strict_types=0);

namespace ILIAS\Tracking\View;

use ILIAS\Tracking\View\Renderer\FactoryInterface as RendererFactoryInterface;
use ILIAS\Tracking\View\DataRetrieval\FactoryInterface as DataRetrievalFactoryInterface;
use ILIAS\Tracking\View\PropertyList\FactoryInterface as PropertyListFactoryInterface;
use ILIAS\Tracking\View\ProgressBlock\FactoryInterface as ProgressBlockFactoryInterface;

interface FactoryInterface
{
    public function renderer(): RendererFactoryInterface;

    public function dataRetrieval(): DataRetrievalFactoryInterface;

    public function propertyList(): PropertyListFactoryInterface;

    public function progressBlock(): ProgressBlockFactoryInterface;
}
