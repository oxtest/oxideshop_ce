<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Internal\Domain\Contact\Form;

use OxidEsales\EshopCommunity\Internal\Transition\Adapter\ShopAdapterInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Form\FormInterface;

class ContactFormMessageBuilder implements ContactFormMessageBuilderInterface
{
    public function __construct(private ShopAdapterInterface $shopAdapter)
    {
    }

    /**
     * @param FormInterface $form
     * @return string
     */
    public function getContent(FormInterface $form)
    {
        $message = $this->shopAdapter->translateString('MESSAGE_FROM') . ' ';

        $salutation = $form->salutation->getValue();
        if ($salutation) {
            $message .= $this->shopAdapter->translateString($salutation) . ' ';
        }

        if ($form->firstName->getValue()) {
            $message .= $form->firstName->getValue() . ' ';
        }

        if ($form->lastName->getValue()) {
            $message .= $form->lastName->getValue() . ' ';
        }

        $message .= '(' . $form->email->getValue() . ')<br /><br />';

        if ($form->message->getValue()) {
            $message .= nl2br($form->message->getValue());
        }

        return $message;
    }
}
