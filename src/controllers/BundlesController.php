<?php

namespace tde\craft\commerce\bundles\controllers;

use craft\commerce\elements\Product;
use craft\errors\ElementNotFoundException;
use craft\errors\MissingComponentException;
use craft\helpers\DateTimeHelper;
use craft\helpers\Localization;
use craft\web\Controller;
use tde\craft\commerce\bundles\assetbundles\productbundles\ProductBundlesAsset;
use tde\craft\commerce\bundles\elements\ProductBundle;
use tde\craft\commerce\bundles\Plugin;
use yii\base\Exception;
use yii\base\InvalidConfigException;
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
     * @throws InvalidConfigException
     */
    public function actionIndex(): Response
    {
        $this->requireCpRequest();

        $this->view->registerTranslations('commerce-product-bundles', ['Add product bundle']);
        $this->view->registerAssetBundle(ProductBundlesAsset::class);

        return $this->renderTemplate('commerce-product-bundles/bundles/index');
    }

    /**
     * @param int|null $productBundleId
     * @param ProductBundle|null $productBundle
     * @param array $variables
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws InvalidConfigException
     * @throws NotFoundHttpException
     */
    public function actionEdit(int $productBundleId = null, ProductBundle $productBundle = null, array $variables = []): Response
    {
        $this->requireCpRequest();

        if ($productBundleId) {
            if (!$productBundle = Plugin::getInstance()->productBundleService->getProductBundleById($productBundleId)) {
                throw new NotFoundHttpException();
            }
        } else {
            $productBundle = $productBundle ?? new ProductBundle();
        }

        $variables['productBundle'] = $productBundle;
        $this->_prepVariables($variables);

        return $this->renderTemplate('commerce-product-bundles/bundles/_edit', $variables);
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

        $productBundle->title = $request->getBodyParam('title', $productBundle->title);
        $productBundle->setProducts($request->getBodyParam('products'));
        $productBundle->setFieldValuesFromRequest('fields');

        // meta
        $productBundle->postDate = $request->getBodyParam('postDate')['date'] ? DateTimeHelper::toDateTime($request->getBodyParam('postDate')) ?: null : null;
        $productBundle->expiryDate = $request->getBodyParam('expiryDate')['date'] ? DateTimeHelper::toDateTime($request->getBodyParam('expiryDate')) ?: null : null;

        $productBundle->siteId = $siteId ?? $productBundle->siteId;
        $productBundle->enabled = (bool) $request->getBodyParam('enabled');
        $productBundle->enabledForSite = (bool) $request->getBodyParam('enabledForSite', $productBundle->enabledForSite);

        $productBundle->price = Localization::normalizeNumber($request->getBodyParam('price'));
        $productBundle->sku = $request->getBodyParam('sku');

        // save
        if (!Plugin::$instance->productBundleService->save($productBundle)) {
            \Craft::$app->getSession()->setError(\Craft::t('commerce-product-bundles', 'Couldn’t save product bundle.'));
            \Craft::$app->getUrlManager()->setRouteParams([
                'productBundle' => $productBundle
            ]);

            return null;
        }

        \Craft::$app->getSession()->setNotice(\Craft::t('commerce-product-bundles', 'Product bundle saved.'));

        return $this->redirectToPostedUrl($productBundle);
    }

    /**
     * @param array $variables
     * @throws InvalidConfigException
     */
    private function _prepVariables(array &$variables)
    {
        $variables['tabs'] = [];

        /** @var ProductBundle $productBundle */
        $productBundle = $variables['productBundle'];

        $form = $productBundle->getFieldLayout()->createForm($productBundle);
        $variables['tabs'] = $form->getTabMenu();
        $variables['fieldsHtml'] = $form->render();

        $variables['productElementType'] = Product::class;
        $variables['siteIds'] = \Craft::$app->getSites()->getAllSiteIds();
        $variables['enabledSiteIds'] = \Craft::$app->getSites()->getAllSiteIds();
    }
}
