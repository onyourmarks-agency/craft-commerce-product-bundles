<?php

namespace tde\craft\commerce\bundles;

use tde\craft\commerce\bundles\elements\ProductBundle;

use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\services\Elements;
use craft\web\twig\variables\Cp;
use yii\base\Event;

/**
 * Class Plugin
 *
 * @package tde\craft\commerce\bundles
 */
class Plugin extends \craft\base\Plugin
{
    /**
     * @var self
     */
    public static $instance;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        self::$instance = $this;

        $this->_registerCpNavItem();
        $this->_registerEvents();
        $this->_registerCpRoutes();
    }

    /**
     * Register our Control Panel navigation item under 'Products' of the Commerce subnav
     */
    protected function _registerCpNavItem()
    {
        Event::on(
            Cp::class,
            Cp::EVENT_REGISTER_CP_NAV_ITEMS,
            function(RegisterCpNavItemsEvent $event) {
                if (isset($event->navItems['commerce'])) {
                    $keys = array_keys($event->navItems['commerce']['subnav']);
                    $pos = array_search('products', $keys) + 1;

                    $event->navItems['commerce']['subnav'] = array_merge(
                        array_slice(
                            $event->navItems['commerce']['subnav'],
                            0,
                            $pos
                        ),
                        [
                            'product-bundles' => [
                                'label' => 'Product bundles',
                                'url' => 'commerce/product-bundles',
                            ]
                        ],
                        array_slice($event->navItems['commerce']['subnav'], $pos)
                    );
                }
            }
        );
    }

    /**
     * Register events
     */
    protected function _registerEvents()
    {
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = ProductBundle::class;
            }
        );
    }

    /**
     * Register Control Panel routes
     */
    protected function _registerCpRoutes()
    {

    }
}