<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/../../../interfaces/interface.ilCtrlStructureInterface.php';

return [
    'ilctrlbaseclasstestgui' => [
        ilCtrlStructureInterface::KEY_CLASS_CID      => '0',
        ilCtrlStructureInterface::KEY_CLASS_NAME     => 'ilCtrlBaseClassTestGUI',
        ilCtrlStructureInterface::KEY_CLASS_PATH     => './Services/UICore/test/Data/GUI/class.ilCtrlBaseClassTestGUI.php',
        ilCtrlStructureInterface::KEY_CLASS_PARENTS  => [],
        ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [
            'ilctrlcommandclass1testgui',
        ],
    ],

    'ilctrlcommandclass1testgui' => [
        ilCtrlStructureInterface::KEY_CLASS_CID      => '1',
        ilCtrlStructureInterface::KEY_CLASS_NAME     => 'ilCtrlCommandClass1TestGUI',
        ilCtrlStructureInterface::KEY_CLASS_PATH     => './Services/UICore/test/Data/GUI/class.ilCtrlCommandClass1TestGUI.php',
        ilCtrlStructureInterface::KEY_CLASS_PARENTS  => [
            'ilctrlbaseclasstestgui',
        ],
        ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [
            'ilctrlcommandclass2testgui',
        ],
    ],

    'ilctrlcommandclass2testgui' => [
        ilCtrlStructureInterface::KEY_CLASS_CID      => '2',
        ilCtrlStructureInterface::KEY_CLASS_NAME     => 'ilCtrlCommandClass2TestGUI',
        ilCtrlStructureInterface::KEY_CLASS_PATH     => './Services/UICore/test/Data/GUI/class.ilCtrlCommandClass2TestGUI.php',
        ilCtrlStructureInterface::KEY_CLASS_PARENTS  => [
            'ilctrlcommandclass1testgui',
        ],
        ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [],
    ],
];