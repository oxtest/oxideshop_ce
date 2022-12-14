<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Framework\Smarty;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContextInterface;

class SmartyContext implements SmartyContextInterface
{
    public function __construct(
        private BasicContextInterface $basicContext,
        private Config $config,
        private UtilsView $utilsView
    ) {
    }

    /**
     * @return bool
     */
    public function getTemplateEngineDebugMode(): bool
    {
        $debugMode = $this->getConfigParameter('iDebug');
        return ($debugMode === 1 || $debugMode === 3 || $debugMode === 4);
    }

    /**
     * @return bool
     */
    public function showTemplateNames(): bool
    {
        $debugMode = $this->getConfigParameter('iDebug');
        return ($debugMode === 8 && !$this->getBackendMode());
    }

    /**
     * @return bool
     */
    public function getTemplateSecurityMode(): bool
    {
        return $this->getDemoShopMode();
    }

    /**
     * @return string
     */
    public function getTemplateCompileDirectory(): string
    {
        return $this->utilsView->getSmartyDir();
    }

    /**
     * @return array
     */
    public function getTemplateDirectories(): array
    {
        return $this->utilsView->getTemplateDirs();
    }

    /**
     * @return string
     */
    public function getTemplateCompileId(): string
    {
        return $this->utilsView->getTemplateCompileId();
    }

    /**
     * @return bool
     */
    public function getTemplateCompileCheckMode(): bool
    {
        $compileCheck = (bool) $this->getConfigParameter('blCheckTemplates');
        if ($this->config->isProductiveMode()) {
            // override in any case
            $compileCheck = false;
        }
        return $compileCheck;
    }

    /**
     * @return array
     */
    public function getSmartyPluginDirectories(): array
    {
        return $this->utilsView->getSmartyPluginDirectories();
    }

    /**
     * @return int
     */
    public function getTemplatePhpHandlingMode(): int
    {
        return (int) $this->getConfigParameter('iSmartyPhpHandling');
    }

    /**
     * @param string $templateName
     *
     * @return string
     */
    public function getTemplatePath($templateName): string
    {
        return $this->config->getTemplatePath($templateName, $this->getBackendMode());
    }

    /**
     * @return string
     */
    public function getSourcePath(): string
    {
        return $this->basicContext->getSourcePath();
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    private function getConfigParameter($name)
    {
        return $this->config->getConfigParam($name);
    }

    /**
     * @return bool
     */
    private function getBackendMode(): bool
    {
        return $this->config->isAdmin();
    }

    /**
     * @return bool
     */
    private function getDemoShopMode(): bool
    {
        return (bool)$this->config->isDemoShop();
    }
}
