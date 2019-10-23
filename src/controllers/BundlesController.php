<?php

namespace tde\craft\commerce\bundles\controllers;

use craft\commerce\elements\Product;
use craft\errors\ElementNotFoundException;
use craft\errors\MissingComponentException;
use craft\helpers\DateTimeHelper;
use craft\helpers\Localization;
use craft\web\Controller;
use tde\craft\commerce\bundles\elements\ProductBundle;
use tde\craft\commerce\bundles\Plugin;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class BundlesController
 *
 * @package tde\craft\commerce\bundles\controllers
 */
class BundlesController extends Controller
{
    /**
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionIndex(): Response
    {
        $this->requireCpRequest();

        return $this->renderTemplate('commerce-product-bundles/bundles/index');
    }

    /**
     * @param int|null $productBundleId
     * @return Response
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function actionEdit(int $productBundleId = null): Response
    {
        $this->requireCpRequest();

        if ($productBundleId) {
            if (!$productBundle = ProductBundle::findOne(['id' => $productBundleId])) {
                throw new NotFoundHttpException();
            }
        } else {
            $productBundle = new ProductBundle();
        }

        return $this->renderTemplate('commerce-product-bundles/bundles/_edit', [
            'productBundle' => $productBundle,
            'productElementType' => Product::class,
        ]);
    }

    /**
     * @return Response|null
     * @throws BadRequestHttpException
     * @throws MissingComponentException
     * @throws \Throwable
     * @throws ElementNotFoundException
     * @throws Exception
     */
    public function actionSave()
    {
        $this->requireCpRequest();
        $this->requirePostRequest();

        $request = \Craft::$app->getRequest();

        $productBundleId = $request->getBodyParam('productBundleId');
        $siteId = $request->getBodyParam('siteId');

        if ($productBundleId) {
            $productBundle = Plugin::getInstance()->productBundleService->getProductBundleById($productBundleId, $siteId);

            if (!$productBundle) {
                throw new \Exception(\Craft::t('commerce-product-bundles', 'No product bundle with the ID “{id}”', ['id' => $productBundleId]));
            }
        } else {
            $productBundle = new ProductBundle();
        }

        $productBundle->siteId = $siteId ?? $productBundle->siteId;
        $productBundle->enabled = (bool )$request->getBodyParam('enabled');
        $productBundle->price = Localization::normalizeNumber($request->getBodyParam('price'));
        $productBundle->sku = $request->getBodyParam('sku');
        $productBundle->setProducts($request->getBodyParam('products'));

        if ($postDate = $request->getBodyParam('postDate')) {
            $productBundle->postDate = DateTimeHelper::toDateTime($postDate) ?: null;
        }

        if ($expiryDate = $request->getBodyParam('expiryDate')) {
            $productBundle->expiryDate = DateTimeHelper::toDateTime($expiryDate) ?: null;
        }

        $productBundle->taxCategoryId = $request->getBodyParam('taxCategoryId');
        $productBundle->shippingCategoryId = $request->getBodyParam('shippingCategoryId');

        $productBundle->enabledForSite = (bool) $request->getBodyParam('enabledForSite', $productBundle->enabledForSite);
        $productBundle->title = $request->getBodyParam('title', $productBundle->title);

        $productBundle->setFieldValuesFromRequest('fields');

        if (!Plugin::$instance->productBundleService->save($productBundle)) {
            \Craft::$app->getSession()->setError(\Craft::t('commerce-product-bundles', 'Couldn’t save product bundle.'));

            // Send the category back to the template
            \Craft::$app->getUrlManager()->setRouteParams([
                'productBundle' => $productBundle
            ]);

            return null;
        }

        \Craft::$app->getSession()->setNotice(\Craft::t('commerce-product-bundles', 'Product bundle saved.'));

        return $this->redirectToPostedUrl($productBundle);
    }
}