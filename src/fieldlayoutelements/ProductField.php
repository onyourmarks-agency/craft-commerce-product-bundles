<?php

namespace tde\craft\commerce\bundles\fieldlayoutelements;

use craft\base\ElementInterface;
use craft\fieldlayoutelements\BaseField;
use tde\craft\commerce\bundles\elements\ProductBundle;
use tde\craft\commerce\bundles\helpers\ProductMatrix;
use yii\base\InvalidArgumentException;

/**
 * @package tde\craft\commerce\bundles\fieldlayoutelements
 */
class ProductField extends BaseField
{
    /**
     * @inheritdoc
     */
    public function attribute(): string
    {
        return 'products';
    }

    /**
     * @inheritdoc
     */
    public function hasCustomWidth(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function defaultLabel(ElementInterface $element = null, bool $static = false)
    {
        return \Craft::t('commerce-product-bundles', 'Products');
    }

//    /**
//     * @inheritdoc
//     */
//    protected function selectorInnerHtml(): string
//    {
//        return
//            \Html::tag('span', '', [
//                'class' => ['fld-variants-field-icon', 'fld-field-hidden', 'hidden'],
//            ]) .
//            parent::selectorInnerHtml();
//    }

    /**
     * @inheritdoc
     */
    protected function inputHtml(ElementInterface $element = null, bool $static = false)
    {
        if (!$element instanceof ProductBundle) {
            throw new InvalidArgumentException('ProductField can only be used in product bundle field layouts.');
        }

        return ProductMatrix::getProductMatrixHtml($element);
    }
}
