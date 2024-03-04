<?php

namespace oym\craft\commerce\bundles;

use craft\base\Model;
use craft\commerce\elements\Variant;
use craft\commerce\events\LineItemEvent;
use craft\commerce\services\LineItems;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\i18n\PhpMessageSource;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\services\Elements;
use craft\web\twig\variables\Cp;
use oym\craft\commerce\bundles\behaviors\ProductBundleBehavior;
use oym\craft\commerce\bundles\elements\ProductBundle;
use oym\craft\commerce\bundles\fields\ProductBundleField;
use oym\craft\commerce\bundles\helpers\ProductBundleHelper;
use oym\craft\commerce\bundles\models\Settings;
use oym\craft\commerce\bundles\services\ProductBundleService;
use oym\craft\commerce\bundles\variables\ProductBundlesVariable;
use yii\base\Event;

/**
 * @property ProductBundleService $productBundleService
 */
class Plugin extends \craft\base\Plugin
{
    public static self $instance;
    public bool $hasCpSettings = true;

    public function init()
    {
        parent::init();
        self::$instance = $this;

        $this->setComponents([
            'productBundleService' => ProductBundleService::class,
        ]);

        \Craft::$app->i18n->translations['commerce-product-bundles'] = [
            'class' => PhpMessageSource::class,
            'sourceLanguage' => 'en-US',
            'basePath' => __DIR__ . '/translations',
            'allowOverrides' => true,
        ];

        $this->_registerEvents();
        $this->_registerCpNavItem();
        $this->_registerCpRoutes();
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    public function getSettingsResponse(): mixed
    {
        return \Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('commerce/product-bundles/settings'));
    }

    protected function _registerEvents()
    {
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = ProductBundle::class;
            }
        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $e) {
                /** @var CraftVariable $variable */
                $variable = $e->sender;
                $variable->set('commerceProductBundles', ProductBundlesVariable::class);
                $variable->attachBehaviors([
                    ProductBundleBehavior::class,
                ]);
            }
        );

        Event::on(
            LineItems::class,
            LineItems::EVENT_POPULATE_LINE_ITEM,
            function (LineItemEvent $lineItemEvent) {
                $lineItem = $lineItemEvent->lineItem;
                $purchasable = $lineItem->getPurchasable();

                if (!$purchasable instanceof ProductBundle) {
                    return;
                }

                if (!isset($lineItem->snapshot['options'][ProductBundle::KEY_PRODUCTS])) {
                    return;
                }

                $lineItem->snapshot['options'][ProductBundle::KEY_PRODUCTS_META] = [];

                foreach ($lineItem->snapshot['options'][ProductBundle::KEY_PRODUCTS] as $productId => $variantId) {
                    $variant = Variant::findOne(['id' => $variantId]);
                    $qty = ProductBundleHelper::getProductQuantity($purchasable, $productId);

                    $lineItem->snapshot['options'][ProductBundle::KEY_PRODUCTS_META][] = [
                        'variant' => $variant->getSnapshot(),
                        'qty' => $qty,
                    ];
                }
            }
        );

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = ProductBundleField::class;
            }
        );
    }

    protected function _registerCpNavItem()
    {
        Event::on(
            Cp::class,
            Cp::EVENT_REGISTER_CP_NAV_ITEMS,
            function (RegisterCpNavItemsEvent $event) {
                foreach ($event->navItems as $navKey => $navItem) {
                    if ($navItem['url'] === 'commerce') {
                        $keys = array_keys($event->navItems[$navKey]['subnav']);
                        $pos = array_search('products', $keys) + 1;

                        $event->navItems[$navKey]['subnav'] = array_merge(
                            array_slice(
                                $event->navItems[$navKey]['subnav'],
                                0,
                                $pos
                            ),
                            [
                                'product-bundles' => [
                                    'label' => 'Product bundles',
                                    'url' => 'commerce/product-bundles',
                                ]
                            ],
                            array_slice($event->navItems[$navKey]['subnav'], $pos)
                        );
                    }
                }
            }
        );
    }

    protected function _registerCpRoutes()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules, [
                    'commerce/product-bundles' => 'commerce-product-bundles/product-bundles/index',
                    'commerce/product-bundles/new' => 'commerce-product-bundles/product-bundles/edit',
                    'commerce/product-bundles/new/<siteHandle:{handle}>' => 'commerce-product-bundles/product-bundles/edit',
                    'commerce/product-bundles/<productBundleId:\d+>' => 'commerce-product-bundles/product-bundles/edit',
                    'commerce/product-bundles/<productBundleId:\d+>/<siteHandle:{handle}>' => 'commerce-product-bundles/product-bundles/edit',
                    'commerce/product-bundles/settings' => 'commerce-product-bundles/settings',
                ]);
            }
        );
    }
}
