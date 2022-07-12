<?php declare(strict_types=1);

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

namespace ILIAS\Notifications\Repository;

use ilDBInterface;
use ILIAS\DI\Container;
use ILIAS\Notifications\Model\ilNotificationConfig;
use ILIAS\Notifications\Model\ilNotificationLink;
use ILIAS\Notifications\Model\ilNotificationObject;
use ILIAS\Notifications\Model\OSD\ilOSDNotificationObject;
use ilLanguage;

/**
 * @author Jan Posselt <jposselt@databay.de>
 */
interface ilNotificationOSDRepositoryInterface
{
    public function __construct();

    public function createOSDNotification(int $user_id, ilNotificationObject $object) : ?ilOSDNotificationObject;

    public function ifOSDNotificationExistsById(int $id) : bool;

    /**
     * @return ilOSDNotificationObject[]
     */
    public function getOSDNotificationsByUser(int $user_id) : array;

    public function deleteOSDNotificationById(int $id) : bool;
}
