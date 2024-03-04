<?php

namespace oym\craft\commerce\bundles\models;

use craft\base\Model;
use craft\commerce\elements\Product;
use oym\craft\commerce\bundles\elements\ProductBundle as ProductBundleElement;
use oym\craft\commerce\bundles\records\ProductBundleProduct as ProductBundleProductRecord;

class ProductBundleProduct extends Model
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var ProductBundleElement
     */
    public $productBundle;

    /**
     * @var Product
     */
    public $product;

    /**
     * @var int
     */
    public $qty;

    /**
     * @return int
     */
    public function getId()
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
     * @return ProductBundleElement
     */
    public function getProductBundle(): ProductBundleElement
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
     * @return int
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param int $qty
     */
    public function setQty(int $qty)
    {
        $this->qty = $qty;
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
        $record->setAttribute('qty', $this->qty);

        return $record;
    }
}
