<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Domain\Review\Dao;

use Doctrine\Common\Collections\ArrayCollection;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Domain\Review\DataMapper\ReviewDataMapperInterface;
use OxidEsales\EshopCommunity\Internal\Domain\Review\DataObject\Review;

class ReviewDao implements ReviewDaoInterface
{
    public function __construct(
        private QueryBuilderFactoryInterface $queryBuilderFactory,
        private ReviewDataMapperInterface $reviewDataMapper
    ) {
    }

    /**
     * Returns User Reviews.
     *
     * @param string $userId
     *
     * @return ArrayCollection
     */
    public function getReviewsByUserId($userId)
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->select('r.*')
            ->from('oxreviews', 'r')
            ->where('r.oxuserid = :userId')
            ->orderBy('r.oxcreate', 'DESC')
            ->setParameter('userId', $userId);

        return $this->mapReviews($queryBuilder->execute()->fetchAll());
    }

    /**
     * @param Review $review
     */
    public function delete(Review $review)
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->delete('oxreviews')
            ->where('oxid = :id')
            ->setParameter('id', $review->getId())
            ->execute();
    }

    /**
     * Maps rating data from database to Reviews Collection.
     *
     * @param array $reviewsData
     *
     * @return ArrayCollection
     */
    private function mapReviews($reviewsData)
    {
        $reviews = new ArrayCollection();

        foreach ($reviewsData as $reviewData) {
            $review = new Review();
            $reviews[] = $this->reviewDataMapper->map($review, $reviewData);
        }

        return $reviews;
    }
}
