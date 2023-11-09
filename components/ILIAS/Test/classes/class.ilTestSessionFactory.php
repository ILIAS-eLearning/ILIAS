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

/**
 * Factory for test session
 * @author         Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 * @package components\ILIAS/Test
 */
class ilTestSessionFactory
{
    /**
     * singleton instances of test sessions
     *
     * @var array<ilTestSession>
     */
    private $testSession = [];

    public function __construct(
        private ilObjTest $test_obj,
        private ilDBInterface $db,
        private ilObjUser $user
    ) {
    }

    /**
     * temporarily bugfix for resetting the state of this singleton
     * smeyer
     * --> BH: not required anymore
     */
    public function reset(): void
    {
        $this->testSession = [];
    }

    /**
     * Creates and returns an instance of a test sequence
     * that corresponds to the current test mode
     */
    public function getSession(?int $active_id = null): ilTestSession
    {
        if ($active_id === null ||
            $this->testSession === [] ||
            !array_key_exists($active_id, $this->testSession) ||
            $this->testSession[$active_id] === null
        ) {
            $testSession = $this->getNewTestSessionObject();

            $testSession->setRefId($this->test_obj->getRefId());
            $testSession->setTestId($this->test_obj->getTestId());

            if ($active_id) {
                $testSession->loadFromDb($active_id);
                $this->testSession[$active_id] = $testSession;
            } else {
                $testSession->loadTestSession(
                    $this->test_obj->getTestId(),
                    $this->user->getId(),
                    $testSession->getAccessCodeFromSession()
                );

                return $testSession;
            }
        }

        return $this->testSession[$active_id];
    }

    /**
     * @todo: Björn, we also need to handle the anonymous user here
     * @param integer $userId
     * @return ilTestSession
     */
    public function getSessionByUserId(int $user_id): ilTestSession
    {
        if (!isset($this->testSession[$this->buildCacheKey($user_id)])) {
            $testSession = $this->getNewTestSessionObject();

            $testSession->setRefId($this->test_obj->getRefId());
            $testSession->setTestId($this->test_obj->getTestId());

            $testSession->loadTestSession($this->test_obj->getTestId(), $user_id);

            $this->testSession[$this->buildCacheKey($user_id)] = $testSession;
        }

        return $this->testSession[$this->buildCacheKey($user_id)];
    }

    private function getNewTestSessionObject(): ilTestSession
    {
        return new ilTestSession($this->db, $this->user);
    }

    /**
     * @param $userId
     * @return string
     */
    private function buildCacheKey(int $user_id): string
    {
        $user_id_string = (string) $user_id;
        return "{$this->test_obj->getTestId()}::{$user_id_string}";
    }
}
