<?php

declare(strict_types=1);

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


class ilOpenIdAttributeMappingTemplate
{
    const OPEN_ID_CONFIGURED_SCOPES = 'auth_oidc_configured_scopes';

    /**
     * @param array $additional_scopes
     * @return array
     */
    public function getMappingRulesByAdditionalScopes(array $additional_scopes): array
    {
        $mapping_rule = [];
        if(in_array('address', $additional_scopes)) {
            $mapping_rule = $this->loadAddress($mapping_rule);
        }
        if(in_array('email', $additional_scopes)) {
            $mapping_rule = $this->loadEmail($mapping_rule);
        }
        if(in_array('phone', $additional_scopes)) {
            $mapping_rule = $this->loadPhone($mapping_rule);
        }
        if(in_array('profile', $additional_scopes)) {
            $mapping_rule = $this->loadProfile($mapping_rule);
        }
        return $mapping_rule;
    }

    private function loadProfile($mapping_rule) {
        $mapping_rule['lastname']  = 'family_name';
        $mapping_rule['firstname'] = 'given_name';
        $mapping_rule['login']     = 'preferred_username';
        $mapping_rule['gender']    = 'gender';
        $mapping_rule['birthday']  = 'birthdate';
        return $mapping_rule;
    }

    private function loadEmail($mapping_rule) {
        $mapping_rule['email'] = 'email';
        return $mapping_rule;
    }
    private function loadAddress($mapping_rule) {
        $mapping_rule['street']  = 'street_address';
        $mapping_rule['city']    = 'locality';
        $mapping_rule['zipcode'] = 'postal_code';
        $mapping_rule['country'] = 'country';
        return $mapping_rule;
    }
    private function loadPhone($mapping_rule) {
        $mapping_rule['phone_home'] = 'phone_number';
        return $mapping_rule;
    }
}
