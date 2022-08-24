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
class ilNotificationLink
{
    /**
     * @var string|ilNotificationParameter
     */
    private $title;
    private string $url;

    public function __construct($title, string $url)
    {
        $this->title = $title;
        $this->url = $url;
    }

    /**
     * @return  string|ilNotificationParameter
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string|ilNotificationParameter $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }
}
