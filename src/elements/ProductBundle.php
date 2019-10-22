<?php

namespace tde\craft\commerce\bundles\elements;

use craft\commerce\base\Purchasable;

/**
 * Class ProductBundle
 *
 * @package tde\craft\commerce\bundles\elements
 */
class ProductBundle extends Purchasable
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return \Craft::t('commerce-product-bundles', 'Product bundle');
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->title;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public static function hasUris(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return true;
    }
}