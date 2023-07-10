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

namespace ILIAS\Awareness\User;

use ILIAS\Awareness;
use ILIAS\Awareness\InternalDataService;
use ILIAS\Awareness\InternalRepoService;
use ILIAS\Awareness\InternalDomainService;

/**
 * Collects users from all providers
 * @author Alexander Killing <killing@leifos.de>
 */
class Collector
{
    protected static ?array $online_users = null;
    /** @var int[] */
    protected static array $online_user_ids = array();
    protected \ilRbacReview $rbacreview;
    protected Awareness\AdminManager $admin_manager;
    protected Awareness\InternalDataService $data_service;
    protected ProviderFactory $provider_factory;
    protected Collection $collection;
    protected array $collections;
    protected int $user_id;
    protected int $ref_id;
    protected \ilSetting $settings;
    protected InternalRepoService $repo_service;

    public function __construct(
        int $user_id,
        int $ref_id,
        InternalDataService $data_service,
        InternalRepoService $repo_service,
        InternalDomainService $domain_service
    ) {
        $this->user_id = $user_id;
        $this->settings = $domain_service->settings();
        $this->provider_factory = $domain_service
            ->userProvider();
        $this->data_service = $data_service;
        $this->admin_manager = $domain_service
            ->admin($ref_id);
        $this->rbacreview = $domain_service->rbac()->review();
        $this->repo_service = $repo_service;
    }

    public static function getOnlineUsers(): array
    {
        if (self::$online_users === null) {
            self::$online_user_ids = array();
            self::$online_users = array();
            foreach (\ilObjUser::_getUsersOnline() as $u) {
                // ask context $u["context"] if it supports pushMessages
                if ($u["context"] &&
                    \ilContext::directCall($u["context"], "supportsPushMessages")) {
                    self::$online_users[$u["user_id"]] = $u;
                    self::$online_user_ids[] = $u["user_id"];
                }
            }
        }
        return self::$online_users;
    }


    /**
     * Collect users
     */
    public function collectUsers(bool $a_online_only = false): array
    {
        $rbacreview = $this->rbacreview;

        $this->collections = array();

        $awrn_logger = \ilLoggerFactory::getLogger('awrn');

        $awrn_logger->debug("Start, Online Only: " . $a_online_only . ", Current User: " . $this->user_id);

        self::getOnlineUsers();
        $all_users = array();
        foreach ($this->provider_factory->getAllProviders() as $prov) {
            $awrn_logger->debug("Provider: " . $prov->getProviderId() . ", Activation Mode: " . $this->admin_manager->getActivationMode($prov->getProviderId()) . ", Current User: " . $this->user_id);

            // overall collection of users
            $collection = $this->data_service->userCollection();

            $provider_active = $this->admin_manager->isProviderActivated($prov->getProviderId());
            $provider_includes_offline = $this->admin_manager->includesProviderOfflineUsers($prov->getProviderId());

            if ($provider_active) {
                $online_users = null;
                if (!$provider_includes_offline || $a_online_only) {
                    $awrn_logger->debug("Provider: " . $prov->getProviderId() . ", Online Filter Users: " . count(self::$online_user_ids) . ", Current User: " . $this->user_id);
                    $online_users = self::$online_user_ids;
                }

                $coll = $this->collectUsersFromProvider($prov, $online_users);
                $awrn_logger->debug("Provider: " . $prov->getProviderId() . ", Collected Users: " . count($coll) . ", Current User: " . $this->user_id);

                foreach ($coll->getUsers() as $user_id) {
                    // filter out the anonymous user
                    if ($user_id == ANONYMOUS_USER_ID) {
                        continue;
                    }
                    // filter out current user
                    if ($user_id == $this->user_id) {
                        continue;
                    }

                    $awrn_logger->debug("Current User: " . $this->user_id . ", " .
                        "Provider: " . $prov->getProviderId() . ", Collected User: " . $user_id);

                    // cross check online, filter out offline users (if necessary)
                    if ((!$a_online_only && $provider_includes_offline)
                        || in_array($user_id, self::$online_user_ids)) {
                        $collection->addUser($user_id);
                        if (!in_array($user_id, $all_users)) {
                            $all_users[] = $user_id;
                        }
                    }
                }
            }
            $this->collections[] = array(
                "uc_title" => $prov->getTitle(),
                "highlighted" => $prov->isHighlighted(),
                "collection" => $collection
            );
        }

        $remove_users = array();

        if ($this->settings->get("hide_own_online_status") === "n") {
            // remove all users with hide_own_online_status "y"
            foreach (\ilObjUser::getUserSubsetByPreferenceValue($all_users, "hide_own_online_status", "y") as $u) {
                $remove_users[] = $u;
            }
        } else {
            // remove all, except user with hide_own_online_status "n"
            $show_users = \ilObjUser::getUserSubsetByPreferenceValue($all_users, "hide_own_online_status", "n");
            $remove_users = array_filter($all_users, function ($i) use ($show_users) {
                return !in_array($i, $show_users);
            });
        }

        // remove all users that have not accepted the terms of service yet
        if (\ilTermsOfServiceHelper::isEnabled()) {
            foreach (\ilObjUser::getUsersAgreed(false, $all_users) as $u) {
                if ($u != SYSTEM_USER_ID && !$rbacreview->isAssigned($u, SYSTEM_ROLE_ID)) {
                    //if ($u != SYSTEM_USER_ID)
                    $remove_users[] = $u;
                }
            }
        }

        $this->removeUsersFromCollections($remove_users);

        return $this->collections;
    }

    public function collectUsersFromProvider(Provider $prov, ?array $online_users): Collection
    {
        $coll = $this->data_service->userCollection();
        foreach ($prov->getInitialUserSet($online_users) as $user_id) {
            if ((is_null($online_users) || in_array($user_id, $online_users))) {
                $coll->addUser($user_id);
            }
        }
        return $coll;
    }

    /**
     * Remove users from collection
     * @param int[] $a_remove_users array of user IDs
     */
    protected function removeUsersFromCollections(array $a_remove_users): void
    {
        foreach ($this->collections as $c) {
            reset($a_remove_users);
            foreach ($a_remove_users as $u) {
                $c["collection"]->removeUser($u);
            }
        }
    }
}
