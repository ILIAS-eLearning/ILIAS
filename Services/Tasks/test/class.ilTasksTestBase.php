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

require_once __DIR__ . '/bootstrap.php';

use PHPUnit\Framework\TestCase;

/**
 * Base test class for tasks tests
 *
 * @author killing@leifos.de
 */
class ilTasksTestBase extends TestCase
{
    /**
     * @var bool
     */
    protected $backupGlobals = false;

    protected $_mock_user;
    protected $_mock_lng;
    protected $_mock_ui;
    protected $_mock_access;
    protected $_mock_task_service;
    protected $_mock_dic;

    /**
     *
     */
    protected function setUp() : void
    {
        $this->_mock_user = $this->getMockBuilder('ilObjUser')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mock_lng = $this->getMockBuilder('ilLanguage')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mock_ui = $this->getMockBuilder('\ILIAS\DI\UIServices')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mock_access = $this->getMockBuilder('ilAccessHandler')
            ->disableOriginalConstructor()
            ->getMock();

        require_once __DIR__ . '/class.ilDummyDerivedTaskProvider.php';
        require_once __DIR__ . '/class.ilDummyDerivedTaskProviderFactory.php';

        $dummy_task_provider_factory = new ilDummyDerivedTaskProviderFactory();
        $this->_mock_task_service = new ilTaskService(
            $this->_mock_user,
            $this->_mock_lng,
            $this->_mock_ui,
            $this->_mock_access,
            [$dummy_task_provider_factory]
        );
        $dummy_task_provider_factory->setTaskService($this->_mock_task_service);
    }

    public function getTaskServiceMock()
    {
        return $this->_mock_task_service;
    }
}
