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

namespace ILIAS\Search\Presentation\Result\User;

use ILIAS\User\Profile\Profile as UserProfile;
use ILIAS\User\Profile\Fields\Field;
use ilObjUser;
use ILIAS\User\Profile\Fields\Standard\FirstName;
use ILIAS\User\Profile\Fields\Standard\LastName;
use ilLanguage;
use ILIAS\User\Profile\Fields\Standard\Alias;
use ILIAS\User\Profile\Fields\Standard\Roles;
use ILIAS\User\Profile\Fields\Standard\Avatar;
use ILIAS\User\Profile\Fields\Standard\Location;
use ilUserUtil;
use ILIAS\User\Profile\Fields\Standard\OrganisationalUnits;
use ILIAS\Data\URI;
use ILIAS\Data\Factory as DataFactory;
use ilCtrlInterface;
use ilSearchControllerGUI;
use ilLuceneUserSearchGUI;
use ILIAS\User\Profile\PublicProfileGUI;
use ilDatePresentation;
use ilDate;

class PropertiesAggregatorImpl implements PropertiesAggregator
{
    protected const array EXCLUDED_EXTRA_FIELDS = [
        FirstName::class,
        LastName::class,
        Alias::class,
        Roles::class,
        OrganisationalUnits::class,
        Avatar::class,
        Location::class
    ];

    public function __construct(
        protected ilLanguage $lng,
        protected UserProfile $user_profile,
        protected ilCtrlInterface $ctrl,
        protected DataFactory $data_factory
    ) {
    }

    public function fetchForUsers(int ...$user_ids): PropertiesCollection
    {
        $fields = $this->user_profile->getFields(
            [],
            self::EXCLUDED_EXTRA_FIELDS
        );
        $result = [];
        foreach ($user_ids as $user_id) {
            $result[] = $this->fetchForUser($user_id, ...$fields);
        }
        return new PropertiesCollectionImpl($result);
    }

    protected function fetchForUser(int $user_id, Field ...$fields): Properties
    {
        $user = new ilObjUser($user_id);

        $presentable_name = ilUserUtil::getNamePresentation($user_id);
        $login = $user->getLogin();
        $avatar_path = $user->getPersonalPicturePath();
        $profile_link = $this->buildProfileLink($user_id);

        $other_fields = [];
        foreach ($fields as $field) {
            if (!$field->isPublishedByUser($user)) {
                continue;
            }
            $other_fields[$field->getLabel($this->lng)] = $this->getPresentableFieldValueForUser($field, $user);
        }
        return new PropertiesImpl(
            $user_id,
            $presentable_name,
            $login,
            $profile_link,
            $avatar_path,
            $other_fields
        );
    }

    protected function buildProfileLink(int $user_id): URI
    {
        $this->ctrl->setParameterByClass(ilLuceneUserSearchGUI::class, 'user_id', $user_id);
        $ctrl_target = $this->ctrl->getLinkTargetByClass(
            [ilSearchControllerGUI::class, ilLuceneUserSearchGUI::class, PublicProfileGUI::class],
            PublicProfileGUI::DEFAULT_CMD
        );
        $this->ctrl->clearParameterByClass(ilLuceneUserSearchGUI::class, 'user_id');
        return $this->data_factory->uri(
            rtrim(ILIAS_HTTP_PATH, '/') . '/' . $ctrl_target
        );
    }

    protected function getPresentableFieldValueForUser(Field $field, ilObjUser $user): string
    {
        $value = $field->retrieveValueFromUser($user);

        if ($field->getIdentifier() === 'birthday') {
            $relative_dates = ilDatePresentation::useRelativeDates();
            ilDatePresentation::setUseRelativeDates(false);
            $presentable_date = ilDatePresentation::formatDate(new ilDate($value, IL_CAL_DATE));
            ilDatePresentation::setUseRelativeDates($relative_dates);
            return $presentable_date;
        }

        if ($field->getIdentifier() === 'gender') {
            return $this->lng->txt('gender_' . $value);
        }

        return is_array($value) ? implode(', ', $value) : (string) $value;
    }
}
