<?php

namespace oym\craft\commerce\bundles\models;

use craft\base\Model;
use craft\behaviors\FieldLayoutBehavior;
use craft\models\FieldLayout;

class Settings extends Model
{
    /**
     * @var int
     */
    public $fieldLayoutId;

    /**
     * @var array
     */
    public $siteSettings;

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
