<?php

namespace oym\craft\commerce\bundles\controllers;

use craft\helpers\UrlHelper;
use craft\web\Controller;
use oym\craft\commerce\bundles\elements\ProductBundle;
use oym\craft\commerce\bundles\helpers\ProductBundleHelper;
use oym\craft\commerce\bundles\Plugin;
use yii\web\HttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class ProductBundlesPreviewController extends Controller
{
    protected int|bool|array $allowAnonymous = true;

    /**
     * Previews a product bundle
     *
     * @throws HttpException
     */
    public function actionPreviewProductBundle(): Response
    {
        $this->requirePostRequest();

        $productBundle = ProductBundleHelper::populateProductBundleFromPost();

        $this->enforceProductBundlePermissions($productBundle);

        return $this->showProductBundle($productBundle);
    }

    /**
     * Redirects the client to a URL for viewing a disabled product bundle on the front end.
     *
     * @param mixed $productBundleId
     * @param mixed $siteId
     * @return Response
     * @throws HttpException
     */
    public function actionShareProductBundle($productBundleId, $siteId): Response
    {
        $productBundle = Plugin::getInstance()->productBundleService->getProductBundleById($productBundleId, $siteId);

        if (!$productBundle) {
            throw new HttpException(404);
        }

        $this->enforceProductBundlePermissions($productBundle);

        // Make sure the product actually can be viewed
        if (!ProductBundleHelper::isProductBundleTemplateValid($productBundle, $productBundle->siteId)) {
            throw new HttpException(404);
        }

        // Create the token and redirect to the product URL with the token in place
        $token = \Craft::$app->getTokens()->createToken([
            'commerce-product-bundles/products-bundles-preview/view-shared-product-bundle',
            [
                'productBundleId' => $productBundle->id,
                'siteId' => $siteId
            ],
        ]);

        $url = UrlHelper::urlWithToken($productBundle->getUrl(), $token);

        return $this->redirect($url);
    }

    /**
     * Shows an product bundle/draft/version based on a token.
     *
     * @param mixed $productBundleId
     * @param mixed $site
     * @return Response|null
     * @throws HttpException
     */
    public function actionViewSharedProductBundle($productBundleId, $site = null)
    {
        $this->requireToken();

        $productBundle = Plugin::getInstance()->productBundleService->getProductBundleById($productBundleId, $site);

        if (!$productBundle) {
            throw new HttpException(404);
        }

        $this->showProductBundle($productBundle);

        return null;
    }

    /**
     * @param ProductBundle $productBundle
     * @throws HttpException
     */
    protected function enforceProductBundlePermissions(ProductBundle $productBundle)
    {
        $this->requirePermission('commerce-manageProductType');
    }

    /**
     * Displays a product bundle
     *
     * @param ProductBundle $productBundle
     * @return Response
     * @throws HttpException
     */
    protected function showProductBundle(ProductBundle $productBundle): Response
    {
        if (ProductBundleHelper::isProductBundleTemplateValid($productBundle, $productBundle->siteId)) {
            throw new ServerErrorHttpException('The product bundle ' . $productBundle->id . ' doesn\'t have a URL for the site ' . $productBundle->siteId . '.');
        }

        if (!$site = \Craft::$app->getSites()->getSiteById($productBundle->siteId)) {
            throw new ServerErrorHttpException('Invalid site ID: ' . $productBundle->siteId);
        }

        \Craft::$app->language = $site->language;

        // Have this product override any freshly queried products with the same ID/site
        if ($productBundle->id) {
            \Craft::$app->getElements()->setPlaceholderElement($productBundle);
        }

        $this->getView()->getTwig()->disableStrictVariables();

        return $this->renderTemplate(ProductBundleHelper::getSiteSettings($productBundle)['template'], [
            'productBundle' => $productBundle,
        ]);
    }
}
