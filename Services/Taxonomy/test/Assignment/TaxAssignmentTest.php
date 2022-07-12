<?php

use PHPUnit\Framework\TestCase;

/**
 * Test peer reviews
 * @author Alexander Killing <killing@leifos.de>
 */
class TaxAssignmentTest extends TestCase
{
    //protected $backupGlobals = false;

    protected function setUp() : void
    {
        parent::setUp();
    }

    protected function tearDown() : void
    {
    }

    /**
     * Test if each rater has $num_assignments peers
     */
    public function testNewTaxAssignment() : void
    {
        $database = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $tax_assignment = new ilTaxNodeAssignment(
            "comp_id",
            1,
            "item_type",
            2,
            $database
        );

        $this->assertEquals(
            $tax_assignment->getComponentId(),
            "comp_id"
        );

        $this->assertEquals(
            $tax_assignment->getObjectId(),
            1
        );

        $this->assertEquals(
            $tax_assignment->getItemType(),
            "item_type"
        );

        $this->assertEquals(
            $tax_assignment->getTaxonomyId(),
            2
        );
    }
}
