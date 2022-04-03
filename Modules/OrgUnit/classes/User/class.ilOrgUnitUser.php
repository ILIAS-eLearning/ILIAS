<?php

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
    final public static function getInstanceById(int $user_id) : self
    {
        if (null === static::$instances[$user_id]) {
            $org_unit_user_repository = new ilOrgUnitUserRepository();
            $orgUnitUser = $org_unit_user_repository->getOrgUnitUser($user_id);
            if($orgUnitUser === null) {
                throw new Exception('no OrgUnitUser found with user_id '.$user_id);
            }

            static::$instances[$user_id] = $org_unit_user_repository->getOrgUnitUser($user_id);
        }

        return static::$instances[$user_id];
    }

    final public static function getInstance(int $user_id, string $login, string $email, string $second_email) : self
    {
        if (null === static::$instances[$user_id]) {
            static::$instances[$user_id] = new self($user_id, $login, $email, $second_email);
        }

        return static::$instances[$user_id];
    }


    final public function addSuperior(ilOrgUnitUser $org_unit_user) : void
    {
        $this->superiors[] = $org_unit_user;
    }

    final public function addPositions(\ilOrgUnitPosition $org_unit_position)
    {
        $this->org_unit_positions[] = $org_unit_position;
    }

    /**
     * @return ilOrgUnitUser[]
     * eager loading
     */
    final public function getSuperiors() : array
    {
        if (count($this->superiors) === 0) {
            $this->loadSuperiors();
        }

        return $this->superiors;
    }

    final public function loadSuperiors() : void
    {
        $org_unit_user_repository = new ilOrgUnitUserRepository();
        $org_unit_user_repository->loadSuperiors([$this->user_id]);
    }

    /**
     * @return \ilOrgUnitPosition[]
     * eager loading
     */
    final public function getOrgUnitPositions() : array
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

    final public function getUserId() : int
    {
        return $this->user_id;
    }

    final  public function getLogin() : string
    {
        return $this->login;
    }

    final public function getEmail() : string
    {
        return $this->email;
    }

    final public function getSecondEmail() : string
    {
        return $this->second_email;
    }

    final public function setSecondEmail(string $second_email) : void
    {
        $this->second_email = $second_email;
    }
}
