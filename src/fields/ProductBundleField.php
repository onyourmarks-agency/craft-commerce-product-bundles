<?php

namespace oym\craft\commerce\bundles\fields;

use craft\fields\BaseRelationField;
use oym\craft\commerce\bundles\elements\db\ProductBundleQuery;
use oym\craft\commerce\bundles\elements\ProductBundle;

class ProductBundleField extends BaseRelationField
{
    public static function displayName(): string
    {
        return \Craft::t('commerce-product-bundles', 'Commerce product bundle(s)');
    }

    public static function elementType(): string
    {
        return ProductBundle::class;
    }

    public static function defaultSelectionLabel(): string
    {
        return \Craft::t('commerce-product-bundles', 'Add a product bundle');
    }

    public static function valueType(): string
    {
        return ProductBundleQuery::class;
    }
}
