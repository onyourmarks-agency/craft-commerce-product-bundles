<?php
declare(strict_types=1);

namespace tde\craft\commerce\bundles\events;

use craft\commerce\elements\Product;
use yii\base\Event;

class CustomizeProductBundleSnapshotFieldsEvent extends Event
{
    /**
     * @var Product[] The products
     */
    public array $products;

    /**
     * @var array|null The fields to be captured
     */
    public ?array $fields = null;
}
