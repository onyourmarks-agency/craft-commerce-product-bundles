<?php

namespace tde\craft\commerce\bundles\services;

use craft\base\ElementInterface;
use craft\commerce\elements\Product;
use craft\commerce\elements\Variant;
use craft\commerce\models\LineItem;
use craft\db\Query;
use craft\errors\ElementNotFoundException;
use tde\craft\commerce\bundles\elements\ProductBundle;
use tde\craft\commerce\bundles\models\ProductBundleProduct;
use tde\craft\commerce\bundles\records\ProductBundleProduct as ProductBundleProductRecord;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * Class ProductBundleService
 * @package tde\craft\commerce\bundles\services
 */
class ProductBundleService extends Component
{
    /**
     * @param int $id
     * @param null $siteId
     * @return ElementInterface|null
     */
    public function getProductBundleById(int $id, $siteId = null)
    {
        return \Craft::$app->getElements()->getElementById(
            $id,
            ProductBundle::class,
            $siteId,
            [
                'status' => null
            ]
        );
    }

    /**
     * @param ProductBundle $productBundle
     * @return bool
     * @throws \Throwable
     * @throws ElementNotFoundException
     * @throws Exception
     */
    public function save(ProductBundle $productBundle)
    {
        if (!\Craft::$app->getElements()->saveElement($productBundle)) {
            return false;
        }

        $this->deleteAllProductsForBundle($productBundle);

        foreach ($productBundle->getProducts() as $product) {
            $productBundleProduct = new ProductBundleProduct();
            $productBundleProduct->setProductBundle($productBundle);
            $productBundleProduct->setProduct($product['product']);
            $productBundleProduct->setQty($product['qty']);

            if (!$productBundleProduct->toRecord()->save()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param ProductBundle $productBundle
     *
     * @return array
     */
    public function getProductsForBundle(ProductBundle $productBundle)
    {
        $records = ProductBundleProductRecord::find()
            ->where(['productBundleId' => $productBundle->getId()])
            ->all();

        return array_map(function (ProductBundleProductRecord $record) {
            return [
                'product' => $record->getProduct(),
                'qty' => $record->getQty(),
            ];
        }, $records);
    }

    /**
     * @param Product $product
     * @return array
     * @throws InvalidConfigException
     */
    public function getProductBundlesByProduct(Product $product)
    {
        $productBundleIds = (new Query())
            ->select(['bundles.id'])
            ->from('{{%commerce_product_bundles_bundles}} bundles')
            ->innerJoin('{{%commerce_product_bundles_bundles_products}} products', 'products.productBundleId = bundles.id')
            ->andWhere('products.productId = ' . (int) $product->getId())
            ->column();

        $productBundles = [];
        foreach ($productBundleIds as $productBundleId) {
            $productBundle = ProductBundle::findOne(['id' => $productBundleId]);

            if ($productBundle && $this->isPurchasable($productBundle)) {
                $productBundles[] = $productBundle;
            }
        }

        return $productBundles;
    }

    /**
     * @param LineItem $lineItem
     * @return int
     */
    public function getOrderableQuantity(LineItem $lineItem)
    {
        $orderableQuantity = 9999999999;

        foreach ($lineItem->getOptions()['productBundleProductsVariantIds'] as $variantId) {
            $variant = Variant::findOne(['id' => $variantId]);
            if (!$variant->hasUnlimitedStock) {
                if (is_null($orderableQuantity)) {
                    $orderableQuantity = $variant->stock;
                } else if ($variant->stock < $orderableQuantity) {
                    $orderableQuantity = $variant->stock;
                }
            }
        }

        return $orderableQuantity;
    }

    /**
     * @param ProductBundle $productBundle
     */
    protected function deleteAllProductsForBundle(ProductBundle $productBundle)
    {
        ProductBundleProductRecord::deleteAll(['productBundleId' => $productBundle->getId()]);
    }

    /**
     * Check if the product bundle is available for purchase
     *
     * @param ProductBundle $productBundle
     * @return bool
     * @throws InvalidConfigException
     */
    protected function isPurchasable(ProductBundle $productBundle)
    {
        // not enabled
        foreach ($productBundle->getProducts() as $product) {
            if (!$product->enabled) {
                return false;
            }
        }

        // not in stock
        if (!$productBundle->hasStock()) {
            return false;
        }

        return true;
    }
}
