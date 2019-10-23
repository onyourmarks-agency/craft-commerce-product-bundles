<?php

namespace tde\craft\commerce\bundles\models;

use craft\base\Model;

/**
 * Class ProductBundle
 *
 * @package tde\craft\commerce\bundles\models
 */
class ProductBundle extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $id;
    public $bundleId;
    public $purchasableId;
    public $qty;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [
                [
                    'bundleId',
                    'purchasableId'
                ], 'required'
            ],
            [['qty'], 'integer', 'min' => 1],
        ];

    }
}