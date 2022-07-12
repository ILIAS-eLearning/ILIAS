<?php declare(strict_types = 1);

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
 */

require_once __DIR__ . '/../../../interfaces/interface.ilCtrlStructureInterface.php';

return [
    'ilctrlbaseclass1testgui' => [
        ilCtrlStructureInterface::KEY_CLASS_CID => '0',
        ilCtrlStructureInterface::KEY_CLASS_NAME => 'ilCtrlBaseClass1TestGUI',
        ilCtrlStructureInterface::KEY_CLASS_PATH => './Services/UICore/test/Data/GUI/class.ilCtrlBaseClass1TestGUI.php',
        ilCtrlStructureInterface::KEY_CLASS_PARENTS => [],
        ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [
            'ilctrlcommandclass1testgui',
            'ilias\\tests\\ctrl\\ilctrlnamespacedtestgui',
        ],
    ],

    'ilctrlbaseclass2testgui' => [
        ilCtrlStructureInterface::KEY_CLASS_CID => '1',
        ilCtrlStructureInterface::KEY_CLASS_NAME => 'ilCtrlBaseClass2TestGUI',
        ilCtrlStructureInterface::KEY_CLASS_PATH => './Services/UICore/test/Data/GUI/class.ilCtrlBaseClass2TestGUI.php',
        ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
            'ilctrlcommandclass1testgui'
        ],
        ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [
            'ilctrlcommandclass1testgui',
        ],
    ],

    'ilctrlcommandclass1testgui' => [
        ilCtrlStructureInterface::KEY_CLASS_CID => '2',
        ilCtrlStructureInterface::KEY_CLASS_NAME => 'ilCtrlCommandClass1TestGUI',
        ilCtrlStructureInterface::KEY_CLASS_PATH => './Services/UICore/test/Data/GUI/class.ilCtrlCommandClass1TestGUI.php',
        ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
            'ilctrlbaseclass1testgui',
            'ilctrlbaseclass2testgui',
        ],
        ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [
            'ilctrlbaseclass2testgui',
            'ilctrlcommandclass2testgui',
        ],
    ],

    'ilctrlcommandclass2testgui' => [
        ilCtrlStructureInterface::KEY_CLASS_CID => '3',
        ilCtrlStructureInterface::KEY_CLASS_NAME => 'ilCtrlCommandClass2TestGUI',
        ilCtrlStructureInterface::KEY_CLASS_PATH => './Services/UICore/test/Data/GUI/class.ilCtrlCommandClass2TestGUI.php',
        ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
            'ilctrlcommandclass1testgui',
        ],
        ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [],
    ],

    'ilias\\tests\\ctrl\\ilctrlnamespacedtestgui' => [
        ilCtrlStructureInterface::KEY_CLASS_CID => '4',
        ilCtrlStructureInterface::KEY_CLASS_NAME => 'ILIAS\\Tests\\Ctrl\\ilCtrlNamespacedTestGUI',
        ilCtrlStructureInterface::KEY_CLASS_PATH => './Services/UICore/test/Data/GUI/class.ilCtrlNamespacedTestGUI.php',
        ilCtrlStructureInterface::KEY_CLASS_PARENTS => [
            'ilctrlbaseclass1testgui',
        ],
        ilCtrlStructureInterface::KEY_CLASS_CHILDREN => [],
    ]
];
