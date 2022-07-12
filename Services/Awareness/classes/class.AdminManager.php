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

namespace ILIAS\Awareness;

/**
 * Administrate awareness tool
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class AdminManager
{
    protected const MODE_INACTIVE = 0;
    protected const MODE_ONLINE_ONLY = 1;
    protected const MODE_INCL_OFFLINE = 2;

    protected \ilLanguage $lng;
    protected InternalDomainService $domain_service;
    protected InternalDataService $data_service;
    protected \ilSetting $settings;
    protected int $user_id;
    protected int $ref_id = 0;
    
    public function __construct(
        int $ref_id,
        InternalDataService $data_service,
        InternalDomainService $domain_service
    ) {
        $this->ref_id = $ref_id;
        $this->settings = $domain_service->awarenessSettings();
        $this->domain_service = $domain_service;
        $this->lng = $domain_service->lng();
        $this->data_service = $data_service;
    }

    /**
     * @return User\Provider[]
     */
    public function getAllUserProviders() : array
    {
        return $this->domain_service->userProvider()->getAllProviders();
    }

    /**
     * Activate provider
     */
    public function setActivationMode(string $provider_id, int $a_val) : void
    {
        $this->settings->set("up_act_" . $provider_id, (string) $a_val);
    }

    public function getActivationMode(string $provider_id) : int
    {
        return (int) $this->settings->get("up_act_" . $provider_id);
    }

    public function isProviderActivated(string $provider_id) : bool
    {
        return ($this->getActivationMode($provider_id) != self::MODE_INACTIVE);
    }

    public function includesProviderOfflineUsers(string $provider_id) : bool
    {
        return ($this->getActivationMode($provider_id) == self::MODE_INCL_OFFLINE);
    }
    
    /**
     * @return array<int,string>
     */
    public function getModeOptions() : array
    {
        $options = array(
            self::MODE_INACTIVE => $this->lng->txt("awrn_inactive"),
            self::MODE_ONLINE_ONLY => $this->lng->txt("awrn_online_only"),
            self::MODE_INCL_OFFLINE => $this->lng->txt("awrn_incl_offline")
        );

        return $options;
    }
}
