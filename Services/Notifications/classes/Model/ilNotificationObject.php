<?php

declare(strict_types=1);

/******************************************************************************
 *
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
 *     https://www.ilias.de
 *     https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

namespace ILIAS\Notifications\Model;

use ilNotification;
use ilObjUser;

/**
 * @author Jan Posselt <jposselt@databay.de>
 */
class ilNotificationObject
{
    public ilNotificationConfig $baseNotification;
    public ilObjUser $user;
    public string $title = '';
    public string $shortDescription = '';
    public string $longDescription = '';
    /**
     * @var ilNotificationLink[]
     */
    public array $links = [];
    public string $iconPath = '';
    public array $handlerParams = [];

    public function __construct(ilNotificationConfig $baseNotification, ilObjUser $user)
    {
        $this->baseNotification = $baseNotification;
        $this->user = $user;
        $this->handlerParams = $this->baseNotification->getHandlerParams();
    }

    /**
     * @return string[]
     */
    public function __sleep(): array
    {
        return ['title', 'shortDescription', 'longDescription', 'iconPath', 'links', 'handlerParams'];
    }
}
