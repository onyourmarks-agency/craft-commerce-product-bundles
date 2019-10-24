<?php

namespace tde\craft\commerce\bundles\controllers;

use craft\errors\MissingComponentException;
use craft\web\Controller;
use tde\craft\commerce\bundles\elements\ProductBundle;
use tde\craft\commerce\bundles\Plugin;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Class SettingsController
 *
 * @package tde\craft\commerce\bundles\controllers
 */
class SettingsController extends Controller
{
    /**
     * @return string
     *
     * @throws BadRequestHttpException
     */
    public function actionIndex()
    {
        $this->requireCpRequest();

        return $this->renderTemplate('commerce-product-bundles/settings', [
            'fieldLayout' => \Craft::$app->getFields()->getLayoutByType(ProductBundle::class)
        ]);
    }

    /**
     * @return Response|null
     * @throws BadRequestHttpException
     * @throws MissingComponentException
     * @throws Exception
     */
    public function actionSave()
    {
        $this->requireCpRequest();
        $this->requirePostRequest();

        $settings = Plugin::$instance->getSettings();

        // Set the variant field layout
        $fieldLayout = \Craft::$app->getFields()->assembleLayoutFromPost();
        $fieldLayout->type = ProductBundle::class;

        if (!\Craft::$app->getFields()->saveLayout($fieldLayout)) {
            \Craft::$app->getSession()->setError(\Craft::t('commerce-product-bundles', 'Could not save field layout.'));

            return null;
        }

        \Craft::$app->getSession()->setNotice(\Craft::t('commerce-product-bundles', 'Field layout saved.'));

        return $this->redirectToPostedUrl($settings);
    }
}