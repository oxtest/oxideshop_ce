<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Framework\Smarty\Configuration;

use OxidEsales\EshopCommunity\Internal\Framework\Smarty\SmartyContextInterface;

class SmartyPrefiltersDataProvider implements SmartyPrefiltersDataProviderInterface
{
    public function __construct(private SmartyContextInterface $context)
    {
    }

    /**
     * @return array
     */
    public function getPrefilterPlugins(): array
    {
        $prefilterPath = $this->getPrefilterPath();
        $prefilter['smarty_prefilter_oxblock'] = $prefilterPath . '/prefilter.oxblock.php';
        if ($this->context->showTemplateNames()) {
            $prefilter['smarty_prefilter_oxtpldebug'] = $prefilterPath . '/prefilter.oxtpldebug.php';
        }

        return $prefilter;
    }

    /**
     * @return string
     */
    private function getPrefilterPath(): string
    {
        return $this->context->getSourcePath() . '/Core/Smarty/Plugin';
    }
}
