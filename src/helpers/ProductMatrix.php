<?php

namespace tde\craft\commerce\bundles\helpers;

use craft\commerce\elements\Product;
use craft\helpers\Html;
use craft\helpers\Json;
use tde\craft\commerce\bundles\assetbundles\productmatrix\ProductMatrixAsset;
use tde\craft\commerce\bundles\elements\ProductBundle;
use Twig\Error\Error;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * @package tde\craft\commerce\bundles\helpers
 */
class ProductMatrix
{
    /**
     * Returns the HTML for a given product bundles product matrix.
     *
     * @param ProductBundle $productBundle The product bundle element
     * @param string $name The input name (sans namespace). Default is 'products'.
     *
     * @return string The variant matrix HTML
     * @throws Error
     * @throws Exception
     * @throws Error
     * @throws InvalidConfigException
     */
    public static function getProductMatrixHtml(ProductBundle $productBundle, $name = 'products'): string
    {
        $viewService = \Craft::$app->getView();
        $id = Html::id($name);

        $html = $viewService->renderTemplate('commerce-product-bundles/bundles/_product_matrix', [
            'id' => $id,
            'name' => $name,
            'productElementType' => Product::class,
            'productBundle' => $productBundle,
            'products' => $productBundle->getProducts(),
        ]);

        // Namespace the name/ID for JS
        $namespacedName = $viewService->namespaceInputName($name);
        $namespacedId = $viewService->namespaceInputId($id);

        $namespace = $viewService->getNamespace();
        $viewService->setNamespace(null);

        // Get the field HTML
        [$fieldBodyHtml, $fieldFootHtml] = self::_getProductFieldHtml($productBundle, $namespacedName);

        $viewService->registerAssetBundle(ProductMatrixAsset::class);
        $viewService->registerJs('new Craft.Bundles.ProductMatrix(' .
            '"' . $namespacedId . '", ' .
            Json::encode($fieldBodyHtml, JSON_UNESCAPED_UNICODE) . ', ' .
            Json::encode($fieldFootHtml, JSON_UNESCAPED_UNICODE) . ', ' .
            '"' . $namespacedName . '"' .
            ');');

        $viewService->setNamespace($namespace);

        return $html;
    }


    /**
     * Returns info about each product field type for a product matrix.
     *
     * @param ProductBundle $productBundle The product bundle element
     * @param string $namespace The input namespace
     *
     * @return array
     * @throws Error
     * @throws Exception
     */
    protected static function _getProductFieldHtml(ProductBundle $productBundle, string $namespace): array
    {
        $templatesService = \Craft::$app->getView();
        $templatesService->startJsBuffer();

        $bodyHtml = $templatesService->renderTemplate('commerce-product-bundles/bundles/_product_matrix_fields', [
            'namespace' => Html::namespaceInputName('__VARIANT__', $namespace),
            'productBundle' => $productBundle,
            'productElementType' => Product::class,
        ]);

        $footHtml = $templatesService->clearJsBuffer();

        return [$bodyHtml, $footHtml];
    }
}
