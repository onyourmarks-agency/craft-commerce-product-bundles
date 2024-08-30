<?php
declare(strict_types=1);

namespace tde\craft\commerce\bundles\events;

use craft\commerce\elements\Product;
use yii\base\Event;

class CustomizeProductBundleSnapshotDataEvent extends Event
{
    /**
     * @var Product[] The products
     */
    public array $products;

    /**
     * @var array The captured data
     */
    public array $fieldData;
}
