<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Framework\Templating\Cache;

use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContextInterface;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\PathUtil\Path;

/**
 * Class TemplateCacheService
 * @package OxidEsales\EshopCommunity\Internal\Framework\Templating\Cache
 */
class TemplateCacheService implements TemplateCacheServiceInterface
{
    public function __construct(
        private BasicContextInterface $basicContext,
        private Filesystem $filesystem
    ) {
    }

    public function invalidateTemplateCache(): void
    {
        $templateCacheDirectory = $this->getTemplateCacheDirectory();

        if ($this->filesystem->exists($templateCacheDirectory)) {
            $recursiveIterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($templateCacheDirectory, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST,
                RecursiveIteratorIterator::CATCH_GET_CHILD
            );

            $this->filesystem->remove($recursiveIterator);
        }
    }

    private function getTemplateCacheDirectory(): string
    {
        return Path::join(
            $this->basicContext->getCacheDirectory(),
            'smarty'
        );
    }
}
