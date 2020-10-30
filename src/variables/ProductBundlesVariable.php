<?php

namespace tde\craft\commerce\bundles\variables;

use craft\commerce\elements\Product;
use tde\craft\commerce\bundles\elements\ProductBundle;
use tde\craft\commerce\bundles\helpers\ProductBundleHelper;
use tde\craft\commerce\bundles\Plugin;
use yii\base\InvalidConfigException;

/**
 * Class ProductBundlesVariable
 *
 * @package tde\craft\commerce\bundles\variables
 */
class ProductBundlesVariable
{
    /**
     * @param Product $product
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function getProductBundles(Product $product)
    {
        return Plugin::$instance->productBundleService->getProductBundlesByProduct($product);
    }

    /**
     * @param ProductBundle $productBundle
     * @param int $productId
     *
     * @return int
     */
    public function getProductQuantity(ProductBundle $productBundle, int $productId)
    {
        return ProductBundleHelper::getProductQuantity($productBundle, $productId);
    }
}
