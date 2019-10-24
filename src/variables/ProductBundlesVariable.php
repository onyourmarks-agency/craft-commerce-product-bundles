<?php

namespace tde\craft\commerce\bundles\variables;

use craft\commerce\elements\Product;
use tde\craft\commerce\bundles\Plugin;

/**
 * Class ProductBundlesVariable
 *
 * @package tde\craft\commerce\bundles\variables
 */
class ProductBundlesVariable
{
    public function getProductBundles(Product $product)
    {
        return Plugin::$instance->productBundleService->getProductBundlesByProduct($product);
    }
}