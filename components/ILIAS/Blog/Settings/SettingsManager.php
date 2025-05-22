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

namespace ILIAS\Blog\Settings;

use ILIAS\Blog\InternalDataService;
use ILIAS\Blog\InternalRepoService;
use ILIAS\Blog\InternalDomainService;

class SettingsManager
{
    public function __construct(
        protected InternalDataService $data,
        protected InternalRepoService $repo,
        protected InternalDomainService $domain
    ) {
    }


    public function create(Settings $settings): void
    {
        $this->repo->settings()->create($settings);
    }

    public function update(Settings $settings): void
    {
        $this->repo->settings()->update($settings);
    }

    public function getByObjId(int $id): ?Settings
    {
        return $this->repo->settings()->getByObjId($id);
    }

    public function clone($from_id, $to_id): void
    {
        $settings = $this->repo->settings()->getByObjId($from_id);
        $settings = $settings->withId($to_id);
        $this->update($settings);
    }

    public function saveOrder(int $id, array $order): void
    {
        $this->repo->settings()->saveOrder($id, $order);
    }

    public function getOrderingOptions(
        Settings $settings,
        bool $in_repository
    ): array {
        $lng = $this->domain->lng();
        $order_options = [];
        foreach ($settings->getOrder() as $item) {
            $order_options[$item] = $lng->txt("blog_" . $item);
        }

        $type = "navigation";
        if (!isset($order_options[$type])) {
            $order_options[$type] = $lng->txt("blog_" . $type);
        }

        if ($in_repository) {
            $type = "authors";
            if (!isset($order_options[$type])) {
                $order_options[$type] = $lng->txt("blog_" . $type);
            }
        }

        $type = "keywords";
        if (!isset($order_options[$type])) {
            $order_options[$type] = $lng->txt("blog_" . $type);
        }

        return $order_options;
    }
}
