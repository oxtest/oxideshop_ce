<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Framework\Module\TemplateExtension;

use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

class TemplateBlockExtensionDao implements TemplateBlockExtensionDaoInterface
{
    public function __construct(
        private QueryBuilderFactoryInterface $queryBuilderFactory,
        private ShopAdapterInterface $shopAdapter
    ) {
    }

    /**
     * @param TemplateBlockExtension $templateBlockExtension
     */
    public function add(TemplateBlockExtension $templateBlockExtension)
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->insert('oxtplblocks')
            ->values([
                'oxid'          => ':id',
                'oxshopid'      => ':shopId',
                'oxmodule'      => ':moduleId',
                'oxtheme'       => ':themeId',
                'oxblockname'   => ':name',
                'oxfile'        => ':filePath',
                'oxtemplate'    => ':templatePath',
                'oxpos'         => ':priority',
                'oxactive'      => '1',
            ])
            ->setParameters([
                'id'            => $this->shopAdapter->generateUniqueId(),
                'shopId'        => $templateBlockExtension->getShopId(),
                'moduleId'      => $templateBlockExtension->getModuleId(),
                'themeId'       => $templateBlockExtension->getThemeId(),
                'name'          => $templateBlockExtension->getName(),
                'filePath'      => $templateBlockExtension->getFilePath(),
                'templatePath'  => $templateBlockExtension->getExtendedBlockTemplatePath(),
                'priority'      => $templateBlockExtension->getPosition(),
            ]);

        $queryBuilder->execute();
    }

    /**
     * @param string $name
     * @param int    $shopId
     * @return array
     */
    public function getExtensions(string $name, int $shopId): array
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->select('*')
            ->from('oxtplblocks')
            ->where('oxshopid = :shopId')
            ->andWhere('oxblockname = :name')
            ->andWhere('oxmodule != \'\'')
            ->setParameters([
                'shopId'    => $shopId,
                'name'      => $name,
            ]);

        $blocksData = $queryBuilder->execute()->fetchAll();

        return $this->mapDataToObjects($blocksData);
    }

    /**
     * @param string $moduleId
     * @param int    $shopId
     */
    public function deleteExtensions(string $moduleId, int $shopId)
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->delete('oxtplblocks')
            ->where('oxshopid = :shopId')
            ->andWhere('oxmodule = :moduleId')
            ->setParameters([
                'shopId'    => $shopId,
                'moduleId'  => $moduleId,
            ]);

        $queryBuilder->execute();
    }

    /**
     * @param array $blocksData
     * @return array
     */
    private function mapDataToObjects(array $blocksData): array
    {
        $templateBlockExtensions = [];

        foreach ($blocksData as $blockData) {
            $templateBlock = new TemplateBlockExtension();
            $templateBlock
                ->setShopId(
                    (int) $blockData['OXSHOPID']
                )
                ->setModuleId(
                    $blockData['OXMODULE']
                )
                ->setThemeId(
                    $blockData['OXTHEME']
                )
                ->setName(
                    $blockData['OXBLOCKNAME']
                )
                ->setFilePath(
                    $blockData['OXFILE']
                )
                ->setExtendedBlockTemplatePath(
                    $blockData['OXTEMPLATE']
                )
                ->setPosition(
                    (int) $blockData['OXPOS']
                );

            $templateBlockExtensions[] = $templateBlock;
        }

        return $templateBlockExtensions;
    }
}
