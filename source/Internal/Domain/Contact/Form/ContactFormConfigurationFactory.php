<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Domain\Contact\Form;

use OxidEsales\EshopCommunity\Internal\Framework\FormConfiguration\FieldConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\FormConfiguration\FieldConfigurationInterface;
use OxidEsales\EshopCommunity\Internal\Framework\FormConfiguration\FormConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\FormConfiguration\FormConfigurationFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\FormConfiguration\FormConfigurationInterface;
use OxidEsales\EshopCommunity\Internal\Framework\FormConfiguration\FormFieldsConfigurationDataProviderInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;

class ContactFormConfigurationFactory implements FormConfigurationFactoryInterface
{
    public function __construct(
        private FormFieldsConfigurationDataProviderInterface $contactFormConfigurationDataProvider,
        private ContextInterface $context
    ) {
    }

    /**
     * @return FormConfigurationInterface
     */
    public function getFormConfiguration()
    {
        $formConfiguration = new FormConfiguration();

        $fieldsConfigurationData = $this
            ->contactFormConfigurationDataProvider
            ->getFormFieldsConfiguration();

        foreach ($fieldsConfigurationData as $fieldConfigurationData) {
            $fieldConfiguration = $this->getFieldConfiguration($fieldConfigurationData);
            $formConfiguration->addFieldConfiguration($fieldConfiguration);
        }

        return $formConfiguration;
    }

    /**
     * @param array $fieldConfigurationData
     * @return FieldConfiguration
     */
    private function getFieldConfiguration($fieldConfigurationData)
    {
        $fieldConfiguration = new FieldConfiguration();
        $fieldConfiguration->setName($fieldConfigurationData['name']);
        $fieldConfiguration->setLabel($fieldConfigurationData['label']);

        if ($this->isFieldRequired($fieldConfiguration)) {
            $fieldConfiguration->setIsRequired(true);
        }

        return $fieldConfiguration;
    }

    /**
     * @param FieldConfigurationInterface $fieldConfiguration
     * @return bool
     */
    private function isFieldRequired(FieldConfigurationInterface $fieldConfiguration)
    {
        return in_array(
            $fieldConfiguration->getName(),
            $this->context->getRequiredContactFormFields()
        );
    }
}
