<?php

namespace tde\craft\commerce\bundles\helpers;

use craft\helpers\DateTimeHelper;
use craft\helpers\Localization;
use craft\helpers\StringHelper;
use craft\web\Request;
use tde\craft\commerce\bundles\elements\ProductBundle;
use tde\craft\commerce\bundles\models\Settings;
use tde\craft\commerce\bundles\Plugin;
use yii\base\InvalidCallException;
use yii\web\NotFoundHttpException;

/**
 * @package tde\craft\commerce\bundles\helpers
 */
class ProductBundleHelper
{
    /**
     * @param Request|null $request
     *
     * @return ProductBundle
     * @throws NotFoundHttpException
     */
    public static function productBundleFromPost(Request $request = null): ProductBundle
    {
        if ($request === null) {
            $request = \Craft::$app->getRequest();
        }

        $productBundleId = $request->getBodyParam('productBundleId');
        $siteId = $request->getBodyParam('siteId');

        if ($productBundleId) {
            $productBundle = Plugin::getInstance()->productBundleService->getProductBundleById($productBundleId, $siteId);

            if (!$productBundle) {
                throw new NotFoundHttpException(\Craft::t('commerce-product-bundles', 'No product bundle with the ID “{id}”', ['id' => $productBundleId]));
            }
        } else {
            $productBundle = new ProductBundle();
            $productBundle->siteId = $siteId ?? $productBundle->siteId;
        }

        return $productBundle;
    }

    /**
     * @param ProductBundle|null $productBundle
     * @param Request|null $request
     *
     * @return ProductBundle
     * @throws NotFoundHttpException
     */
    public static function populateProductBundleFromPost(ProductBundle $productBundle = null, Request $request = null): ProductBundle
    {
        if ($request === null) {
            $request = \Craft::$app->getRequest();
        }

        if ($productBundle === null) {
            $productBundle = static::productBundleFromPost($request);
        }

        $productBundle->title = $request->getBodyParam('title', $productBundle->title);
        $productBundle->slug = $request->getBodyParam('slug', StringHelper::slugify($productBundle->title));

        $productBundle->setProducts($request->getBodyParam('products') ?: []);
        $productBundle->setFieldValuesFromRequest('fields');

        // meta
        $productBundle->postDate = $request->getBodyParam('postDate')['date'] ? DateTimeHelper::toDateTime($request->getBodyParam('postDate')) ?: null : null;
        $productBundle->expiryDate = $request->getBodyParam('expiryDate')['date'] ? DateTimeHelper::toDateTime($request->getBodyParam('expiryDate')) ?: null : null;

        $productBundle->siteId = $siteId ?? $productBundle->siteId;
        $productBundle->enabled = (bool) $request->getBodyParam('enabled');
        $productBundle->enabledForSite = (bool) $request->getBodyParam('enabledForSite', $productBundle->enabledForSite);

        $productBundle->price = Localization::normalizeNumber($request->getBodyParam('price'));
        $productBundle->sku = $request->getBodyParam('sku');

        return $productBundle;
    }

    /**
     * @param ProductBundle $productBundle
     * @param int $siteId
     *
     * @return bool
     */
    public static function isProductBundleTemplateValid(ProductBundle $productBundle, int $siteId): bool
    {
        return !is_null(self::getSiteSettings($productBundle, $siteId));
    }

    /**
     * Get the defined site settings for the site or return null
     *
     * @param ProductBundle $productBundle
     * @param int|null $siteId
     *
     * @return array|null
     */
    public static function getSiteSettings(ProductBundle $productBundle, int $siteId = null)
    {
        if (!$siteId) {
            $siteId = $productBundle->siteId;
        }

        /** @var Settings $pluginSettings */
        $pluginSettings = Plugin::getInstance()->getSettings();
        $siteSettings = $pluginSettings->siteSettings;

        return $siteSettings[$siteId] ?? null;
    }

    /**
     * Get the quantity set for the given product-id in the bundle
     *
     * @param ProductBundle $productBundle
     * @param int $productId
     *
     * @return int
     */
    public static function getProductQuantity(ProductBundle $productBundle, int $productId)
    {
        foreach ($productBundle->getProducts() as $productSet) {
            if ($productSet['product']->id === $productId) {
                return (int) $productSet['qty'];
            }
        }

        throw new InvalidCallException('Requesting product quantity of a product not in the given bundle');
    }
}
