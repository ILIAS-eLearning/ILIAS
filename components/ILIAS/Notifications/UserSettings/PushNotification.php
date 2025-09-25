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

namespace ILIAS\Notifications\UserSettings;

use Exception;
use ILIAS\Setup\NullConfig;
use ILIAS\User\Settings\SettingDefinition;
use ILIAS\User\Settings\AvailablePages;
use ILIAS\User\Settings\AvailableSections;
use ILIAS\Language\Language as Language;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Input;
use ILIAS\Refinery\Factory as Refinery;
use ilFormPropertyGUI;
use ilSetting;
use ilObjUser;
use PushNotificationObjective;

use function ILIAS\UI\examples\Layout\Page\Standard\ui;

class PushNotification implements SettingDefinition
{
    public function getIdentifier(): string
    {
        return 'push_notification';
    }

    public function isAvailable(): bool
    {
        return (new ilSetting('notifications'))->get('enable_push') === '1';
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt($this->getIdentifier());
    }

    public function getSettingsPage(): AvailablePages
    {
        return AvailablePages::MainSettings;
    }

    public function getSection(): AvailableSections
    {
        return AvailableSections::Communication;
    }

    public function getInput(
        FieldFactory $field_factory,
        Language $lng,
        Refinery $refinery,
        ilSetting $settings,
        ?ilObjUser $user = null
    ): Input {
        $lng->loadLanguageModule('notifications_adm');

        $pref = $user ? $this->retrieveValueFromUser($user) : [];
        $provider = [];

        foreach ((new PushNotificationObjective(new NullConfig()))->getArtifacts() as $value) {
            $provider[get_class($value)] = $field_factory->checkbox(
                $value->getName($lng),
                $value->getDescription($lng),
            )->withValue(in_array(get_class($value), $pref));
        }

        return $field_factory->section(
            $provider,
            $lng->txt('push_settings')
        );
    }

    public function getLegacyInput(Language $lng, ilSetting $settings, ?ilObjUser $user = null): ilFormPropertyGUI
    {
        throw new Exception('This Setting does not provide an legacy Input.');
    }

    public function getDefaultValueForDisplay(Language $lng, ilSetting $settings): null
    {
        return null;
    }

    public function hasUserPersonalizedSetting(ilSetting $settings, ilObjUser $user): bool
    {
        return $user->getPref('push_notification_provider') !== null;
    }

    public function persistUserInput(ilObjUser $user, mixed $input): ilObjUser
    {
        $active = [];
        foreach ($input ?? [] as $key => $value) {
            if ($value === true) {
                $active[] = $key;
            }
        }
        $user->setPref('push_notification_provider', json_encode($active));
        $user->update();
        return $user;
    }

    public function retrieveValueFromUser(ilObjUser $user): ?array
    {
        return json_decode($user->getPref('push_notification_provider') ?? '[]');
    }
}
