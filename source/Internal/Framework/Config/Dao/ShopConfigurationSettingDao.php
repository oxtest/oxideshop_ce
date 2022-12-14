<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Framework\Config\Dao;

use OxidEsales\EshopCommunity\Internal\Framework\Config\DataObject\ShopConfigurationSetting;
use OxidEsales\EshopCommunity\Internal\Framework\Config\Utility\ShopSettingEncoderInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Dao\EntryDoesNotExistDaoException;
use OxidEsales\EshopCommunity\Internal\Framework\Config\Event\ShopConfigurationChangedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ShopConfigurationSettingDao implements ShopConfigurationSettingDaoInterface
{
    public function __construct(
        private QueryBuilderFactoryInterface $queryBuilderFactory,
        private ShopSettingEncoderInterface $shopSettingEncoder,
        private ShopAdapterInterface $shopAdapter,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @param ShopConfigurationSetting $shopConfigurationSetting
     */
    public function save(ShopConfigurationSetting $shopConfigurationSetting)
    {
        $this->delete($shopConfigurationSetting);

        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->insert('oxconfig')
            ->values([
                'oxid'          => ':id',
                'oxshopid'      => ':shopId',
                'oxvarname'     => ':name',
                'oxvartype'     => ':type',
                'oxvarvalue'    => ':value',
            ])
            ->setParameters([
                'id'        => $this->shopAdapter->generateUniqueId(),
                'shopId'    => $shopConfigurationSetting->getShopId(),
                'name'      => $shopConfigurationSetting->getName(),
                'type'      => $shopConfigurationSetting->getType(),
                'value'     => $this->shopSettingEncoder->encode(
                    $shopConfigurationSetting->getType(),
                    $shopConfigurationSetting->getValue()
                ),
            ]);

        $queryBuilder->execute();

        $this->eventDispatcher->dispatch(
            new ShopConfigurationChangedEvent(
                $shopConfigurationSetting->getName(),
                $shopConfigurationSetting->getShopId()
            )
        );
    }

    /**
     * @param string $name
     * @param int    $shopId
     * @return ShopConfigurationSetting
     * @throws EntryDoesNotExistDaoException
     */
    public function get(string $name, int $shopId): ShopConfigurationSetting
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->select('oxvarvalue as value, oxvartype as type, oxvarname as name')
            ->from('oxconfig')
            ->where('oxshopid = :shopId')
            ->andWhere('oxvarname = :name')
            ->andWhere('oxmodule = ""')
            ->setParameters([
                'shopId'    => $shopId,
                'name'      => $name,
            ]);

        $result = $queryBuilder->execute()->fetch();

        if (false === $result) {
            throw new EntryDoesNotExistDaoException(
                'Setting ' . $name . ' doesn\'t exist in the shop with id ' . $shopId
            );
        }

        $setting = new ShopConfigurationSetting();
        $setting
            ->setName($name)
            ->setValue($this->shopSettingEncoder->decode($result['type'], $result['value']))
            ->setShopId($shopId)
            ->setType($result['type']);

        return $setting;
    }

    /**
     * @param ShopConfigurationSetting $setting
     */
    public function delete(ShopConfigurationSetting $setting)
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->delete('oxconfig')
            ->where('oxshopid = :shopId')
            ->andWhere('oxvarname = :name')
            ->andWhere('oxmodule = ""')
            ->setParameters([
                'shopId'    => $setting->getShopId(),
                'name'      => $setting->getName(),
            ]);

        $queryBuilder->execute();
    }
}
