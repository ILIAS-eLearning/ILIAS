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

use ILIAS\Test\Administration\GlobalSettingsRepository;
use ILIAS\Test\Logging\TestLogViewer;
use ILIAS\Test\TestDIC;

/**
 * Class ilObjTestFolder
 * @author    Helmut Schottmüller <hschottm@gmx.de>
 * @author    Björn Heyser <bheyser@databay.de>
 * @ingroup components\ILIASTest
 */
class ilObjTestFolder extends ilObject
{
    public const ASS_PROC_LOCK_MODE_NONE = 'none';
    public const ASS_PROC_LOCK_MODE_FILE = 'file';
    public const ASS_PROC_LOCK_MODE_DB = 'db';

    private GlobalSettingsRepository $global_settings_repository;
    private ?TestLogViewer $test_log_viewer = null;

    public ilSetting $setting;

    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        $this->setting = new \ilSetting('assessment');
        $this->type = 'assf';
        $local_dic = TestDIC::dic();
        $this->global_settings_repository = $local_dic['settings.global.repository'];
        $this->test_log_viewer = $local_dic['logging.viewer'];

        parent::__construct($a_id, $a_call_by_reference);
    }

    public function getGlobalSettingsRepository(): GlobalSettingsRepository
    {
        return $this->global_settings_repository;
    }

    public function getTestLogViewer(): TestLogViewer
    {
        return $this->test_log_viewer;
    }
}
