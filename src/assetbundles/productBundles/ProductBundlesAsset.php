<?php

namespace tde\craft\commerce\bundles\assetbundles\productBundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class ProductBundlesAsset
 *
 * @package tde\craft\commerce\bundles\assetbundles\productBundles
 */
class ProductBundlesAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@tde/craft/commerce/bundles/assetbundles/productBundles/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/ProductBundles.js',
        ];

        parent::init();
    }
}