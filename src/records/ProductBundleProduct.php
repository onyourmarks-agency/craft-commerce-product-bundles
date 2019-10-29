<?php

namespace tde\craft\commerce\bundles\records;

use craft\commerce\elements\Product;
use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

/**
 * Class ProductBundleProduct
 *
 * @package tde\craft\commerce\bundles\records
 */
class ProductBundleProduct extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName(): string
    {
        return '{{%commerce_product_bundles_bundles_products}}';
    }

    /**
     * @return ActiveQueryInterface
     */
    public function getProductBundle(): ActiveQueryInterface
    {
        return $this->hasOne(Product::class, ['id' => 'productBundleId']);
    }

    /**
     * @return Product|null
     */
    public function getProduct()
    {
        return Product::findOne([
            'id' => $this->productId,
            'status' => null
        ]);
    }
}