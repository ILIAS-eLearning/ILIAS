<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Unit tests for single choice questions
*
* @author Maximilian Becker <mbecker@databay.de>
* @version $Id$
*
*
* @ingroup ServicesTree
*/
class ilassMarkSchemaTest extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;

    protected function setUp()
    {
        if (defined('ILIAS_PHPUNIT_CONTEXT')) {
            include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
            ilUnitUtil::performInitialisation();
        } else {
            chdir(dirname(__FILE__));
            chdir('../../../');
        }
        // Arrange
        include_once './Modules/Test/classes/class.assMarkSchema.php';
        $this->ass_mark_schema = new ASS_MarkSchema();
    }
        
    /**
     * Test constructor
     */
    public function testConstructor()
    {
        // Arrange
        $expected = is_array(array());
            
        // Act
        $actual = is_array($this->ass_mark_schema->mark_steps);
            
        // Assert
        $this->assertEquals(
            $actual,
            $expected,
            "Constructor failed, mark_steps not an array."
        );
    }

    /**
     * Test for createSimpleSchema using defaults.
     */
    public function testCreateSimpleSchemaDefaults()
    {
        // Arrange
            
            
        $txt_failed_short = "failed";
        $txt_failed_official = "failed";
        $percentage_failed = 0;
        $failed_passed = 0;
        $txt_passed_short = "passed";
        $txt_passed_official = "passed";
        $percentage_passed = 50;
        $passed_passed = 1;
            
        // Act
        $this->ass_mark_schema->createSimpleSchema();
        $marks = $this->ass_mark_schema->mark_steps;
            
        $failed = $marks[0];
        $passed = $marks[1];
            
        // Assert
        $this->assertEquals(
            $failed->getShortName(),
            $txt_failed_short,
            'Failed on $txt_failed_short'
        );
        $this->assertEquals(
            $failed->getOfficialName(),
            $txt_failed_official,
            'Failed on $txt_failed_official'
        );
        $this->assertEquals(
            $failed->getMinimumLevel(),
            $percentage_failed,
            'Failed on $percentage_failed'
        );
        $this->assertEquals(
            $failed->getPassed(),
            $failed_passed,
            'Failed on $failed_passed'
        );

        $this->assertEquals(
            $passed->getShortName(),
            $txt_passed_short,
            'Failed on $txt_passed_short'
        );
        $this->assertEquals(
            $passed->getOfficialName(),
            $txt_passed_official,
            'Failed on $txt_passed_official'
        );
        $this->assertEquals(
            $passed->getMinimumLevel(),
            $percentage_passed,
            'Failed on $percetage_passed'
        );
        $this->assertEquals(
            $passed->getPassed(),
            $passed_passed,
            'Failed on $passed_passed'
        );
    }
 
    /**
     * Test for createSimpleSchema using custom values.
     */
    public function testCreateSimpleSchemaCustom()
    {
        // Arrange
        $txt_failed_short = "failed";
        $txt_failed_official = "failed";
        $percentage_failed = 0;
        $failed_passed = 0;
        $txt_passed_short = "passed";
        $txt_passed_official = "passed";
        $percentage_passed = 50;
        $passed_passed = 1;
            
        // Act
        $this->ass_mark_schema->createSimpleSchema(
            $txt_failed_short,
            $txt_failed_official,
            $percentage_failed,
            $failed_passed,
            $txt_passed_short,
            $txt_passed_official,
            $percentage_passed,
            $passed_passed
        );

        $marks = $this->ass_mark_schema->mark_steps;
            
        $failed = $marks[0];
        $passed = $marks[1];
            
        // Assert
        $this->assertEquals(
            $failed->getShortName(),
            $txt_failed_short,
            'Failed on $txt_failed_short'
        );
        $this->assertEquals(
            $failed->getOfficialName(),
            $txt_failed_official,
            'Failed on $txt_failed_official'
        );
        $this->assertEquals(
            $failed->getMinimumLevel(),
            $percentage_failed,
            'Failed on $percentage_failed'
        );
        $this->assertEquals(
            $failed->getPassed(),
            $failed_passed,
            'Failed on $failed_passed'
        );

        $this->assertEquals(
            $passed->getShortName(),
            $txt_passed_short,
            'Failed on $txt_passed_short'
        );
        $this->assertEquals(
            $passed->getOfficialName(),
            $txt_passed_official,
            'Failed on $txt_passed_official'
        );
        $this->assertEquals(
            $passed->getMinimumLevel(),
            $percentage_passed,
            'Failed on $percetage_passed'
        );
        $this->assertEquals(
            $passed->getPassed(),
            $passed_passed,
            'Failed on $passed_passed'
        );
    }

    /**
     * Test for flush()
     */
    public function testFlush()
    {
        // Arrange
        $expected = is_array(array());
        $this->ass_mark_schema->mark_steps = "a string";
        $this->assertEquals($this->ass_mark_schema->mark_steps, "a string");
        $this->ass_mark_schema->flush();
            
        // Act
        $actual = is_array($this->ass_mark_schema->mark_steps);
            
        // Assert
        $this->assertEquals(
            $actual,
            $expected,
            "Method failed, mark_steps not an array."
        );
    }
        
    /**
     * Test for addMarkStep()
     */
    public function testAddMarkStep()
    {
        // Arrange
        $this->ass_mark_schema->flush();
        $txt_short = "";
        $txt_official = "";
        $percentage = 0;
        $passed = 0;

        // Act
        $this->ass_mark_schema->addMarkStep();
        $mark_schema = $this->ass_mark_schema->mark_steps;
        $mark_step = $mark_schema[0];
            
        // Assert
        $this->assertEquals(
            $mark_step->getShortName(),
            $txt_short,
            'Failed on $txt_failed_short'
        );
        $this->assertEquals(
            $mark_step->getOfficialName(),
            $txt_official,
            'Failed on $txt_failed_official'
        );
        $this->assertEquals(
            $mark_step->getMinimumLevel(),
            $percentage,
            'Failed on $percentage_failed'
        );
        $this->assertEquals(
            $mark_step->getPassed(),
            $passed,
            'Failed on $failed_passed'
        );
    }
    
    public function testSaveToDb_regular()
    {
        /*
        // Arrange
        $ildb_stub = $this->createMock('ilDBInterface');

        $ildb_stub->expects($this->any())
            ->method('query')
            ->will($this->returnValue('foo'));

        $ildb_stub->expects($this->any())
            ->method('numRows')
            ->will($this->returnValue(1));

        $db_result_1 = array('cmi_node_id' => 8);
        $db_result_2 = array('cmi_node_id' => 10);
        $db_result_3 = array('cmi_node_id' => 12);
        $db_result_4 = array('cmi_node_id' => 14);

        $ildb_stub->expects($this->any())
            ->method('fetchAssoc')
            ->will($this->onConsecutiveCalls($db_result_1, $db_result_2, $db_result_3, $db_result_4));
        */
    }
}
