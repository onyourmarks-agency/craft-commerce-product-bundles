<?php

namespace tde\craft\commerce\bundles\services;

use craft\base\ElementInterface;
use craft\commerce\elements\Product;
use craft\db\Query;
use craft\errors\ElementNotFoundException;
use tde\craft\commerce\bundles\elements\ProductBundle;
use tde\craft\commerce\bundles\models\ProductBundleProduct;
use tde\craft\commerce\bundles\records\ProductBundleProduct as ProductBundleProductRecord;
use yii\base\Component;
use yii\base\Exception;

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
            $productBundleProduct->setProduct($product);

            if (!$productBundleProduct->toRecord()->save()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param ProductBundle $productBundle
     *
     * @return Product[]
     */
    public function getProductsForBundle(ProductBundle $productBundle)
    {
        $records = ProductBundleProductRecord::find()
            ->where(['productBundleId' => $productBundle->getId()])
            ->all();

        return array_map(function (ProductBundleProductRecord $record) {
            return $record->getProduct();
        }, $records);
    }

    /**
     * @param Product $product
     * @return array
     */
    public function getProductBundlesByProduct(Product $product)
    {
        $productBundleIds = (new Query())
            ->select(['bundles.id'])
            ->from('{{%commerce_product_bundles_bundles}} bundles')
            ->innerJoin('{{%commerce_product_bundles_bundles_products}} products', 'products.productBundleId = bundles.id')
            ->andWhere('products.productId = ' . (int) $product->getId())
            ->column();

        return array_map(function ($productBundleId) {
            return ProductBundle::findOne(['id' => $productBundleId]);
        }, $productBundleIds);
    }

    /**
     * @param ProductBundle $productBundle
     */
    protected function deleteAllProductsForBundle(ProductBundle $productBundle)
    {
        ProductBundleProductRecord::deleteAll(['productBundleId' => $productBundle->getId()]);
    }
}