<?php

namespace oym\craft\commerce\bundles\records;

use craft\commerce\elements\Product;
use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

class ProductBundleProduct extends ActiveRecord
{
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
     * @return Product
     */
    public function getProduct(): Product
    {
        return Product::findOne([
            'id' => $this->productId,
            'status' => null
        ]);
    }

    /**
     * @return int
     */
    public function getQty()
    {
        return $this->qty;
    }
}
