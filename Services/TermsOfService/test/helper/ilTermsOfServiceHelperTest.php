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

/**
 * Class ilTermsOfServiceHelperTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceHelperTest extends ilTermsOfServiceBaseTest
{
    public function testDocumentCanBeAccepted() : void
    {
        $dataGatewayFactory = $this->getMockBuilder(ilTermsOfServiceDataGatewayFactory::class)->getMock();
        $dataGateway = $this
            ->getMockBuilder(ilTermsOfServiceAcceptanceDataGateway::class)
            ->getMock();

        $dataGateway
            ->expects($this->once())
            ->method('trackAcceptance')
            ->with($this->isInstanceOf(ilTermsOfServiceAcceptanceEntity::class));

        $dataGatewayFactory
            ->method('getByName')
            ->willReturn($dataGateway);

        $helper = new ilTermsOfServiceHelper(
            $dataGatewayFactory,
            $this->getMockBuilder(ilTermsOfServiceDocumentEvaluation::class)->getMock(),
            $this->getMockBuilder(ilTermsOfServiceCriterionTypeFactoryInterface::class)->getMock(),
            $this->getMockBuilder(ilObjTermsOfService::class)->disableOriginalConstructor()->getMock()
        );

        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLanguage', 'getId', 'getLogin', 'writeAccepted', 'hasToAcceptTermsOfServiceInSession'])
            ->getMock();

        $user
            ->method('getId')
            ->willReturn(-1);

        $user
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
            ->getMockBuilder(ilTermsOfServiceDocument::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['content', 'id', 'criteria', 'title'])
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

    public function testAcceptanceHistoryCanBeDeleted() : void
    {
        $dataGatewayFactory = $this->getMockBuilder(ilTermsOfServiceDataGatewayFactory::class)->getMock();
        $dataGateway = $this
            ->getMockBuilder(ilTermsOfServiceAcceptanceDataGateway::class)
            ->getMock();

        $dataGateway
            ->expects($this->once())
            ->method('deleteAcceptanceHistoryByUser')
            ->with($this->isInstanceOf(ilTermsOfServiceAcceptanceEntity::class));

        $dataGatewayFactory
            ->method('getByName')
            ->willReturn($dataGateway);

        $helper = new ilTermsOfServiceHelper(
            $dataGatewayFactory,
            $this->getMockBuilder(ilTermsOfServiceDocumentEvaluation::class)->getMock(),
            $this->getMockBuilder(ilTermsOfServiceCriterionTypeFactoryInterface::class)->getMock(),
            $this->getMockBuilder(ilObjTermsOfService::class)->disableOriginalConstructor()->getMock()
        );

        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getLogin'])
            ->getMock();

        $user
            ->method('getId')
            ->willReturn(-1);

        $user
            ->method('getLogin')
            ->willReturn('phpunit');

        $helper->deleteAcceptanceHistoryByUser($user->getId());
    }

    public function testLatestAcceptanceHistoryEntityCanBeLoadedForUser() : void
    {
        $dataGatewayFactory = $this->getMockBuilder(ilTermsOfServiceDataGatewayFactory::class)->getMock();
        $dataGateway = $this
            ->getMockBuilder(ilTermsOfServiceAcceptanceDataGateway::class)
            ->getMock();

        $entity = new ilTermsOfServiceAcceptanceEntity();
        $entity = $entity->withId(4711);

        $dataGateway
            ->expects($this->atLeast(1))
            ->method('loadCurrentAcceptanceOfUser')
            ->with($this->isInstanceOf(ilTermsOfServiceAcceptanceEntity::class))
            ->willReturn($entity);

        $dataGatewayFactory
            ->method('getByName')
            ->willReturn($dataGateway);

        $helper = new ilTermsOfServiceHelper(
            $dataGatewayFactory,
            $this->getMockBuilder(ilTermsOfServiceDocumentEvaluation::class)->getMock(),
            $this->getMockBuilder(ilTermsOfServiceCriterionTypeFactoryInterface::class)->getMock(),
            $this->getMockBuilder(ilObjTermsOfService::class)->disableOriginalConstructor()->getMock()
        );

        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getLogin'])
            ->getMock();

        $user
            ->method('getId')
            ->willReturn(-1);

        $user
            ->method('getLogin')
            ->willReturn('phpunit');

        $this->assertInstanceOf(ilTermsOfServiceAcceptanceEntity::class, $helper->getCurrentAcceptanceForUser($user));
        $this->assertSame($entity, $helper->getCurrentAcceptanceForUser($user));
    }

    public function testAcceptanceHistoryEntityCanBeLoadedById() : void
    {
        $dataGatewayFactory = $this->getMockBuilder(ilTermsOfServiceDataGatewayFactory::class)->getMock();
        $dataGateway = $this
            ->getMockBuilder(ilTermsOfServiceAcceptanceDataGateway::class)
            ->getMock();

        $entity = new ilTermsOfServiceAcceptanceEntity();
        $entity = $entity->withId(4711);

        $dataGateway
            ->expects($this->atLeast(1))
            ->method('loadById')
            ->willReturn($entity);

        $dataGatewayFactory
            ->method('getByName')
            ->willReturn($dataGateway);

        $helper = new ilTermsOfServiceHelper(
            $dataGatewayFactory,
            $this->getMockBuilder(ilTermsOfServiceDocumentEvaluation::class)->getMock(),
            $this->getMockBuilder(ilTermsOfServiceCriterionTypeFactoryInterface::class)->getMock(),
            $this->getMockBuilder(ilObjTermsOfService::class)->disableOriginalConstructor()->getMock()
        );

        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getLogin'])
            ->getMock();

        $user
            ->method('getId')
            ->willReturn(-1);

        $user
            ->method('getLogin')
            ->willReturn('phpunit');

        $this->assertInstanceOf(ilTermsOfServiceAcceptanceEntity::class, $helper->getById($entity->getId()));
        $this->assertSame($entity, $helper->getById($entity->getId()));
    }
}
