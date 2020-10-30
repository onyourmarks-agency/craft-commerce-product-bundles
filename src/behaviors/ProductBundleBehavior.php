<?php

namespace tde\craft\commerce\bundles\behaviors;

use tde\craft\commerce\bundles\elements\db\ProductBundleQuery;
use tde\craft\commerce\bundles\elements\ProductBundle;
use yii\base\Behavior;

/**
 * @package tde\craft\commerce\bundles\behaviors
 */
class ProductBundleBehavior extends Behavior
{
    /**
     * @param mixed|null $criteria
     *
     * @return ProductBundleQuery
     */
    public function productBundles($criteria = null): ProductBundleQuery
    {
        /** @var ProductBundleQuery $query */
        $query = ProductBundle::find();
        if ($criteria) {
            \Craft::configure($query, $criteria);
        }

        return $query;
    }
}
