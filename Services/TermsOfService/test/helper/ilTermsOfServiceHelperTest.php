<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceHelperTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceHelperTest extends \ilTermsOfServiceBaseTest
{
    /**
     *
     */
    public function testDocumentCanBeAccepted()
    {
        $dataGatewayFactory = $this->getMockBuilder(\ilTermsOfServiceDataGatewayFactory::class)->getMock();
        $dataGateway = $this
            ->getMockBuilder(\ilTermsOfServiceAcceptanceDataGateway::class)
            ->getMock();

        $dataGateway
            ->expects($this->once())
            ->method('trackAcceptance')
            ->with($this->isInstanceOf(\ilTermsOfServiceAcceptanceEntity::class));

        $dataGatewayFactory
            ->expects($this->any())
            ->method('getByName')
            ->willReturn($dataGateway);

        $helper = new \ilTermsOfServiceHelper(
            $this->getMockBuilder(\ilDBInterface::class)->getMock(),
            $dataGatewayFactory
        );

        $user = $this
            ->getMockBuilder(\ilObjUser::class)
            ->disableOriginalConstructor()
            ->setMethods(['getLanguage', 'getId', 'getLogin', 'writeAccepted', 'hasToAcceptTermsOfServiceInSession'])
            ->getMock();

        $user
            ->expects($this->any())
            ->method('getId')
            ->willReturn(-1);

        $user
            ->expects($this->any())
            ->method('getLogin')
            ->willReturn('phpunit');

        $user
            ->expects($this->once())
            ->method('writeAccepted');

        $user
            ->expects($this->once())
            ->method('hasToAcceptTermsOfServiceInSession')
            ->with(false);

        $document = $this
            ->getMockBuilder(\ilTermsOfServiceDocument::class)
            ->disableOriginalConstructor()
            ->setMethods(['content', 'id', 'criteria', 'title'])
            ->getMock();

        $document
            ->expects($this->atLeast(1))
            ->method('content')
            ->willReturn('phpunit');

        $document
            ->expects($this->atLeast(1))
            ->method('title')
            ->willReturn('phpunit');

        $document
            ->expects($this->atLeast(1))
            ->method('id')
            ->willReturn(1);

        $document
            ->expects($this->atLeast(1))
            ->method('criteria')
            ->willReturn([]);

        $helper->trackAcceptance($user, $document);
    }

    /**
     *
     */
    public function testAcceptanceHistoryCanBeDeleted()
    {
        $dataGatewayFactory = $this->getMockBuilder(\ilTermsOfServiceDataGatewayFactory::class)->getMock();
        $dataGateway = $this
            ->getMockBuilder(\ilTermsOfServiceAcceptanceDataGateway::class)
            ->getMock();

        $dataGateway
            ->expects($this->once())
            ->method('deleteAcceptanceHistoryByUser')
            ->with($this->isInstanceOf(\ilTermsOfServiceAcceptanceEntity::class));

        $dataGatewayFactory
            ->expects($this->any())
            ->method('getByName')
            ->willReturn($dataGateway);

        $helper = new \ilTermsOfServiceHelper(
            $this->getMockBuilder(\ilDBInterface::class)->getMock(),
            $dataGatewayFactory
        );

        $user = $this
            ->getMockBuilder(\ilObjUser::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getLogin'])
            ->getMock();

        $user
            ->expects($this->any())
            ->method('getId')
            ->willReturn(-1);

        $user
            ->expects($this->any())
            ->method('getLogin')
            ->willReturn('phpunit');

        $helper->deleteAcceptanceHistoryByUser($user->getId());
    }

    /**
     *
     */
    public function testLatestAcceptanceHistoryEntityCanBeLoadedForUser()
    {
        $dataGatewayFactory = $this->getMockBuilder(\ilTermsOfServiceDataGatewayFactory::class)->getMock();
        $dataGateway = $this
            ->getMockBuilder(\ilTermsOfServiceAcceptanceDataGateway::class)
            ->getMock();

        $entity = new \ilTermsOfServiceAcceptanceEntity();
        $entity->withId(4711);

        $dataGateway
            ->expects($this->atLeast(1))
            ->method('loadCurrentAcceptanceOfUser')
            ->with($this->isInstanceOf(\ilTermsOfServiceAcceptanceEntity::class))
            ->willReturn($entity);

        $dataGatewayFactory
            ->expects($this->any())
            ->method('getByName')
            ->willReturn($dataGateway);

        $helper = new \ilTermsOfServiceHelper(
            $this->getMockBuilder(\ilDBInterface::class)->getMock(),
            $dataGatewayFactory
        );

        $user = $this
            ->getMockBuilder(\ilObjUser::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getLogin'])
            ->getMock();

        $user
            ->expects($this->any())
            ->method('getId')
            ->willReturn(-1);

        $user
            ->expects($this->any())
            ->method('getLogin')
            ->willReturn('phpunit');

        $this->assertInstanceOf(\ilTermsOfServiceAcceptanceEntity::class, $helper->getCurrentAcceptanceForUser($user));
        $this->assertEquals($entity, $helper->getCurrentAcceptanceForUser($user));
    }

    /**
     *
     */
    public function testAcceptanceHistoryEntityCanBeLoadedById()
    {
        $dataGatewayFactory = $this->getMockBuilder(\ilTermsOfServiceDataGatewayFactory::class)->getMock();
        $dataGateway = $this
            ->getMockBuilder(\ilTermsOfServiceAcceptanceDataGateway::class)
            ->getMock();

        $entity = new \ilTermsOfServiceAcceptanceEntity();
        $entity->withId(4711);

        $dataGateway
            ->expects($this->atLeast(1))
            ->method('loadById')
            ->willReturn($entity);

        $dataGatewayFactory
            ->expects($this->any())
            ->method('getByName')
            ->willReturn($dataGateway);

        $helper = new \ilTermsOfServiceHelper(
            $this->getMockBuilder(\ilDBInterface::class)->getMock(),
            $dataGatewayFactory
        );

        $user = $this
            ->getMockBuilder(\ilObjUser::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getLogin'])
            ->getMock();

        $user
            ->expects($this->any())
            ->method('getId')
            ->willReturn(-1);

        $user
            ->expects($this->any())
            ->method('getLogin')
            ->willReturn('phpunit');

        $this->assertInstanceOf(\ilTermsOfServiceAcceptanceEntity::class, $helper->getById($entity->getId()));
        $this->assertEquals($entity, $helper->getById($entity->getId()));
    }
}
