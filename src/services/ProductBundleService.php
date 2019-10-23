<?php

namespace tde\craft\commerce\bundles\services;

use craft\base\ElementInterface;
use craft\commerce\elements\Product;
use craft\errors\ElementNotFoundException;
use tde\craft\commerce\bundles\elements\ProductBundle;
use tde\craft\commerce\bundles\models\ProductBundleProduct;
use tde\craft\commerce\bundles\records\ProductBundle as ProductBundleRecord;
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
        return \Craft::$app->getElements()->getElementById($id, ProductBundle::class, $siteId);
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
        $records = ProductBundleRecord::find()
            ->where(['productBundleId' => $productBundle->getId()])
            ->all();

        return array_map(function (ProductBundleProduct $record) {
            return $record->getProduct();
        }, $records);
    }

    /**
     * @param ProductBundle $productBundle
     */
    protected function deleteAllProductsForBundle(ProductBundle $productBundle)
    {
        ProductBundleRecord::deleteAll(['productBundleId' => $productBundle->getId()]);
    }
}