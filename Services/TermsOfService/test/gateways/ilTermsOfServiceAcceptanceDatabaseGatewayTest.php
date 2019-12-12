<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceAcceptanceDatabaseGatewayTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceDatabaseGatewayTest extends \ilTermsOfServiceBaseTest
{
    /**
     *
     */
    public function testInstanceCanBeCreated()
    {
        $database = $this->getMockBuilder(\ilDBInterface::class)->getMock();
        $gateway = new \ilTermsOfServiceAcceptanceDatabaseGateway($database);

        $this->assertInstanceOf(\ilTermsOfServiceAcceptanceDatabaseGateway::class, $gateway);
    }

    /**
     *
     */
    public function testAcceptanceIsTrackedAndCreatesANewTermsOfServicesVersionIfNecessary()
    {
        $entity = new \ilTermsOfServiceAcceptanceEntity();
        $entity->withUserId(666);
        $entity->withDocumentId(4711);
        $entity->withTitle('Document PHP Unit');
        $entity->withSerializedCriteria('');
        $entity->withText('PHP Unit');
        $entity->withTimestamp(time());
        $entity->withHash(md5($entity->getText()));

        $expected_id = 4711;

        $database = $this->getMockBuilder(\ilDBInterface::class)->getMock();
        $result = $this->getMockBuilder(\ilDBStatement::class)->getMock();

        $database
            ->expects($this->once())
            ->method('queryF')
            ->with(
                'SELECT id FROM tos_versions WHERE hash = %s AND doc_id = %s',
                ['text', 'integer'],
                [$entity->getHash(), $entity->getDocumentId()]
            )->will($this->returnValue($result));
    
        $database
            ->expects($this->once())
            ->method('numRows')
            ->with($result)->
            will($this->returnValue(0));

        $database
            ->expects($this->once())
            ->method('nextId')
            ->with('tos_versions')
            ->will($this->returnValue($expected_id));

        $expectedVersions = [
            'id' => ['integer', $expected_id],
            'doc_id' => ['integer', $entity->getDocumentId()],
            'title' => ['text', $entity->getTitle()],
            'text' => ['clob', $entity->getText()],
            'hash' => ['text', $entity->getHash()],
            'ts' => ['integer', $entity->getTimestamp()]
        ];
        $expectedTracking = [
            'tosv_id' => ['integer', $expected_id],
            'usr_id' => ['integer', $entity->getUserId()],
            'criteria' => ['clob', $entity->getSerializedCriteria()],
            'ts' => ['integer', $entity->getTimestamp()]
        ];

        $database
            ->expects($this->exactly(2))
            ->method('insert')
            ->with(
                $this->logicalOr('tos_versions', 'tos_acceptance_track'),
                $this->logicalOr($expectedVersions, $expectedTracking)
            );

        $gateway = new \ilTermsOfServiceAcceptanceDatabaseGateway($database);
        $gateway->trackAcceptance($entity);
    }

    /**
     *
     */
    public function testAcceptanceIsTrackedAndRefersToAnExistingTermsOfServicesVersion()
    {
        $entity = new \ilTermsOfServiceAcceptanceEntity();
        $entity->withUserId(666);
        $entity->withDocumentId(4711);
        $entity->withTitle('Document PHP Unit');
        $entity->withSerializedCriteria('');
        $entity->withText('PHP Unit');
        $entity->withTimestamp(time());
        $entity->withHash(md5($entity->getText()));

        $expected_id = 4711;

        $database = $this->getMockBuilder(\ilDBInterface::class)->getMock();
        $result = $this->getMockBuilder(\ilDBStatement::class)->getMock();

        $database
            ->expects($this->once())
            ->method('queryF')
            ->with(
                'SELECT id FROM tos_versions WHERE hash = %s AND doc_id = %s',
                ['text', 'integer'],
                [$entity->getHash(), $entity->getDocumentId()]
            )->will($this->returnValue($result));

        $database
            ->expects($this->once())
            ->method('numRows')
            ->with($result)
            ->will($this->returnValue(1));

        $database
            ->expects($this->once())
            ->method('fetchAssoc')
            ->with($result)
            ->will($this->returnValue(['id' => $expected_id]));

        $expectedTracking = [
            'tosv_id' => ['integer', $expected_id],
            'usr_id' => ['integer', $entity->getUserId()],
            'criteria' => ['clob', $entity->getSerializedCriteria()],
            'ts' => ['integer', $entity->getTimestamp()]
        ];
        $database
            ->expects($this->once())
            ->method('insert')
            ->with('tos_acceptance_track', $expectedTracking);

        $gateway = new \ilTermsOfServiceAcceptanceDatabaseGateway($database);
        $gateway->trackAcceptance($entity);
    }

    /**
     *
     */
    public function testLatestAcceptanceOfUserCanBeLoaded()
    {
        $entity = new \ilTermsOfServiceAcceptanceEntity();

        $expected = [
            'id' => 4711,
            'usr_id' => 6,
            'title' => 'Document PHP Unit',
            'doc_id' => 4711,
            'criteria' => '',
            'text' => 'PHP Unit',
            'hash' => md5('PHP Unit'),
            'accepted_ts' => time()
        ];

        $database = $this->getMockBuilder(\ilDBInterface::class)->getMock();
        $database
            ->expects($this->once())
            ->method('fetchAssoc')
            ->will($this->onConsecutiveCalls($expected));

        $gateway = new \ilTermsOfServiceAcceptanceDatabaseGateway($database);
        $entity = $gateway->loadCurrentAcceptanceOfUser($entity);

        $this->assertEquals($expected['id'], $entity->getId());
        $this->assertEquals($expected['usr_id'], $entity->getUserId());
        $this->assertEquals($expected['doc_id'], $entity->getDocumentId());
        $this->assertEquals($expected['title'], $entity->getTitle());
        $this->assertEquals($expected['criteria'], $entity->getSerializedCriteria());
        $this->assertEquals($expected['text'], $entity->getText());
        $this->assertEquals($expected['accepted_ts'], $entity->getTimestamp());
        $this->assertEquals($expected['hash'], $entity->getHash());
    }

    /**
     *
     */
    public function testAcceptanceHistoryOfAUserCanBeDeleted()
    {
        $entity = new \ilTermsOfServiceAcceptanceEntity();
        $entity->withUserId(4711);

        $database = $this->getMockBuilder(\ilDBInterface::class)->getMock();

        $database
            ->expects($this->once())
            ->method('quote')
            ->with($entity->getUserId(), 'integer')
            ->will($this->returnValue($entity->getUserId()));

        $database
            ->expects($this->once())
            ->method('manipulate')
            ->with('DELETE FROM tos_acceptance_track WHERE usr_id = ' . $entity->getUserId());

        $gateway = new \ilTermsOfServiceAcceptanceDatabaseGateway($database);
        $gateway->deleteAcceptanceHistoryByUser($entity);
    }

    /**
     *
     */
    public function testAcceptanceHistoryRecordCanBeLoadedById()
    {
        $entity = new \ilTermsOfServiceAcceptanceEntity();

        $expected = [
            'id' => 4711,
            'title' => 'Document PHP Unit',
            'doc_id' => 4711,
            'criteria' => '',
            'text' => 'PHP Unit',
            'hash' => md5('PHP Unit'),
        ];

        $database = $this->getMockBuilder(\ilDBInterface::class)->getMock();
        $database
            ->expects($this->once())
            ->method('fetchAssoc')
            ->will($this->onConsecutiveCalls($expected));

        $gateway = new \ilTermsOfServiceAcceptanceDatabaseGateway($database);
        $entity = $gateway->loadById($entity);

        $this->assertEquals($expected['id'], $entity->getId());
        $this->assertEquals($expected['doc_id'], $entity->getDocumentId());
        $this->assertEquals($expected['title'], $entity->getTitle());
        $this->assertEquals($expected['criteria'], $entity->getSerializedCriteria());
        $this->assertEquals($expected['text'], $entity->getText());
        $this->assertEquals($expected['hash'], $entity->getHash());
    }
}
