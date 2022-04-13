<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceCriterionTypeFactoryTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceCriterionTypeFactoryTest extends ilTermsOfServiceBaseTest
{
    public function testInstanceCanBeCreated() : ilTermsOfServiceCriterionTypeFactory
    {
        $dataCache = $this
            ->getMockBuilder(ilObjectDataCache::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rbacReview = $this
            ->getMockBuilder(ilRbacReview::class)
            ->disableOriginalConstructor()
            ->getMock();

        $criterionTypeFactory = new ilTermsOfServiceCriterionTypeFactory(
            $rbacReview,
            $dataCache,
            []
        );

        $this->assertInstanceOf(ilTermsOfServiceCriterionTypeFactory::class, $criterionTypeFactory);

        return $criterionTypeFactory;
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilTermsOfServiceCriterionTypeFactory $criterionTypeFactory
     */
    public function testFactoryReturnsValidCriteriaWhenRequested(
        ilTermsOfServiceCriterionTypeFactory $criterionTypeFactory
    ) : void {
        $this->assertCount(3, $criterionTypeFactory->getTypesByIdentMap());
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilTermsOfServiceCriterionTypeFactory $criterionTypeFactory
     */
    public function testKeysOfCriteriaCollectionMatchTheRespectiveTypeIdent(
        ilTermsOfServiceCriterionTypeFactory $criterionTypeFactory
    ) : void {
        $criteria = $criterionTypeFactory->getTypesByIdentMap();

        $this->assertSame(
            array_keys($criteria),
            array_values(array_map(static function (ilTermsOfServiceCriterionType $criterion) : string {
                return $criterion->getTypeIdent();
            }, $criteria))
        );
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilTermsOfServiceCriterionTypeFactory $criterionTypeFactory
     */
    public function testCriterionIsReturnedIfRequestedByTypeIdent(
        ilTermsOfServiceCriterionTypeFactory $criterionTypeFactory
    ) : void {
        foreach ($criterionTypeFactory->getTypesByIdentMap() as $criterion) {
            $this->assertSame($criterion, $criterionTypeFactory->findByTypeIdent($criterion->getTypeIdent()));
        }
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilTermsOfServiceCriterionTypeFactory $criterionTypeFactory
     */
    public function testExceptionIsRaisedIfUnsupportedCriterionIsRequested(
        ilTermsOfServiceCriterionTypeFactory $criterionTypeFactory
    ) : void {
        $this->expectException(ilTermsOfServiceCriterionTypeNotFoundException::class);

        $criterionTypeFactory->findByTypeIdent('phpunit');
    }

    /**
     * @depends testInstanceCanBeCreated
     * @param ilTermsOfServiceCriterionTypeFactory $criterionTypeFactory
     */
    public function testNullCriterionIsReturnedAsFallbackIfUnsupportedCriterionIsRequested(
        ilTermsOfServiceCriterionTypeFactory $criterionTypeFactory
    ) : void {
        $this->assertInstanceOf(
            ilTermsOfServiceNullCriterion::class,
            $criterionTypeFactory->findByTypeIdent('phpunit', true)
        );
    }
}
