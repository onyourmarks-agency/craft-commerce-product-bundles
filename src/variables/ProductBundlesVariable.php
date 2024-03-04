<?php

namespace oym\craft\commerce\bundles\variables;

use craft\commerce\elements\Product;
use oym\craft\commerce\bundles\elements\ProductBundle;
use oym\craft\commerce\bundles\helpers\ProductBundleHelper;
use oym\craft\commerce\bundles\Plugin;
use yii\base\InvalidConfigException;

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
