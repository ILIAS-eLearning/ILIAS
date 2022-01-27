<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Style\Content;

/**
 * Content style internal service
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalService
{
    /**
     * @var DataFactory
     */
    protected $data;

    /**
     * @var RepoFactory
     */
    protected $repo;

    /**
     * @var ManagerFactory
     */
    protected $manager;

    /**
     * @var UIFactory
     */
    protected $ui;

    /**
     * Constructor
     */
    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->data = new DataFactory();
        $this->repo = new RepoFactory(
            $DIC->database(),
            $this->data,
            $DIC->filesystem()->web(),
            $DIC->upload()
        );
        $this->manager = new ManagerFactory(
            $DIC->rbac()->system(),
            $this->repo,
            $DIC->user()
        );
        $this->ui = new UIFactory(
            $DIC->ui(),
            $DIC->tabs(),
            $DIC->toolbar(),
            $DIC["ilLocator"],
            $DIC->ctrl(),
            $DIC->language(),
            $DIC["ilHelp"],
            $DIC->http()->request(),
            $DIC->refinery()
        );
    }

    /**
     * @return DataFactory
     */
    public function data() : DataFactory
    {
        return $this->data;
    }

    /**
     * @return RepoFactory
     */
    public function repo() : RepoFactory
    {
        return $this->repo;
    }

    /**
     * @return ManagerFactory
     */
    public function manager() : ManagerFactory
    {
        return $this->manager;
    }

    /**
     * @return UIFactory
     */
    public function ui() : UIFactory
    {
        return $this->ui;
    }
}
