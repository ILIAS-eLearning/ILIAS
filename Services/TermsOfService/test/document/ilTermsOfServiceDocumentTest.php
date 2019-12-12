<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceDocumentTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentTest extends \ilTermsOfServiceCriterionBaseTest
{
    /**
     * @return array
     */
    public function criteriaAssignmentProvider() : array
    {
        $criterionAssignment1 = $this
            ->getMockBuilder(\ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getCriterionValue', 'getCriterionId', 'store', 'delete'])
            ->getMock();

        $criterionAssignment1
            ->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $criterionAssignment1
            ->expects($this->any())
            ->method('getCriterionId')
            ->willReturn('usr_global_role');

        $criterionAssignment1
            ->expects($this->any())
            ->method('getCriterionValue')
            ->willReturn($this->getCriterionConfig(['role_id' => 4]));

        $criterionAssignment2 = $this
            ->getMockBuilder(\ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getCriterionValue', 'getCriterionId', 'store', 'delete'])
            ->getMock();

        $criterionAssignment2
            ->expects($this->any())
            ->method('getId')
            ->willReturn(2);

        $criterionAssignment2
            ->expects($this->any())
            ->method('getCriterionId')
            ->willReturn('usr_language');

        $criterionAssignment2
            ->expects($this->any())
            ->method('getCriterionValue')
            ->willReturn($this->getCriterionConfig(['lng' => 'de']));

        $criterionAssignment3 = $this
            ->getMockBuilder(\ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getCriterionValue', 'getCriterionId', 'store', 'delete'])
            ->getMock();

        $criterionAssignment3
            ->expects($this->any())
            ->method('getId')
            ->willReturn(3);

        $criterionAssignment3
            ->expects($this->any())
            ->method('getCriterionId')
            ->willReturn('usr_global_role');

        $criterionAssignment3
            ->expects($this->any())
            ->method('getCriterionValue')
            ->willReturn($this->getCriterionConfig(['role_id' => 6]));

        return [
            [$criterionAssignment1, $criterionAssignment2, $criterionAssignment3]
        ];
    }

    /**
     * @dataProvider criteriaAssignmentProvider
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
     */
    public function testDocumentModelCanBeBuiltFromArrayWithAttachedCriteriaBeingRead(
        \ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
        \ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
        \ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
    ) {
        $documentConnector = $this->getMockBuilder(\arConnector::class)->getMock();
        $criterionConnector = $this->getMockBuilder(\arConnector::class)->getMock();

        $criterionConnector
            ->expects($this->once())
            ->method('readSet')
            ->willReturnCallback(function () use ($criterionAssignment1, $criterionAssignment2, $criterionAssignment3) {
                return [[
                    'id' => $criterionAssignment1->getId(),
                    'doc_id' => 4711,
                    'criterion_id' => $criterionAssignment1->getCriterionId(),
                    'criterion_value' => $criterionAssignment1->getCriterionValue(),
                ], [
                    'id' => $criterionAssignment2->getId(),
                    'doc_id' => 4711,
                    'criterion_id' => $criterionAssignment2->getCriterionId(),
                    'criterion_value' => $criterionAssignment2->getCriterionValue(),
                ], [
                    'id' => $criterionAssignment3->getId(),
                    'doc_id' => 4711,
                    'criterion_id' => $criterionAssignment3->getCriterionId(),
                    'criterion_value' => $criterionAssignment3->getCriterionValue(),
                ]];
            });

        \arConnectorMap::register(new \ilTermsOfServiceDocument(), $documentConnector);
        \arConnectorMap::register(new \ilTermsOfServiceDocumentCriterionAssignment(), $criterionConnector);

        $document = new \ilTermsOfServiceDocument();

        $document->buildFromArray([
            'id' => 4711,
            'title' => 'phpunit',
        ]);

        $this->assertCount(3, $document->criteria());
    }

    /**
     * @dataProvider criteriaAssignmentProvider
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
     */
    public function testDocumentModelCanCreatedByIdWithAttachedCriteriaBeingRead(
        \ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
        \ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
        \ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
    ) {
        $documentConnector = $this->getMockBuilder(\arConnector::class)->getMock();
        $criterionConnector = $this->getMockBuilder(\arConnector::class)->getMock();

        $documentConnector
            ->expects($this->once())
            ->method('read')
            ->willReturnCallback(function () {
                $object = new stdClass();

                $object->id = 4711;
                $object->title =  'phpunit';
                $object->creation_ts =  time();
                $object->modification_ts =  time();
                $object->owner_usr_id = 6;
                $object->last_modified_usr_id = 6;
                $object->sorting = 10;
                $object->text = 'HelloWorld';

                return [$object];
            });

        $criterionConnector
            ->expects($this->once())
            ->method('readSet')
            ->willReturnCallback(function () use ($criterionAssignment1, $criterionAssignment2, $criterionAssignment3) {
                return [[
                    'id' => $criterionAssignment1->getId(),
                    'doc_id' => 4711,
                    'criterion_id' => $criterionAssignment1->getCriterionId(),
                    'criterion_value' => $criterionAssignment1->getCriterionValue(),
                ], [
                    'id' => $criterionAssignment2->getId(),
                    'doc_id' => 4711,
                    'criterion_id' => $criterionAssignment2->getCriterionId(),
                    'criterion_value' => $criterionAssignment2->getCriterionValue(),
                ], [
                    'id' => $criterionAssignment3->getId(),
                    'doc_id' => 4711,
                    'criterion_id' => $criterionAssignment3->getCriterionId(),
                    'criterion_value' => $criterionAssignment3->getCriterionValue(),
                ]];
            });

        \arConnectorMap::register(new \ilTermsOfServiceDocument(), $documentConnector);
        \arConnectorMap::register(new \ilTermsOfServiceDocumentCriterionAssignment(), $criterionConnector);

        $document = new \ilTermsOfServiceDocument(4711);

        $this->assertCount(3, $document->criteria());
    }

    /**
     * @dataProvider criteriaAssignmentProvider
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
     */
    public function testCriteriaCanBeAttachedToDocument(
        \ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
        \ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
        \ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
    ) {
        $document = new \ilTermsOfServiceDocument();
        $document->attachCriterion($criterionAssignment1);
        $document->attachCriterion($criterionAssignment2);
        $document->attachCriterion($criterionAssignment3);

        $this->assertCount(3, $document->criteria());
    }

    /**
     * @dataProvider criteriaAssignmentProvider
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
     */
    public function testCriteriaCanBeDetachedFromDocument(
        \ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
        \ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
        \ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
    ) {
        $document = new \ilTermsOfServiceDocument();
        $document->attachCriterion($criterionAssignment1);
        $document->attachCriterion($criterionAssignment2);
        $document->attachCriterion($criterionAssignment3);

        $this->assertCount(3, $document->criteria());

        $document->detachCriterion($criterionAssignment2);

        $this->assertCount(2, $document->criteria());
    }

    /**
     * @dataProvider criteriaAssignmentProvider
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
     */
    public function testCriteriaCanBeAttachedToAndDetachedFromDocumentPersistently(
        \ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
        \ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
        \ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
    ) {
        $documentConnector = $this->getMockBuilder(\arConnector::class)->getMock();

        $document = new \ilTermsOfServiceDocument();

        $documentConnector
            ->expects($this->exactly(2))
            ->method('affectedRows')
            ->willReturnOnConsecutiveCalls(0, 1);

        $documentConnector
            ->expects($this->once())
            ->method('nextId')
            ->willReturn(1);

        $documentConnector
            ->expects($this->once())
            ->method('create');

        \arConnectorMap::register($document, $documentConnector);

        $document->attachCriterion($criterionAssignment1);
        $document->attachCriterion($criterionAssignment2);
        $document->attachCriterion($criterionAssignment3);

        $criterionAssignment1
            ->expects($this->exactly(2))
            ->method('store');

        $criterionAssignment1
            ->expects($this->once())
            ->method('delete');

        $criterionAssignment2
            ->expects($this->once())
            ->method('store');

        $criterionAssignment2
            ->expects($this->once())
            ->method('delete');

        $criterionAssignment3
            ->expects($this->exactly(2))
            ->method('store');

        $criterionAssignment3
            ->expects($this->once())
            ->method('delete');

        $this->assertCount(3, $document->criteria());

        $document->store(); // 1 / 2 / 3

        $document->detachCriterion($criterionAssignment2);

        $document->store();  // 1 / 3

        $this->assertCount(2, $document->criteria());

        $document->detachCriterion($criterionAssignment1);
        $document->detachCriterion($criterionAssignment3);

        $this->assertCount(0, $document->criteria());

        $document->delete();
    }

    /**
     * @expectedException \ilTermsOfServiceDuplicateCriterionAssignmentException
     */
    public function testExceptionIsRaisedWhenAttachingDuplicateCriteria()
    {
        $this->assertException(\ilTermsOfServiceDuplicateCriterionAssignmentException::class);

        $criterionAssignment1 = $this
            ->getMockBuilder(\ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getCriterionValue', 'getCriterionId'])
            ->getMock();

        $criterionAssignment1
            ->expects($this->any())
            ->method('getId')
            ->willReturn(0);

        $criterionAssignment1
            ->expects($this->any())
            ->method('getCriterionId')
            ->willReturn('usr_global_role');

        $criterionAssignment1
            ->expects($this->any())
            ->method('getCriterionValue')
            ->willReturn($this->getCriterionConfig(['role_id' => 4]));

        $criterionAssignment2 = $this
            ->getMockBuilder(\ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getCriterionValue', 'getCriterionId'])
            ->getMock();

        $criterionAssignment2
            ->expects($this->any())
            ->method('getId')
            ->willReturn(0);

        $criterionAssignment2
            ->expects($this->any())
            ->method('getCriterionId')
            ->willReturn('usr_global_role');

        $criterionAssignment2
            ->expects($this->any())
            ->method('getCriterionValue')
            ->willReturn($this->getCriterionConfig(['role_id' => 4]));

        $document = new \ilTermsOfServiceDocument();

        $document->attachCriterion($criterionAssignment1);
        $document->attachCriterion($criterionAssignment2);
    }

    /**
     * @expectedException \ilTermsOfServiceDuplicateCriterionAssignmentException
     */
    public function testExceptionIsRaisedWhenAttachingDuplicateCriteriaEvenWithDifferentIds()
    {
        $this->assertException(\ilTermsOfServiceDuplicateCriterionAssignmentException::class);

        $criterionAssignment1 = $this
            ->getMockBuilder(\ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getCriterionValue', 'getCriterionId'])
            ->getMock();

        $criterionAssignment1
            ->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $criterionAssignment1
            ->expects($this->any())
            ->method('getCriterionId')
            ->willReturn('usr_global_role');

        $criterionAssignment1
            ->expects($this->any())
            ->method('getCriterionValue')
            ->willReturn($this->getCriterionConfig(['role_id' => 4]));

        $criterionAssignment2 = $this
            ->getMockBuilder(\ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getCriterionValue', 'getCriterionId'])
            ->getMock();

        $criterionAssignment2
            ->expects($this->any())
            ->method('getId')
            ->willReturn(2);

        $criterionAssignment2
            ->expects($this->any())
            ->method('getCriterionId')
            ->willReturn('usr_global_role');

        $criterionAssignment2
            ->expects($this->any())
            ->method('getCriterionValue')
            ->willReturn($this->getCriterionConfig(['role_id' => 4]));

        $document = new \ilTermsOfServiceDocument();

        $document->attachCriterion($criterionAssignment1);
        $document->attachCriterion($criterionAssignment2);
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testExceptionIsRaisedWhenRemovingUnknownCriterion()
    {
        $this->assertException(\OutOfBoundsException::class);

        $criterionAssignment1 = $this
            ->getMockBuilder(\ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getCriterionValue', 'getCriterionId'])
            ->getMock();

        $criterionAssignment1
            ->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $criterionAssignment1
            ->expects($this->any())
            ->method('getCriterionId')
            ->willReturn('usr_global_role');

        $criterionAssignment1
            ->expects($this->any())
            ->method('getCriterionValue')
            ->willReturn($this->getCriterionConfig(['role_id' => 4]));

        $document = new \ilTermsOfServiceDocument();

        $document->detachCriterion($criterionAssignment1);
    }
}
