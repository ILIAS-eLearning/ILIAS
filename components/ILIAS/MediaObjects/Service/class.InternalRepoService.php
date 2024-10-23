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

namespace ILIAS\MediaObjects;

use ILIAS\MediaObjects\ImageMap\ImageMapEditSessionRepository;
use ILIAS\MediaObjects\Usage\UsageDBRepository;
use ILIAS\Exercise\IRSS\IRSSWrapper;

class InternalRepoService
{
    protected static array $instance = [];
    protected IRSSWrapper $irss_wrapper;

    public function __construct(
        protected InternalDataService $data,
        protected \ilDBInterface $db
    ) {
        $this->irss_wrapper = new IRSSWrapper(new \ILIAS\Exercise\InternalDataService());
    }

    public function imageMap(): ImageMapEditSessionRepository
    {
        return new ImageMapEditSessionRepository();
    }

    public function usage(): UsageDBRepository
    {
        return self::$instance["usage"] ??= new UsageDBRepository($this->db);
    }

    public function mediaObject(): MediaObjectRepository
    {
        return self::$instance["media_object"] ??=
            new MediaObjectRepository(
                $this->db,
                $this->irss_wrapper
            );
    }

}
