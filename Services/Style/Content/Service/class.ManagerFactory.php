<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Style\Content;

use ILIAS\Style\Content\Access\StyleAccessManager;

/**
 * Content style internal manager service
 * @author Alexander Killing <killing@leifos.de>
 */
class ManagerFactory
{
    /**
     * @var RepoFactory
     */
    protected $repo_service;

    /**
     * @var \ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var \ilObjUser
     */
    protected $user;

    public function __construct(
        \ilRbacSystem $rbacsystem,
        RepoFactory $repo_service,
        \ilObjUser $user
    ) {
        $this->rbacsystem = $rbacsystem;
        $this->repo_service = $repo_service;
        $this->user = $user;
    }

    // access manager
    public function access(
        int $ref_id = 0,
        int $user_id = 0
    ) : StyleAccessManager {
        return new StyleAccessManager(
            $this->rbacsystem,
            $ref_id,
            $user_id
        );
    }


    /**
     * @param int                $style_id
     * @param StyleAccessManager $access_manager
     * @return CharacteristicManager
     */
    public function characteristic(
        int $style_id,
        StyleAccessManager $access_manager
    ) : CharacteristicManager {
        return new CharacteristicManager(
            $style_id,
            $access_manager,
            $this->repo_service->characteristic(),
            $this->repo_service->characteristicCopyPaste(),
            $this->repo_service->color(),
            $this->user
        );
    }

    /**
     * @return ColorManager
     */
    public function color(
        int $style_id,
        StyleAccessManager $access_manager
    ) : ColorManager {
        return new ColorManager(
            $style_id,
            $access_manager,
            $this->repo_service->characteristic(),
            $this->repo_service->color()
        );
    }

    public function image(
        int $style_id,
        StyleAccessManager $access_manager
    ) : ImageManager {
        return new ImageManager(
            $style_id,
            $access_manager,
            $this->repo_service->image()
        );
    }
}
