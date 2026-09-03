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

namespace ILIAS\Tracking;

use ilDBInterface;
use ILIAS\DI\Container;
use ILIAS\Tracking\DB\Factory as DBFactory;
use ILIAS\Tracking\DB\FactoryInterface as DBFactoryInterface;
use ILIAS\Tracking\Export\Factory as ExportFactory;
use ILIAS\Tracking\Export\FactoryInterface as ExportFactoryInterface;
use ILIAS\Tracking\Status\Factory as StatusFactory;
use ILIAS\Tracking\Status\FactoryInterface as StatusFactoryInterface;
use ILIAS\Tracking\View\Factory as ViewFactory;
use ILIAS\Tracking\View\FactoryInterface as ViewFactoryInterface;

class Factory implements FactoryInterface
{
    protected ilDBInterface $db;
    protected Container $DIC;

    public function __construct()
    {
        global $DIC;
        $this->DIC = $DIC;
        $this->db = $DIC->database();
    }

    public function db(): DBFactoryInterface
    {
        return new DBFactory(
            $this->db
        );
    }

    public function export(): ExportFactoryInterface
    {
        return new ExportFactory(
            $this->status(),
            $this->db()->lpSettings()->element(),
            $this->db()->lpCollection()->element()
        );
    }

    public function view(): ViewFactoryInterface
    {
        return new ViewFactory();
    }

    public function status(): StatusFactoryInterface
    {
        return new StatusFactory(
            $this->DIC
        );
    }
}
