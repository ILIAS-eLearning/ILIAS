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

namespace ILIAS\User\Badges;

use ILIAS\Language\Language;
use ILIAS\User\Profile\PersonalProfileGUI;
use Psr\Http\Message\RequestInterface;

class ProfileBadgeGUI implements \ilBadgeTypeGUI
{
    public function __construct(
        private readonly Language $lng,
        private readonly RequestInterface $request
    ) {
    }

    public function initConfigForm(
        \ilPropertyFormGUI $form,
        int $parent_ref_id
    ): void {
        $fields = new \ilCheckboxGroupInputGUI(
            $this->lng->txt('profile'),
            'profile'
        );
        $form->addItem($fields);

        (new PersonalProfileGUI())->showPublicProfileFields($form, [], $fields, true);
    }

    public function importConfigToForm(
        \ilPropertyFormGUI $form,
        array $config
    ): void {
        if (!is_array($config['profile'])) {
            return;
        }

        foreach ($form->getItemByPostVar('profile')->getSubItems() as $field) {
            foreach ($config['profile'] as $id) {
                if ($field->getPostVar() === $id) {
                    $field->setChecked(true);
                    break;
                }
            }
        }
    }

    /**
     * @return array<string>
     */
    public function getConfigFromForm(
        \ilPropertyFormGUI $form
    ): array {
        return ['profile' => array_reduce(
            array_keys($this->request->getParsedBody()),
            static function (array $c, string $v): array {
                if (str_starts_with($v, 'chk_')) {
                    $c[] = $v;
                }
                return $c;
            },
            []
        )];
    }

    public function validateForm(\ilPropertyFormGUI $form): bool
    {
        return true;
    }
}
