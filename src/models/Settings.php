<?php

namespace tde\craft\commerce\bundles\models;

use craft\base\Model;
use craft\behaviors\FieldLayoutBehavior;
use craft\models\FieldLayout;

/**
 * Class Settings
 *
 * @package tde\craft\commerce\bundles\models
 */
class Settings extends Model
{
    /**
     * @var int
     */
    public $fieldLayoutId;

    /**
     * @inheritDoc
     */
    public function getFieldLayout(): FieldLayout
    {
        $behavior = $this->getBehavior('fieldLayout');

        return $behavior->getFieldLayout();
    }

    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return [
            'fieldLayout' => [
                'class' => FieldLayoutBehavior::class,
                'elementType' => ProductBundle::class,
                'idAttribute' => 'fieldLayoutId'
            ]
        ];
    }
}