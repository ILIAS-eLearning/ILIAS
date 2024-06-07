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

namespace ILIAS\MetaData\XML\Copyright;

use ILIAS\MetaData\Copyright\RepositoryInterface as CopyrightRepository;

class CopyrightHandler implements CopyrightHandlerInterface
{
    protected CopyrightRepository $copyright_repository;

    public function __construct(
        CopyrightRepository $copyright_repository
    ) {
        $this->copyright_repository = $copyright_repository;
    }

    public function copyrightForExport(string $copyright): string
    {
        return \ilMDCopyrightSelectionEntry::_lookupCopyright($copyright, true);
    }

    public function copyrightFromExport(string $copyright): string
    {
        $entry_id = \ilMDCopyrightSelectionEntry::lookupCopyrightByText($copyright);
        if (!$entry_id) {
            return $copyright;
        } else {
            return \ilMDCopyrightSelectionEntry::createIdentifier($entry_id);
        }
    }

    public function copyrightAsString(string $copyright): string
    {
        $entry_id = \ilMDCopyrightSelectionEntry::_extractEntryId($copyright);
        if (!$entry_id) {
            return $copyright;
        } else {
            $entry_data = $this->copyright_repository->getEntry($entry_id)->copyrightData();
            $full_name = $entry_data->fullName();
            $link = $entry_data->link();

            $res = [];
            if ($full_name !== '') {
                $res[] = $full_name;
            }
            if ($link !== null) {
                $res[] = (string) $link;
            }

            return implode(' ', $res);
        }
    }
}
