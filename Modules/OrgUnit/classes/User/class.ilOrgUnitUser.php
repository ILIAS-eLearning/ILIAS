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
 ********************************************************************
 */

namespace OrgUnit\User;

use function PHPUnit\Framework\throwException;
use Exception;

class ilOrgUnitUser
{

    /** @var self[] */
    protected static array $instances;
    private int $user_id;
    private string $login;
    private string $email;
    private string $second_email;
    /**
     * @var \ilOrgUnitPosition[]
     */
    private array $org_unit_positions = [];
    /**
     * @var ilOrgUnitUser[]
     */
    private array $superiors = [];

    private function __construct(int $user_id, string $login, string $email, string $second_email)
    {
        $this->user_id = $user_id;
        $this->login = $login;
        $this->email = $email;
        $this->second_email = $second_email;
    }

    /**
     * @throws Exception
     */
    public static function getInstanceById(int $user_id) : self
    {
        if (null === static::$instances[$user_id]) {
            $org_unit_user_repository = new ilOrgUnitUserRepository();
            $orgUnitUser = $org_unit_user_repository->getOrgUnitUser($user_id);
            if ($orgUnitUser === null) {
                throw new Exception('no OrgUnitUser found with user_id ' . $user_id);
            }

            static::$instances[$user_id] = $org_unit_user_repository->getOrgUnitUser($user_id);
        }

        return static::$instances[$user_id];
    }

    public static function getInstance(int $user_id, string $login, string $email, string $second_email) : self
    {
        if (!isset(static::$instances) ||
            !array_key_exists($user_id, static::$instances) ||
            is_null(static::$instances[$user_id])
            ) {
            static::$instances[$user_id] = new self($user_id, $login, $email, $second_email);
        }

        return static::$instances[$user_id];
    }


    public function addSuperior(ilOrgUnitUser $org_unit_user) : void
    {
        $this->superiors[] = $org_unit_user;
    }

    public function addPositions(\ilOrgUnitPosition $org_unit_position)
    {
        $this->org_unit_positions[] = $org_unit_position;
    }

    /**
     * @return ilOrgUnitUser[]
     * eager loading
     */
    public function getSuperiors() : array
    {
        if (count($this->superiors) === 0) {
            $this->loadSuperiors();
        }

        return $this->superiors;
    }

    public function loadSuperiors() : void
    {
        $org_unit_user_repository = new ilOrgUnitUserRepository();
        $org_unit_user_repository->loadSuperiors([$this->user_id]);
    }

    /**
     * @return \ilOrgUnitPosition[]
     * eager loading
     */
    public function getOrgUnitPositions() : array
    {
        if (count($this->org_unit_positions) == 0) {
            $this->loadOrgUnitPositions();
        }

        return $this->org_unit_positions;
    }

    /**
     * @return \ilOrgUnitPosition[]
     * eager loading
     */
    protected function loadOrgUnitPositions() : array
    {
        $org_unit_user_repository = new ilOrgUnitUserRepository();
        $org_unit_user_repository->loadPositions([$this->user_id]);
    }

    public function getUserId() : int
    {
        return $this->user_id;
    }

    public function getLogin() : string
    {
        return $this->login;
    }

    public function getEmail() : string
    {
        return $this->email;
    }

    public function getSecondEmail() : string
    {
        return $this->second_email;
    }

    public function setSecondEmail(string $second_email) : void
    {
        $this->second_email = $second_email;
    }
}
