<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Application\Controller\Admin;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\TableViewNameGenerator;

/**
 * Admin article main delivery manager.
 * There is possibility to change delivery name, article, user
 * and etc.
 * Admin Menu: Shop settings -> Shipping & Handling -> Main.
 */
class DeliveryUsers extends \OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController
{
    /** @inheritdoc */
    public function render()
    {
        parent::render();

        $soxId = $this->getEditObjectId();

        $tableViewNameGenerator = oxNew(TableViewNameGenerator::class);
        $sViewName = $tableViewNameGenerator->getViewName("oxgroups", $this->_iEditLang);
        // all usergroups
        $oGroups = oxNew(\OxidEsales\Eshop\Core\Model\ListModel::class);
        $oGroups->init('oxgroups');
        $oGroups->selectString("select * from {$sViewName}");

        $oRoot = new \OxidEsales\Eshop\Application\Model\Groups();
        $oRoot->oxgroups__oxid = new \OxidEsales\Eshop\Core\Field("");
        $oRoot->oxgroups__oxtitle = new \OxidEsales\Eshop\Core\Field("-- ");
        // rebuild list as we need the "no value" entry at the first position
        $aNewList = [];
        $aNewList[] = $oRoot;

        foreach ($oGroups as $val) {
            $aNewList[$val->oxgroups__oxid->value] = new \OxidEsales\Eshop\Application\Model\Groups();
            $aNewList[$val->oxgroups__oxid->value]->oxgroups__oxid = new \OxidEsales\Eshop\Core\Field($val->oxgroups__oxid->value);
            $aNewList[$val->oxgroups__oxid->value]->oxgroups__oxtitle = new \OxidEsales\Eshop\Core\Field($val->oxgroups__oxtitle->value);
        }

        $oGroups = $aNewList;

        if (isset($soxId) && $soxId != "-1") {
            $oDelivery = oxNew(\OxidEsales\Eshop\Application\Model\Delivery::class);
            $oDelivery->load($soxId);

            //Disable editing for derived articles
            if ($oDelivery->isDerived()) {
                $this->_aViewData['readonly'] = true;
            }
        }

        $this->_aViewData["allgroups2"] = $oGroups;

        $iAoc = Registry::getRequest()->getRequestEscapedParameter("aoc");
        if ($iAoc == 1) {
            $oDeliveryUsersAjax = oxNew(\OxidEsales\Eshop\Application\Controller\Admin\DeliveryUsersAjax::class);
            $this->_aViewData['oxajax'] = $oDeliveryUsersAjax->getColumns();

            return "popups/delivery_users";
        } elseif ($iAoc == 2) {
            $oDeliveryGroupsAjax = oxNew(\OxidEsales\Eshop\Application\Controller\Admin\DeliveryGroupsAjax::class);
            $this->_aViewData['oxajax'] = $oDeliveryGroupsAjax->getColumns();

            return "popups/delivery_groups";
        }

        return "delivery_users";
    }
}
