<?php

namespace oym\craft\commerce\bundles\controllers;

use craft\errors\MissingComponentException;
use craft\web\Controller;
use oym\craft\commerce\bundles\elements\ProductBundle;
use oym\craft\commerce\bundles\models\Settings;
use oym\craft\commerce\bundles\Plugin;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class SettingsController extends Controller
{
    /**
     * @throws BadRequestHttpException
     */
    public function actionIndex(): Response
    {
        $this->requireCpRequest();

        /** @var Settings $settings */
        $settings = Plugin::$instance->getSettings();

        return $this->renderTemplate('commerce-product-bundles/settings', [
            'siteSettings' => $settings->siteSettings,
            'fieldLayout' => \Craft::$app->getFields()->getLayoutById($settings->fieldLayoutId),
        ]);
    }

    /**
     * @return Response|null
     * @throws BadRequestHttpException
     * @throws MissingComponentException
     * @throws Exception
     */
    public function actionSave(): ?Response
    {
        $this->requireCpRequest();
        $this->requirePostRequest();

        /** @var Settings $settings */
        $settings = Plugin::$instance->getSettings();

        // Site-specific settings
        $siteSettings = [];
        foreach (\Craft::$app->getSites()->getAllSites() as $site) {
            $postedSettings = \Craft::$app->getRequest()->getBodyParam('sites.' . $site->handle);

            if (!empty($postedSettings['uriFormat'])) {
                $siteSettings[$site->id] = [
                    'uriFormat' => $postedSettings['uriFormat'],
                    'template' => $postedSettings['template'],
                ];
            }
        }

        $settings->siteSettings = $siteSettings;

        // Set the bundle field layout
        $fieldLayout = \Craft::$app->getFields()->assembleLayoutFromPost();
        $fieldLayout->type = ProductBundle::class;

        if (!\Craft::$app->getFields()->saveLayout($fieldLayout)) {
            \Craft::$app->getSession()->setError(\Craft::t('commerce-product-bundles', 'Could not save field layout.'));

            return null;
        }

        $settings->fieldLayoutId = $fieldLayout->id;

        // validate
        if (!$settings->validate()) {
            \Craft::$app->getSession()->setError(\Craft::t('commerce-product-bundles', 'Couldn’t save settings.'));

            return null;
        }

        // save
        $pluginSettingsSaved = \Craft::$app->getPlugins()->savePluginSettings(Plugin::getInstance(), $settings->toArray());
        if (!$pluginSettingsSaved) {
            \Craft::$app->getSession()->setError(\Craft::t('commerce-product-bundles', 'Couldn’t save settings.'));

            return null;
        }

        \Craft::$app->getSession()->setNotice(\Craft::t('commerce-product-bundles', 'Settings saved.'));

        return $this->redirectToPostedUrl($settings);
    }
}
