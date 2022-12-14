<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Transition\Adapter\TemplateLogic;

use OxidEsales\Eshop\Core\Exception\StandardException;

class TranslateSalutationLogic
{
    /**
     * @param string $sIdent
     *
     * @return string
     */
    public function translateSalutation(string $sIdent = null): string
    {
        $oLang = \OxidEsales\Eshop\Core\Registry::getLang();
        $iLang = $oLang->getTplLanguage();

        if (!isset($iLang)) {
            $iLang = $oLang->getBaseLanguage();
            if (!isset($iLang)) {
                $iLang = 0;
            }
        }

        try {
            $sTranslation = $oLang->translateString($sIdent, $iLang, $oLang->isAdmin());
        } catch (StandardException) {
            // is thrown in debug mode and has to be caught here, as smarty hangs otherwise!
        }

        return $sTranslation ?: '';
    }
}
