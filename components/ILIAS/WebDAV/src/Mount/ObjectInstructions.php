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

namespace ILIAS\WebDAV\Mount;

use ilObject;
use ilSetting;

class ObjectInstructions extends BaseInstructions
{
    protected int $obj_id;
    protected string $obj_title;

    public function __construct(
        Repository $a_repo,
        UriBuilder $a_uri_builder,
        ilSetting $a_settings,
        string $language,
        protected int $ref_id
    ) {
        parent::__construct($a_repo, $a_uri_builder, $a_settings, $language);

        // TODO: Change this to be more unit testable!
        $this->obj_id = ilObject::_lookupObjectId($this->ref_id);
        $this->obj_title = ilObject::_lookupTitle($this->obj_id);
    }

    protected function fillPlaceholdersForMountInstructions(array $mount_instructions): array
    {
        foreach ($mount_instructions as $title => $mount_instruction) {
            $mount_instruction = str_replace(
                [
                    '[WEBFOLDER_ID]',
                    '[WEBFOLDER_TITLE]',
                    '[WEBFOLDER_URI]',
                    '[WEBFOLDER_URI_KONQUEROR]',
                    '[WEBFOLDER_URI_NAUTILUS]',
                    '[ADMIN_MAIL]',
                ],
                [
                    (string) $this->ref_id,
                    $this->obj_title,
                    $this->uri_builder->getWebDavDefaultUri($this->ref_id),
                    $this->uri_builder->getWebDavKonquerorUri($this->ref_id),
                    $this->uri_builder->getWebDavNautilusUri($this->ref_id),
                    $this->settings->get('admin_email'),
                ],
                $mount_instruction
            );

            $mount_instructions[$title] = $mount_instruction;
        }

        return $mount_instructions;
    }
}
