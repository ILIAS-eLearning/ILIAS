<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/../../../interfaces/interface.ilCtrlStructureInterface.php';

return [
    'test_ui_plugin' => [
        'ilctrlbaseclasstestgui' => [
            ilCtrlStructureInterface::KEY_CLASS_CID      => '0',
            ilCtrlStructureInterface::KEY_CLASS_NAME     => 'ilCtrlBaseClassTestGUI',
            ilCtrlStructureInterface::KEY_CLASS_PATH     => './Services/UICore/test/Data/GUI/class.ilCtrlBaseClassTestGUI.php',
            ilCtrlStructureInterface::KEY_CLASS_PARENTS  => [],
            ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [
                'ilctrlcommandclass1testgui',
            ],
        ],
    ],
];