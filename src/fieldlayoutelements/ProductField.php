<?php

namespace oym\craft\commerce\bundles\fieldlayoutelements;

use craft\base\ElementInterface;
use craft\fieldlayoutelements\BaseField;
use oym\craft\commerce\bundles\elements\ProductBundle;
use oym\craft\commerce\bundles\helpers\ProductMatrix;
use yii\base\InvalidArgumentException;

class ProductField extends BaseField
{
    public function attribute(): string
    {
        return 'products';
    }

    public function hasCustomWidth(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function defaultLabel(ElementInterface $element = null, bool $static = false): ?string
    {
        return \Craft::t('commerce-product-bundles', 'Products');
    }

    protected function inputHtml(ElementInterface $element = null, bool $static = false): ?string
    {
        if (!$element instanceof ProductBundle) {
            throw new InvalidArgumentException('ProductField can only be used in product bundle field layouts.');
        }

        return ProductMatrix::getProductMatrixHtml($element);
    }
}
