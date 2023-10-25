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

namespace ILIAS\Taxonomy;

use ILIAS\DI\Container;

class InternalService
{
    protected Container $DIC;
    protected static ?InternalDataService $data = null;
    protected static ?InternalRepoService $repo = null;
    protected static ?InternalDomainService $domain = null;
    protected static ?InternalGUIService $gui = null;

    public function __construct(Container $DIC)
    {
        $this->DIC = $DIC;
    }

    public function data(): InternalDataService
    {
        if (is_null(self::$data)) {
            self::$data = new InternalDataService();
        }
        return self::$data;
    }

    public function repo(): InternalRepoService
    {
        if (is_null(self::$repo)) {
            self::$repo = new InternalRepoService(
                $this->data(),
                $this->DIC->database()
            );
        }
        return self::$repo;
    }

    public function domain(): InternalDomainService
    {
        if (is_null(self::$domain)) {
            self::$domain = new InternalDomainService(
                $this->DIC,
                $this->repo(),
                $this->data()
            );
        }
        return self::$domain;
    }

    public function gui(): InternalGUIService
    {
        if (is_null(self::$gui)) {
            self::$gui = new InternalGUIService(
                $this->DIC,
                $this->data(),
                $this->domain()
            );
        }
        return self::$gui;
    }
}
