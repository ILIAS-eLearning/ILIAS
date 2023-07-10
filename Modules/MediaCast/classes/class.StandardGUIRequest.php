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

namespace ILIAS\MediaCast;

use ILIAS\Repository\BaseGUIRequest;

class StandardGUIRequest
{
    use BaseGUIRequest;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery,
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ) {
        $this->initRequest(
            $http,
            $refinery,
            $passed_query_params,
            $passed_post_data
        );
    }

    public function getRefId(): int
    {
        return $this->int("ref_id");
    }

    public function getUserId(): int
    {
        return $this->int("user_id");
    }

    public function getItemId(): int
    {
        return $this->int("item_id");
    }

    public function getPurpose(): string
    {
        return $this->str("purpose");
    }

    public function getPresentation(): bool
    {
        return (bool) $this->int("presentation");
    }

    public function getEvent(): string
    {
        return $this->str("event");
    }

    public function getPlayer(): string
    {
        return $this->str("player");
    }

    public function getMobId(): int
    {
        return $this->int("mob_id");
    }

    public function getAutoplay(): bool
    {
        return (bool) $this->int("autoplay");
    }

    /** @return int[] */
    public function getItemIds(): array
    {
        return $this->intArray("item_id");
    }

    public function getTargetPurpose(): string
    {
        return $this->str("target_purpose");
    }

    public function getTargetFormat(): string
    {
        return $this->str("target_format");
    }

    public function getSeconds(): int
    {
        return $this->int("sec");
    }

    public function getSettingsPurpose(string $purpose): string
    {
        return $this->str($purpose);
    }

    public function getDefaultAccess(): string
    {
        return $this->str("defaultaccess");
    }

    public function getMimeTypes(): string
    {
        return $this->str("mimetypes");
    }
}
