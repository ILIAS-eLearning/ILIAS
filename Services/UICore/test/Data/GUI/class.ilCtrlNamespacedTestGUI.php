<?php

declare(strict_types=1);

/* Copyright (c) 2022 Thibeau Fuhrer <thibeau@sr.solutions> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Ctrl;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * @ilCtrl_isCalledBy ILIAS\Tests\Ctrl\ilCtrlNamespacedTestGUI : ilCtrlBaseClass1TestGUI
 */
class ilCtrlNamespacedTestGUI
{
    public function executeCommand(): string
    {
        return self::class;
    }
}
