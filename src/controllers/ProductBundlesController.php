<?php

namespace tde\craft\commerce\bundles\controllers;

use craft\commerce\elements\Product;
use craft\errors\ElementNotFoundException;
use craft\errors\MissingComponentException;
use craft\errors\SiteNotFoundException;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use tde\craft\commerce\bundles\assetbundles\productbundles\ProductBundlesAsset;
use tde\craft\commerce\bundles\elements\ProductBundle;
use tde\craft\commerce\bundles\helpers\ProductBundleHelper;
use tde\craft\commerce\bundles\Plugin;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class ProductBundlesController
 *
 * @package tde\craft\commerce\bundles\controllers
 */
class ProductBundlesController extends Controller
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

        return $this->renderTemplate('commerce-product-bundles/product-bundles/index');
    }

    /**
     * @param int|null $productBundleId
     * @param string|null $siteHandle
     * @param ProductBundle|null $productBundle
     * @param array $variables
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws SiteNotFoundException
     */
    public function actionEdit(
        int $productBundleId = null,
        string $siteHandle = null,
        ProductBundle $productBundle = null,
        array $variables = []
    ): Response {
        $this->requireCpRequest();

        // determine site
        if (\Craft::$app->getIsMultiSite()) {
            $variables['siteIds'] = \Craft::$app->getSites()->getEditableSiteIds();
        } else {
            $variables['siteIds'] = [\Craft::$app->getSites()->getPrimarySite()->id];
        }

        if ($siteHandle !== null) {
            $variables['site'] = \Craft::$app->getSites()->getSiteByHandle($siteHandle);

            if (!$variables['site']) {
                throw new NotFoundHttpException('Invalid site handle: ' . $siteHandle);
            }
        }

        if (empty($variables['site'])) {
            $variables['site'] = \Craft::$app->getSites()->currentSite;

            if (!in_array($variables['site']->id, $variables['siteIds'], false)) {
                $variables['site'] = \Craft::$app->getSites()->getSiteById($variables['siteIds'][0]);
            }
        } else {
            if (!in_array($variables['site']->id, $variables['siteIds'], false)) {
                throw new ForbiddenHttpException('User not permitted to edit content in this site');
            }
        }

        // get product bundle or generate new
        if (!$productBundle) {
            if ($productBundleId) {
                if (!$productBundle = Plugin::getInstance()->productBundleService->getProductBundleById($productBundleId, $variables['site']->id)) {
                    throw new NotFoundHttpException();
                }
            } else {
                $productBundle = new ProductBundle();
                $productBundle->siteId = $variables['site']->id;
                $productBundle->enabled = true;
            }
        }

        // Enable Live Preview?
        if (
            !\Craft::$app->getRequest()->isMobileBrowser(true)
            && ProductBundleHelper::isProductBundleTemplateValid($productBundle, $variables['site']->id)
        ) {
            $this->getView()->registerJs('Craft.LivePreview.init(' . Json::encode([
                    'fields' => '#fields > .flex-fields > .field',
                    'extraFields' => '#details',
                    'previewUrl' => $productBundle->getUrl(),
                    'previewAction' => \Craft::$app->getSecurity()->hashData('commerce-product-bundles/products-bundles-preview/preview-product-bundle'),
                    'previewParams' => [
                        'productBundleId' => $productBundle->id,
                        'siteId' => $productBundle->siteId,
                    ]
                ]) . ');');

            $variables['showPreviewBtn'] = true;

            // Should we show the Share button too?
            if ($productBundle->id !== null) {
                // If the product is enabled, use its main URL as its share URL.
                if ($productBundle->getStatus() == Product::STATUS_LIVE) {
                    $variables['shareUrl'] = $productBundle->getUrl();
                } else {
                    $variables['shareUrl'] = UrlHelper::actionUrl('commerce-product-bundles/products-bundles-preview/share-product-bundle', [
                        'productBundleId' => $productBundle->id,
                        'siteId' => $productBundle->siteId
                    ]);
                }
            }
        } else {
            $variables['showPreviewBtn'] = false;
        }

        $variables['productBundle'] = $productBundle;
        $this->_prepVariables($variables);

        return $this->renderTemplate('commerce-product-bundles/product-bundles/_edit', $variables);
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

        $productBundle = ProductBundleHelper::populateProductBundleFromPost($productBundle);

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
