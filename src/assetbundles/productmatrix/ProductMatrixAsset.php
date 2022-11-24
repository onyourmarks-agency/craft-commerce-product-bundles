<?php

namespace tde\craft\commerce\bundles\assetbundles\productmatrix;

use craft\commerce\web\assets\commercecp\CommerceCpAsset;
use craft\web\AssetBundle;
use craft\web\View;

/**
 * @package tde\craft\commerce\bundles\assetbundles\productmatrix
 */
class ProductMatrixAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = __DIR__ . '/dist';

        $this->depends = [
            CommerceCpAsset::class,
        ];

        $this->js = [
            'js/ProductMatrix.js',
        ];

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function registerAssetFiles($view)
    {
        parent::registerAssetFiles($view);

        if ($view instanceof View) {
            $view->registerTranslations('commerce-product-bundles', [
                'Actions',
                'Add a variant',
                'Add variant above',
                'Are you sure you want to delete the selected variants?',
                'Collapse',
                'Default',
                'Disable',
                'Disabled',
                'Enable',
                'Expand',
                'Set as the default variant',
            ]);
        }
    }
}
