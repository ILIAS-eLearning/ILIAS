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
