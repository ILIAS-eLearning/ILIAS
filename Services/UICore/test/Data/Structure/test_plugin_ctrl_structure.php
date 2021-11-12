<?php declare(strict_types = 1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/../../../interfaces/interface.ilCtrlStructureInterface.php';

return [
    'test_ui_plugin' => [
        'ilvalidtestplugingui' => [
            ilCtrlStructureInterface::KEY_CLASS_CID      => '4',
            ilCtrlStructureInterface::KEY_CLASS_NAME     => 'ilValidTestPluginGUI',
            ilCtrlStructureInterface::KEY_CLASS_PATH     => './Services/UICore/test/Data/Plugins/Valid/Services/UIComponent/UserInterfaceHook/ValidTestPlugin/classes/class.ilValidTestPluginGUI.php',
            ilCtrlStructureInterface::KEY_CLASS_PARENTS  => [
                'ilctrlbaseclass2testgui',
            ],
            ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [],
        ],
    ],
];