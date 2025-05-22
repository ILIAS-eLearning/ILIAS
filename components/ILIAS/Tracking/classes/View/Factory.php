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

use ilDBInterface;
use ILIAS\DI\UIServices;
use ILIAS\Tracking\View\DataRetrieval\Factory as DataRetrievalFactory;
use ILIAS\Tracking\View\DataRetrieval\FactoryInterface as DataRetrievalFactoryInterface;
use ILIAS\Tracking\View\FactoryInterface as ViewFactoryInterface;
use ILIAS\Tracking\View\PropertyList\FactoryInterface as PropertyListFactoryInterface;
use ILIAS\Tracking\View\PropertyList\Factory as PropertyListFactory;
use ILIAS\Tracking\View\Renderer\FactoryInterface as RendererFactoryInterface;
use ILIAS\Tracking\View\Renderer\Factory as RendererFactory;
use ILIAS\Tracking\View\ProgressBlock\FactoryInterface as ProgressBlockFactoryInterface;
use ILIAS\Tracking\View\ProgressBlock\Factory as ProgressBlockFactory;

class Factory implements ViewFactoryInterface
{
    protected UIServices $ui;
    protected ilDBInterface $db;

    public function __construct()
    {
        global $DIC;
        $this->ui = $DIC->ui();
        $this->db = $DIC->database();
    }

    public function renderer(): RendererFactoryInterface
    {
        return new RendererFactory(
            $this->ui
        );
    }

    public function dataRetrieval(): DataRetrievalFactoryInterface
    {
        return new DataRetrievalFactory(
            $this->db
        );
    }

    public function propertyList(): PropertyListFactoryInterface
    {
        return new PropertyListFactory();
    }

    public function progressBlock(): ProgressBlockFactory
    {
        return new ProgressBlockFactory($this->db);
    }
}
