<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Integration\Internal\Domain\Review\Bridge;

use Doctrine\DBAL\Connection;
use OxidEsales\Eshop\Application\Model\Rating;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\EshopCommunity\Internal\Framework\Dao\EntryDoesNotExistDaoException;
use OxidEsales\EshopCommunity\Internal\Domain\Review\Bridge\UserRatingBridge;
use OxidEsales\EshopCommunity\Internal\Domain\Review\Exception\RatingPermissionException;
use OxidEsales\EshopCommunity\Internal\Domain\Review\Service\UserRatingService;
use OxidEsales\EshopCommunity\Internal\Domain\Review\Service\UserRatingServiceInterface;
use OxidEsales\EshopCommunity\Tests\TestUtils\IntegrationTestCase;
use OxidEsales\EshopCommunity\Tests\TestUtils\Traits\ContainerTrait;
use OxidEsales\EshopCommunity\Tests\TestUtils\Traits\DatabaseTestingTrait;

class UserRatingBridgeTest extends IntegrationTestCase
{
    public function testDeleteRating()
    {
        $this->createTestRating();

        $userRatingBridge = $this->getUserRatingBridge();
        $userRatingBridge->deleteRating('testUserId', 'testRatingId');

        $this->assertFalse(
            $this->ratingExists('testRatingId')
        );
        $this->tearDownTestContainer();
    }

    public function testDeleteRatingForSubShop()
    {
        $this->createTestRatingForSubShop();

        $userRatingBridge = $this->getUserRatingBridge();
        $userRatingBridge->deleteRating('testUserId', 'testSubShopRatingId');

        $this->assertFalse(
            $this->ratingExists('testSubShopRatingId')
        );
    }

    public function testDeleteRatingWithNonExistentRatingId()
    {
        $this->expectException(EntryDoesNotExistDaoException::class);

        $userRatingBridge = $this->getUserRatingBridge();
        $userRatingBridge->deleteRating('testUserId', 'nonExistentId');
    }

    public function testDeleteRatingWithWrongUserId()
    {
        $this->expectException(RatingPermissionException::class);

        $this->createTestRating();

        $userRatingBridge = $this->getUserRatingBridge();
        $userRatingBridge->deleteRating('userWithWrongId', 'testRatingId');
    }

    private function ratingExists($id)
    {
        $rating = oxNew(Rating::class);

        return $rating->load($id) !== false;
    }

    private function getUserRatingBridge()
    {
        return new UserRatingBridge(
            $this->getUserRatingServiceMock()
        );
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|UserRatingServiceInterface
     */
    private function getUserRatingServiceMock()
    {
        $userRatingServiceMock = $this->getMockBuilder(UserRatingService::class)
            ->disableOriginalConstructor()
            ->getMock();
        return $userRatingServiceMock;
    }

    private function createTestRating()
    {
        $rating = oxNew(Rating::class);
        $rating->setId('testRatingId');
        $rating->oxratings__oxuserid = new Field('testUserId');
        $rating->save();
    }

    private function createTestRatingForSubShop()
    {
        $rating = oxNew(Rating::class);
        $rating->setId('testSubShopRatingId');
        $rating->oxratings__oxuserid = new Field('testUserId');
        $rating->oxratings__oxshopid = new Field(5);
        $rating->save();
    }
}
