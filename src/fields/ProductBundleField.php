<?php

namespace tde\craft\commerce\bundles\fields;

use craft\fields\BaseRelationField;
use tde\craft\commerce\bundles\elements\db\ProductBundleQuery;
use tde\craft\commerce\bundles\elements\ProductBundle;

/**
 * @package tde\craft\commerce\bundles\fields
 */
class ProductBundleField extends BaseRelationField
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return \Craft::t('commerce-product-bundles', 'Commerce product bundle(s)');
    }

    /**
     * @inheritdoc
     */
    public static function elementType(): string
    {
        return ProductBundle::class;
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return \Craft::t('commerce-product-bundles', 'Add a product bundle');
    }

    /**
     * @inheritdoc
     */
    public static function valueType(): string
    {
        return ProductBundleQuery::class;
    }
}
