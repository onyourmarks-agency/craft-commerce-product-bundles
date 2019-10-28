<?php

namespace tde\craft\commerce\bundles\assetbundles\productbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class ProductBundlesAsset
 *
 * @package tde\craft\commerce\bundles\assetbundles\productbundles
 */
class ProductBundlesAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@tde/craft/commerce/bundles/assetbundles/productbundles/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/ProductBundles.js',
        ];

        parent::init();
    }
}