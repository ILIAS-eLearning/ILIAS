<?php declare(strict_types=1);

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

use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTermsOfServiceDocumentTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentTest extends ilTermsOfServiceCriterionBaseTest
{
    public function criteriaAssignmentProvider() : array
    {
        $criterionAssignment1 = $this
            ->getMockBuilder(ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCriterionValue', 'getCriterionId', 'store', 'delete'])
            ->addMethods(['getId'])
            ->getMock();

        $criterionAssignment1
            ->method('getId')
            ->willReturn(1);

        $criterionAssignment1
            ->method('getCriterionId')
            ->willReturn('usr_global_role');

        $criterionAssignment1
            ->method('getCriterionValue')
            ->willReturn($this->getCriterionConfig(['role_id' => 4]));

        $criterionAssignment2 = $this
            ->getMockBuilder(ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCriterionValue', 'getCriterionId', 'store', 'delete'])
            ->addMethods(['getId'])
            ->getMock();

        $criterionAssignment2
            ->method('getId')
            ->willReturn(2);

        $criterionAssignment2
            ->method('getCriterionId')
            ->willReturn('usr_language');

        $criterionAssignment2
            ->method('getCriterionValue')
            ->willReturn($this->getCriterionConfig(['lng' => 'de']));

        $criterionAssignment3 = $this
            ->getMockBuilder(ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCriterionValue', 'getCriterionId', 'store', 'delete'])
            ->addMethods(['getId'])
            ->getMock();

        $criterionAssignment3
            ->method('getId')
            ->willReturn(3);

        $criterionAssignment3
            ->method('getCriterionId')
            ->willReturn('usr_global_role');

        $criterionAssignment3
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
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
    ) : void {
        $documentConnector = $this->getMockBuilder(arConnector::class)->getMock();
        $criterionConnector = $this->getMockBuilder(arConnector::class)->getMock();

        $criterionConnector
            ->expects($this->once())
            ->method('readSet')
            ->willReturnCallback(function () use ($criterionAssignment1, $criterionAssignment2, $criterionAssignment3) {
                return [
                    [
                        'id' => $criterionAssignment1->getId(),
                        'doc_id' => 4711,
                        'criterion_id' => $criterionAssignment1->getCriterionId(),
                        'criterion_value' => $criterionAssignment1->getCriterionValue()->toJson(),
                    ],
                    [
                        'id' => $criterionAssignment2->getId(),
                        'doc_id' => 4711,
                        'criterion_id' => $criterionAssignment2->getCriterionId(),
                        'criterion_value' => $criterionAssignment2->getCriterionValue()->toJson(),
                    ],
                    [
                        'id' => $criterionAssignment3->getId(),
                        'doc_id' => 4711,
                        'criterion_id' => $criterionAssignment3->getCriterionId(),
                        'criterion_value' => $criterionAssignment3->getCriterionValue()->toJson(),
                    ]
                ];
            });

        arConnectorMap::register(new ilTermsOfServiceDocument(), $documentConnector);
        arConnectorMap::register(new ilTermsOfServiceDocumentCriterionAssignment(), $criterionConnector);

        $document = new ilTermsOfServiceDocument();

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
    public function testDocumentModelCanBeCreatedByIdWithAttachedCriteriaBeingRead(
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
    ) : void {
        $documentConnector = $this->getMockBuilder(arConnector::class)->getMock();
        $criterionConnector = $this->getMockBuilder(arConnector::class)->getMock();

        $documentConnector
            ->expects($this->once())
            ->method('read')
            ->willReturnCallback(function () {
                $object = new stdClass();

                $object->id = 4711;
                $object->title = 'phpunit';
                $object->creation_ts = time();
                $object->modification_ts = time();
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
                return [
                    [
                        'id' => $criterionAssignment1->getId(),
                        'doc_id' => 4711,
                        'criterion_id' => $criterionAssignment1->getCriterionId(),
                        'criterion_value' => $criterionAssignment1->getCriterionValue()->toJson(),
                    ],
                    [
                        'id' => $criterionAssignment2->getId(),
                        'doc_id' => 4711,
                        'criterion_id' => $criterionAssignment2->getCriterionId(),
                        'criterion_value' => $criterionAssignment2->getCriterionValue()->toJson(),
                    ],
                    [
                        'id' => $criterionAssignment3->getId(),
                        'doc_id' => 4711,
                        'criterion_id' => $criterionAssignment3->getCriterionId(),
                        'criterion_value' => $criterionAssignment3->getCriterionValue()->toJson(),
                    ]
                ];
            });

        arConnectorMap::register(new ilTermsOfServiceDocument(), $documentConnector);
        arConnectorMap::register(new ilTermsOfServiceDocumentCriterionAssignment(), $criterionConnector);

        $document = new ilTermsOfServiceDocument(4711);

        $this->assertCount(3, $document->criteria());
    }

    /**
     * @dataProvider criteriaAssignmentProvider
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2
     * @param ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
     */
    public function testCriteriaCanBeAttachedToDocument(
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
    ) : void {
        $document = new ilTermsOfServiceDocument();
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
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
    ) : void {
        $document = new ilTermsOfServiceDocument();
        $document->attachCriterion($criterionAssignment1);
        $document->attachCriterion($criterionAssignment2);
        $document->attachCriterion($criterionAssignment3);

        $this->assertCount(3, $document->criteria());

        $document->detachCriterion($criterionAssignment2);
        $this->assertCount(2, $document->criteria());
    }

    /**
     * @dataProvider criteriaAssignmentProvider
     * @param ilTermsOfServiceDocumentCriterionAssignment&MockObject $criterionAssignment1
     * @param ilTermsOfServiceDocumentCriterionAssignment&MockObject $criterionAssignment2
     * @param ilTermsOfServiceDocumentCriterionAssignment&MockObject $criterionAssignment3
     */
    public function testCriteriaCanBeAttachedToAndDetachedFromDocumentPersistently(
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment1,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment2,
        ilTermsOfServiceDocumentCriterionAssignment $criterionAssignment3
    ) : void {
        $documentConnector = $this->getMockBuilder(arConnector::class)->getMock();

        $document = new ilTermsOfServiceDocument();

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

        arConnectorMap::register($document, $documentConnector);

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

    public function testExceptionIsRaisedWhenAttachingDuplicateCriteria() : void
    {
        $this->expectException(ilTermsOfServiceDuplicateCriterionAssignmentException::class);

        $criterionAssignment1 = $this
            ->getMockBuilder(ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCriterionValue', 'getCriterionId'])
            ->addMethods(['getId'])
            ->getMock();

        $criterionAssignment1
            ->method('getId')
            ->willReturn(0);

        $criterionAssignment1
            ->method('getCriterionId')
            ->willReturn('usr_global_role');

        $criterionAssignment1
            ->method('getCriterionValue')
            ->willReturn($this->getCriterionConfig(['role_id' => 4]));

        $criterionAssignment2 = $this
            ->getMockBuilder(ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCriterionValue', 'getCriterionId'])
            ->addMethods(['getId'])
            ->getMock();

        $criterionAssignment2
            ->method('getId')
            ->willReturn(0);

        $criterionAssignment2
            ->method('getCriterionId')
            ->willReturn('usr_global_role');

        $criterionAssignment2
            ->method('getCriterionValue')
            ->willReturn($this->getCriterionConfig(['role_id' => 4]));

        $document = new ilTermsOfServiceDocument();

        $document->attachCriterion($criterionAssignment1);
        $document->attachCriterion($criterionAssignment2);
    }

    public function testExceptionIsRaisedWhenAttachingDuplicateCriteriaEvenWithDifferentIds() : void
    {
        $this->expectException(ilTermsOfServiceDuplicateCriterionAssignmentException::class);

        $criterionAssignment1 = $this
            ->getMockBuilder(ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCriterionValue', 'getCriterionId'])
            ->addMethods(['getId'])
            ->getMock();

        $criterionAssignment1
            ->method('getId')
            ->willReturn(1);

        $criterionAssignment1
            ->method('getCriterionId')
            ->willReturn('usr_global_role');

        $criterionAssignment1
            ->method('getCriterionValue')
            ->willReturn($this->getCriterionConfig(['role_id' => 4]));

        $criterionAssignment2 = $this
            ->getMockBuilder(ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCriterionValue', 'getCriterionId'])
            ->addMethods(['getId'])
            ->getMock();

        $criterionAssignment2
            ->method('getId')
            ->willReturn(2);

        $criterionAssignment2
            ->method('getCriterionId')
            ->willReturn('usr_global_role');

        $criterionAssignment2
            ->method('getCriterionValue')
            ->willReturn($this->getCriterionConfig(['role_id' => 4]));

        $document = new ilTermsOfServiceDocument();

        $document->attachCriterion($criterionAssignment1);
        $document->attachCriterion($criterionAssignment2);
    }

    public function testExceptionIsRaisedWhenRemovingUnknownCriterion() : void
    {
        $this->expectException(OutOfBoundsException::class);

        $criterionAssignment1 = $this
            ->getMockBuilder(ilTermsOfServiceDocumentCriterionAssignment::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCriterionValue', 'getCriterionId'])
            ->addMethods(['getId'])
            ->getMock();

        $criterionAssignment1
            ->method('getId')
            ->willReturn(1);

        $criterionAssignment1
            ->method('getCriterionId')
            ->willReturn('usr_global_role');

        $criterionAssignment1
            ->method('getCriterionValue')
            ->willReturn($this->getCriterionConfig(['role_id' => 4]));

        $document = new ilTermsOfServiceDocument();

        $document->detachCriterion($criterionAssignment1);
    }
}
