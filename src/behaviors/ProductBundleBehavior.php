<?php

namespace oym\craft\commerce\bundles\behaviors;

use oym\craft\commerce\bundles\elements\db\ProductBundleQuery;
use oym\craft\commerce\bundles\elements\ProductBundle;
use yii\base\Behavior;

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
