<?php

namespace oym\craft\commerce\bundles\assetbundles\productbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class ProductBundlesAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@oym/craft/commerce/bundles/assetbundles/productbundles/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/ProductBundles.js',
        ];

        parent::init();
    }
}
