<?php

namespace tde\craft\commerce\bundles\models;

use craft\base\Model;
use craft\commerce\elements\Product;
use tde\craft\commerce\bundles\elements\ProductBundle as ProductBundleElement;
use tde\craft\commerce\bundles\records\ProductBundleProduct as ProductBundleProductRecord;

/**
 * Class ProductBundleProduct
 *
 * @package tde\craft\commerce\bundles\models
 */
class ProductBundleProduct extends Model
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var ProductBundle
     */
    public $productBundle;

    /**
     * @var Product
     */
    public $product;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return ProductBundle
     */
    public function getProductBundle(): ProductBundle
    {
        return $this->productBundle;
    }

    /**
     * @param ProductBundleElement $productBundle
     */
    public function setProductBundle(ProductBundleElement $productBundle)
    {
        $this->productBundle = $productBundle;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * @return ProductBundleProductRecord
     */
    public function toRecord()
    {
        $record = new ProductBundleProductRecord();
        $record->setAttribute('id', $this->getId());
        $record->setAttribute('productBundleId', $this->getProductBundle()->id);
        $record->setAttribute('productId', $this->getProduct()->id);

        return $record;
    }
}