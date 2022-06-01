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
 
use PHPUnit\Framework\TestCase;

class ilDBStepExecutionDBTest extends TestCase
{
    public const CLASS_NAME_200 = "01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789";
    public const CLASS_NAME_201 = "012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890";

    protected function setUp() : void
    {
        $this->db = $this->createMock(\ilDBInterface::class);
        $this->execution_db = new \ilDBStepExecutionDB($this->db, fn () => new \DateTime());
    }

    public function testStartedThrowsOnLongClassName() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->execution_db->started(self::CLASS_NAME_201, 1);
    }

    public function testFinishedThrowsOnLongClassName() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->execution_db->finished(self::CLASS_NAME_201, 1);
    }

    public function testGetLastStartedStepThrowsOnLongClassName() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->execution_db->getLastStartedStep(self::CLASS_NAME_201);
    }

    public function testGetLastFinishedStepThrowsOnLongClassName() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->execution_db->getLastFinishedStep(self::CLASS_NAME_201);
    }

    public function testStartedThrowsOnStartStepNotLargerThenLastFinishedStep() : void
    {
        $STEP = 1;
        $NOW = "2021-08-12 13:37:23.111111";
        
        $execution_db = $this->getMockBuilder(\ilDBStepExecutionDB::class)
            ->onlyMethods(["getLastStartedStep", "getLastFinishedStep"])
            ->setConstructorArgs([$this->db, fn () => new \DateTime($NOW)])
            ->getMock();

        $execution_db->expects($this->once())
            ->method("getLastFinishedStep")
            ->with(self::CLASS_NAME_200)
            ->willReturn(2);

        $this->expectException(\RuntimeException::class);

        $execution_db->started(self::CLASS_NAME_200, $STEP);
    }

    public function testStartedThrowsWhenLastStepNotFinished() : void
    {
        $NOW = "2021-08-12 13:37:23.111111";
        
        $execution_db = $this->getMockBuilder(\ilDBStepExecutionDB::class)
            ->onlyMethods(["getLastStartedStep", "getLastFinishedStep"])
            ->setConstructorArgs([$this->db, fn () => new \DateTime($NOW)])
            ->getMock();

        $execution_db->expects($this->once())
            ->method("getLastFinishedStep")
            ->with(self::CLASS_NAME_200)
            ->willReturn(1);


        $execution_db->expects($this->once())
            ->method("getLastStartedStep")
            ->with(self::CLASS_NAME_200)
            ->willReturn(2);

        $this->expectException(\RuntimeException::class);

        $execution_db->started(self::CLASS_NAME_200, 3);
    }

    public function testFinishedThrowsWhenOtherStepThenLastIsFinished() : void
    {
        $STEP = 1;
        $NOW = "2021-08-12 13:37:23.111111";

        $execution_db = $this->getMockBuilder(\ilDBStepExecutionDB::class)
            ->onlyMethods(["getLastStartedStep", "getLastFinishedStep"])
            ->setConstructorArgs([$this->db, fn () => new \DateTime($NOW)])
            ->getMock();

        $execution_db->expects($this->once())
            ->method("getLastStartedStep")
            ->with(self::CLASS_NAME_200)
            ->willReturn(2);

        $this->expectException(\RuntimeException::class);

        $execution_db->finished(self::CLASS_NAME_200, $STEP);
    }

    public function testGetLastStartedStepStartsWithZero() : void
    {
        $result = $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->db
            ->method("query")
            ->willReturn($result);
        $this->db
            ->method("fetchAssoc")
            ->willReturn([ilDBStepExecutionDB::FIELD_STEP => null]);

        $this->assertEquals(0, $this->execution_db->getLastStartedStep(self::CLASS_NAME_200));
    }

    public function testGetLastFinishedStepStartsWithZero() : void
    {
        $result = $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->db
            ->method("query")
            ->willReturn($result);
        $this->db
            ->method("fetchAssoc")
            ->willReturn([ilDBStepExecutionDB::FIELD_STEP => null]);

        $this->assertEquals(0, $this->execution_db->getLastFinishedStep(self::CLASS_NAME_200));
    }

    public function testStartedWritesToDB() : void
    {
        $STEP = 2;
        $NOW = "2021-08-12 13:37:23.111111";

        $execution_db = $this->getMockBuilder(\ilDBStepExecutionDB::class)
            ->onlyMethods(["getLastStartedStep", "getLastFinishedStep"])
            ->setConstructorArgs([$this->db, fn () => new \DateTime($NOW)])
            ->getMock();

        $execution_db->expects($this->once())
            ->method("getLastStartedStep")
            ->with(self::CLASS_NAME_200)
            ->willReturn(1);

        $execution_db->expects($this->once())
            ->method("getLastFinishedStep")
            ->with(self::CLASS_NAME_200)
            ->willReturn(1);

        $this->db->expects($this->once())
            ->method("insert")
            ->with(
                ilDBStepExecutionDB::TABLE_NAME,
                [
                    ilDBStepExecutionDB::FIELD_CLASS => ["text", self::CLASS_NAME_200],
                    ilDBStepExecutionDB::FIELD_STEP => ["integer", $STEP],
                    ilDBStepExecutionDB::FIELD_STARTED => ["text", $NOW]
                ]
            );

        $execution_db->started(self::CLASS_NAME_200, $STEP);
    }

    public function testFinishedWritesToDB() : void
    {
        $STEP = 2;
        $NOW = "2021-08-12 13:37:23.222222";

        $execution_db = $this->getMockBuilder(\ilDBStepExecutionDB::class)
            ->onlyMethods(["getLastStartedStep", "getLastFinishedStep"])
            ->setConstructorArgs([$this->db, fn () => new \DateTime($NOW)])
            ->getMock();

        $execution_db->expects($this->once())
            ->method("getLastStartedStep")
            ->with(self::CLASS_NAME_200)
            ->willReturn(2);

        $this->db->expects($this->once())
            ->method("update")
            ->with(
                ilDBStepExecutionDB::TABLE_NAME,
                [
                    ilDBStepExecutionDB::FIELD_FINISHED => ["text", $NOW]
                ],
                [
                    ilDBStepExecutionDB::FIELD_CLASS => ["text", self::CLASS_NAME_200],
                    ilDBStepExecutionDB::FIELD_STEP => ["integer", $STEP]
                ]
            );

        $execution_db->finished(self::CLASS_NAME_200, $STEP);
    }

    public function testGetLastStartedStepQueriesDB() : void
    {
        $STEP = 23;

        $this->db->expects($this->once())
            ->method("quote")
            ->withConsecutive(
                [self::CLASS_NAME_200, "text"],
            )
            ->willReturnOnConsecutiveCalls(
                "CLASS"
            );

        $result = $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->db->expects($this->once())
            ->method("query")
            ->with(
                "SELECT MAX(" . ilDBStepExecutionDB::FIELD_STEP . ") AS " . ilDBStepExecutionDB::FIELD_STEP .
                " FROM " . ilDBStepExecutionDB::TABLE_NAME .
                " WHERE " . ilDBStepExecutionDB::FIELD_CLASS . " = CLASS"
            )
            ->willReturn($result);
        $this->db->expects($this->once())
            ->method("fetchAssoc")
            ->willReturn([ilDBStepExecutionDB::FIELD_STEP => $STEP]);

        $this->assertEquals($STEP, $this->execution_db->getLastStartedStep(self::CLASS_NAME_200));
    }

    public function testGetLastFinishedStepQueriesDB() : void
    {
        $STEP = 23;

        $this->db->expects($this->once())
            ->method("quote")
            ->withConsecutive(
                [self::CLASS_NAME_200, "text"],
            )
            ->willReturnOnConsecutiveCalls(
                "CLASS"
            );

        $result = $this->getMockBuilder(ilDBStatement::class)->getMock();
        $this->db->expects($this->once())
            ->method("query")
            ->with(
                "SELECT MAX(" . ilDBStepExecutionDB::FIELD_STEP . ") AS " . ilDBStepExecutionDB::FIELD_STEP .
                " FROM " . ilDBStepExecutionDB::TABLE_NAME .
                " WHERE " . ilDBStepExecutionDB::FIELD_CLASS . " = CLASS" .
                " AND " . ilDBStepExecutionDB::FIELD_FINISHED . " IS NOT NULL"
            )
            ->willReturn($result);
        $this->db->expects($this->once())
            ->method("fetchAssoc")
            ->willReturn([ilDBStepExecutionDB::FIELD_STEP => $STEP]);

        $this->assertEquals($STEP, $this->execution_db->getLastFinishedStep(self::CLASS_NAME_200));
    }
}
