<?php

namespace tde\craft\commerce\bundles\records;

use craft\db\ActiveRecord;
use craft\records\Element;
use yii\db\ActiveQueryInterface;

/**
 * Class ProductBundle
 *
 * @package tde\craft\commerce\bundles\records
 */
class ProductBundle extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%commerce_product_bundles_bundles}}';
    }

    /**
     * @return ActiveQueryInterface
     */
    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }
}
