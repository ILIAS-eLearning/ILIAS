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

namespace ILIAS\User\Search;

use ILIAS\User\Profile\Fields\ConfigurationRepository as FieldConfigurationRepository;
use ILIAS\User\Profile\DataRepository as ProfileDataRepository;
use ILIAS\User\Settings\DataRepository as SettingsDataRepository;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\HTTP\Services as HttpService;
use ILIAS\Refinery\Factory as Refinery;

class EndpointFactory
{
    public function __construct(
        private readonly FieldConfigurationRepository $field_configuration_repository,
        private readonly ProfileDataRepository $profile_data_repository,
        private readonly SettingsDataRepository $settings_data_repository,
        private readonly \ilObjUser $current_user,
        private readonly HttpService $http,
        private readonly Refinery $refinery,
        private readonly \ilCtrl $ctrl,
        private readonly DataFactory $data_factory
    ) {
    }

    public function getEndpointGUI(
        EndpointConfigurator $endpoint_configurator
    ): EndpointGUI {
        return new EndpointGUI(
            $this->field_configuration_repository,
            $this->profile_data_repository,
            $this->settings_data_repository,
            $this->current_user,
            $this->http,
            $this->refinery,
            $this->ctrl,
            $this->data_factory,
            $endpoint_configurator
        );
    }
}
