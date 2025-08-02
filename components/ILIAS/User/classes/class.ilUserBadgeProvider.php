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

use ILIAS\User\Badges\ProfileBadge;
use ILIAS\User\Profile\Profile;
use ILIAS\Language\Language;
use Psr\Http\Message\RequestInterface;

/**
 * Class ilUserBadgeProvider
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilUserBadgeProvider implements \ilBadgeProvider
{
    private readonly Language $lng;
    private readonly \ilSetting $setting;
    private readonly RequestInterface $request;
    private readonly Profile $profile;

    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC['lng'];
        $this->setting = $DIC['ilSetting'];
        $this->request = $DIC['http']->request();
        $this->profile = $DIC['user']->getProfile();
    }
    /**
     * @inheritcoc
     */
    public function getBadgeTypes(): array
    {
        return [
            new ProfileBadge(
                $this->lng,
                $this->setting,
                $this->request,
                $this->profile
            )
        ];
    }
}
