<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Integration\Core\Module;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Install\DataObject\OxidEshopPackage;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Install\Service\ModuleInstallerInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Bridge\ModuleActivationBridgeInterface;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\EshopCommunity\Tests\TestUtils\IntegrationTestCase;
use OxidEsales\EshopCommunity\Tests\TestUtils\Traits\ModuleTestingTrait;
use PHPUnit\Framework\TestCase;
use Webmozart\PathUtil\Path;

/**
 * Class ModuleSmartyPluginDirectoryTest
 */
class ModuleSmartyPluginDirectoriesTest extends IntegrationTestCase
{
    private $container;

    public function setup(): void
    {
        parent::setUp();
        $module = 'with_metadata_v21';
        $this->installModule($module, Path::canonicalize(Path::join(__DIR__, 'Fixtures')));
        $this->activateModule($module);

        $this->activateTestModule();
    }

    /**
     * Smarty should know about the smarty plugin directories of the modules being activated.
     */
    public function testModuleSmartyPluginDirectoryIsIncludedOnModuleActivation()
    {
        $utilsView = oxNew(UtilsView::class);
        $smarty = $utilsView->getSmarty(true);

        $this->assertTrue(
            $this->isPathInSmartyDirectories($smarty, 'Smarty/PluginDirectory1WithMetadataVersion21')
        );

        $this->assertTrue(
            $this->isPathInSmartyDirectories($smarty, 'Smarty/PluginDirectory2WithMetadataVersion21')
        );
    }

    public function testSmartyPluginDirectoriesOrder()
    {
        $utilsView = oxNew(UtilsView::class);
        $smarty = $utilsView->getSmarty(true);

        $this->assertModuleSmartyPluginDirectoriesFirst($smarty->plugins_dir);
        $this->assertShopSmartyPluginDirectorySecond($smarty->plugins_dir);
    }

    private function assertModuleSmartyPluginDirectoriesFirst($directories)
    {
        $this->assertStringContainsString(
            'Smarty/PluginDirectory1WithMetadataVersion21',
            $directories[0]
        );

        $this->assertStringContainsString(
            'Smarty/PluginDirectory2WithMetadataVersion21',
            $directories[1]
        );
    }

    private function assertShopSmartyPluginDirectorySecond($directories)
    {
        $this->assertStringContainsString(
            'Core/Smarty/Plugin',
            $directories[2]
        );
    }

    private function isPathInSmartyDirectories($smarty, $path)
    {
        foreach ($smarty->plugins_dir as $directory) {
            if (strpos($directory, $path)) {
                return true;
            }
        }

        return false;
    }

    private function activateTestModule()
    {
        $id = 'with_metadata_v21';
        $package = new OxidEshopPackage($id, __DIR__ . '/Fixtures/' . $id);
        $package->setTargetDirectory('oeTest/' . $id);

        $this->container->get(ModuleInstallerInterface::class)
            ->install($package);

        $this->container
            ->get(ModuleActivationBridgeInterface::class)
            ->activate('with_metadata_v21', Registry::getConfig()->getShopId());
    }

    private function deactivateTestModule()
    {
        $this->container
            ->get(ModuleActivationBridgeInterface::class)
            ->deactivate('with_metadata_v21', Registry::getConfig()->getShopId());
    }

    private function removeTestModules()
    {
        $fileSystem = $this->container->get('oxid_esales.symfony.file_system');
        $fileSystem->remove($this->container->get(ContextInterface::class)->getModulesPath() . '/oeTest/');
    }
}
