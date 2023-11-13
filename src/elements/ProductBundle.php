<?php

namespace tde\craft\commerce\bundles\elements;

use craft\commerce\elements\Product;
use craft\commerce\events\CustomizeProductSnapshotDataEvent;
use craft\commerce\events\CustomizeProductSnapshotFieldsEvent;
use craft\commerce\models\ShippingCategory;
use craft\commerce\models\TaxCategory;
use tde\craft\commerce\bundles\elements\db\ProductBundleQuery;

use craft\elements\db\ElementQueryInterface;
use craft\db\Query;
use craft\elements\actions\Delete;
use craft\helpers\DateTimeHelper;
use craft\helpers\UrlHelper;
use craft\validators\DateTimeValidator;

use craft\commerce\Plugin as CommercePlugin;
use craft\commerce\base\Purchasable;
use craft\commerce\elements\Order;
use craft\commerce\models\LineItem;

use tde\craft\commerce\bundles\Plugin;
use tde\craft\commerce\bundles\records\ProductBundle as ProductBundleRecord;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\Expression;
use yii\validators\Validator;

/**
 * Class ProductBundle
 *
 * @package tde\craft\commerce\bundles\elements
 */
class ProductBundle extends Purchasable
{
    const EVENT_BEFORE_CAPTURE_PRODUCT_BUNDLE_SNAPSHOT = 'beforeCaptureProductBundleSnapshot';
    const EVENT_AFTER_CAPTURE_PRODUCT_BUNDLE_SNAPSHOT = 'afterCaptureProductBundleSnapshot';

    const STATUS_LIVE = 'live';
    const STATUS_PENDING = 'pending';
    const STATUS_EXPIRED = 'expired';

    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $taxCategoryId;

    /**
     * @var int
     */
    public $shippingCategoryId;

    /**
     * @var \DateTime
     */
    public $postDate;

    /**
     * @var \DateTime
     */
    public $expiryDate;

    /**
     * @var string
     */
    public $sku;

    /**
     * @var float
     */
    public $price;

    /**
     * @var Product[]
     */
    protected $_products;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return \Craft::t('commerce-product-bundles', 'Product bundle');
    }

    /**
     * @inheritdoc
     */
    public static function pluralDisplayName(): string
    {
        return \Craft::t('commerce-product-bundles', 'Product bundles');
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public static function hasUris(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => \Craft::t('commerce-product-bundles', 'All bundles'),
                'defaultSort' => ['title', 'ASC']
            ]
        ];

        return $sources;
    }

    /**
     * @inheritdoc
     */
    public static function find(): ElementQueryInterface
    {
        return new ProductBundleQuery(static::class);
    }

    /**
     * @inheritdoc
     */
    protected static function defineActions(string $source = null): array
    {
        return [
            \Craft::$app->getElements()->createAction([
                'type' => Delete::class,
                'confirmationMessage' => \Craft::t('commerce-product-bundles', 'Are you sure you want to delete the selected product bundle(s)?'),
                'successMessage' => \Craft::t('commerce-product-bundles', 'Bundles deleted.'),
            ])
        ];
    }

    /**
     * @inheritDoc
     */
    protected static function defineTableAttributes(): array
    {
        return [
            'title' => ['label' => \Craft::t('commerce-product-bundles', 'Title')],
            'sku' => ['label' => \Craft::t('commerce-product-bundles', 'SKU')],
            'price' => ['label' => \Craft::t('commerce-product-bundles', 'Price')],
            'postDate' => ['label' => \Craft::t('commerce-product-bundles', 'Post Date')],
            'expiryDate' => ['label' => \Craft::t('commerce-product-bundles', 'Expiry Date')],
        ];
    }

    /**
     * @inheritDoc
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        return [
            'price',
            'sku',
            'postDate',
            'expiryDate',
        ];
    }

    /**
     * @inheritDoc
     */
    protected static function defineSearchableAttributes(): array
    {
        return ['title', 'sku'];
    }

    /**
     * @inheritDoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            'title' => \Craft::t('commerce-product-bundles', 'Title'),
            'postDate' => \Craft::t('commerce-product-bundles', 'Post Date'),
            'expiryDate' => \Craft::t('commerce-product-bundles', 'Expiry Date'),
            'price' => \Craft::t('commerce-product-bundles', 'Price'),
        ];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->title;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getStatuses(): array
    {
        return [
            self::STATUS_LIVE => \Craft::t('commerce-product-bundles', 'Live'),
            self::STATUS_PENDING => \Craft::t('commerce-product-bundles', 'Pending'),
            self::STATUS_EXPIRED => \Craft::t('commerce-product-bundles', 'Expired'),
            self::STATUS_DISABLED => \Craft::t('commerce-product-bundles', 'Disabled')
        ];
    }

    /**
     * @inheritdoc
     */
    public function getIsAvailable(): bool
    {
        return $this->getStatus() === self::STATUS_LIVE;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        $status = parent::getStatus();

        if ($status === self::STATUS_ENABLED && $this->postDate) {
            $currentTime = DateTimeHelper::currentTimeStamp();
            $postDate = $this->postDate->getTimestamp();
            $expiryDate = $this->expiryDate ? $this->expiryDate->getTimestamp() : null;

            if ($postDate <= $currentTime && (!$expiryDate || $expiryDate > $currentTime)) {
                return self::STATUS_LIVE;
            }

            if ($postDate > $currentTime) {
                return self::STATUS_PENDING;
            }

            return self::STATUS_EXPIRED;
        }

        return $status;
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules[] = [['sku', 'price', 'products'], 'required'];
        $rules[] = [['sku'], 'string'];
        $rules[] = [['postDate', 'expiryDate'], DateTimeValidator::class];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function datetimeAttributes(): array
    {
        $attributes = parent::datetimeAttributes();
        $attributes[] = 'postDate';
        $attributes[] = 'expiryDate';

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function getIsEditable(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getUriFormat()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function beforeSave(bool $isNew): bool
    {
        if ($this->enabled && !$this->postDate) {
            // Default the post date to the current date/time
            $this->postDate = DateTimeHelper::currentUTCDateTime();
        }

        return parent::beforeSave($isNew);
    }

    /**
     * @inheritDoc
     */
    public function afterSave(bool $isNew)
    {
        if (!$isNew) {
            $record = ProductBundleRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid bundle id: ' . $this->id);
            }
        } else {
            $record = new ProductBundleRecord();
            $record->id = $this->id;
        }

        $record->postDate = $this->postDate;
        $record->expiryDate = $this->expiryDate;
        $record->taxCategoryId = $this->taxCategoryId;
        $record->shippingCategoryId = $this->shippingCategoryId;
        $record->price = $this->price;
        $record->sku = $this->sku;

        $record->save(false);

        return parent::afterSave($isNew);
    }

    /**
     * @inheritDoc
     */
    public function getPurchasableId(): int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getPrice(): float
    {
        return (float) $this->price;
    }

    /**
     * @inheritDoc
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function getTaxCategoryId(): int
    {
        return $this->taxCategoryId;
    }

    /**
     * @inheritDoc
     */
    public function getShippingCategoryId(): int
    {
        return $this->shippingCategoryId;
    }

    /**
     * @inheritDoc
     */
    public function hasFreeShipping(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getIsPromotable(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigException
     */
    public function hasStock(): bool
    {
        foreach ($this->getProducts() as $product) {
            foreach ($product->getVariants() as $variant) {
                if ($variant->hasStock() || $variant->hasUnlimitedStock) {
                    // one of the variants of this product is in stock, continue to next product
                    continue 2;
                }
            }

            // all variants of one of the products is fully out of stock
            return false;
        }

        return true;
    }

    /**
     * @return Product[]
     */
    public function getProducts()
    {
        if (is_null($this->_products) && $this->getId()) {
            $this->_products = Plugin::getInstance()->productBundleService->getProductsForBundle($this);
        }

        return $this->_products;
    }

    /**
     * @param $products
     */
    public function setProducts($products)
    {
        $this->_products = [];

        if (is_array($products)) {
            foreach ($products as $product) {
                $this->_products[] = CommercePlugin::getInstance()->getProducts()->getProductById($product);
            }
        }
    }

    /**
     * Updates Stock count from completed order.
     *
     * @inheritdoc
     */
    public function afterOrderComplete(Order $order, LineItem $lineItem)
    {
        foreach ($lineItem->snapshot['options']['productBundleProductsVariantIds'] as $productVariantId) {
            $purchasable = CommercePlugin::getInstance()->getVariants()->getVariantById($productVariantId);

            if ($purchasable->hasUnlimitedStock) {
                continue;
            }

            // Update the qty in the db directly
            \Craft::$app->getDb()->createCommand()
                ->update(
                    '{{%commerce_variants}}',
                    ['stock' => new Expression('stock - :qty', [':qty' => ($lineItem->qty)])],
                    ['id' => $purchasable->id])
                ->execute();

            // Update the stock
            $purchasable->stock = (new Query())
                ->select(['stock'])
                ->from('{{%commerce_variants}}')
                ->where('id = :variantId', [':variantId' => $purchasable->id])
                ->scalar();

            \Craft::$app->getTemplateCaches()->deleteCachesByElementId($this->id);
        }
    }

    /**
     * @return array
     */
    public function getSnapshot(): array
    {
        $data = [];
        $data['type'] = self::class;

        // custom fields
        $fields = [];
        $fieldsEvent = new CustomizeProductSnapshotFieldsEvent([
            'product' => $this,
            'fields' => $fields
        ]);

        // Allow plugins to modify fields to be fetched
        if ($this->hasEventHandlers(self::EVENT_BEFORE_CAPTURE_PRODUCT_BUNDLE_SNAPSHOT)) {
            $this->trigger(self::EVENT_BEFORE_CAPTURE_PRODUCT_BUNDLE_SNAPSHOT, $fieldsEvent);
        }

        $fieldData = $this->getSerializedFieldValues($fieldsEvent->fields);
        $dataEvent = new CustomizeProductSnapshotDataEvent([
            'product' => $this,
            'fieldData' => $fieldData,
        ]);

        // Allow plugins to modify captured data
        if ($this->hasEventHandlers(self::EVENT_AFTER_CAPTURE_PRODUCT_BUNDLE_SNAPSHOT)) {
            $this->trigger(self::EVENT_AFTER_CAPTURE_PRODUCT_BUNDLE_SNAPSHOT, $dataEvent);
        }

        $data['fields'] = $dataEvent->fieldData;
        $data['productId'] = $this->id;

        return array_merge($this->getAttributes(), $data);
    }


    /**
     * @inheritdoc
     */
    public function getLineItemRules(LineItem $lineItem): array
    {
        // After the order is complete shouldn't check things like stock being available or the purchasable being around since they are irrelevant.
        if ($lineItem->getOrder() && $lineItem->getOrder()->isCompleted) {
            return [];
        }

        // an inline validator defined as an anonymous function
        return [
            [
                'purchasableId',
                function ($attribute, $params, Validator $validator) use ($lineItem) {
                    if ($lineItem->getPurchasable()->getStatus() != self::STATUS_LIVE) {
                        $validator->addError($lineItem, $attribute, \Craft::t('commerce', 'The item is not enabled for sale.'));
                    }
                }
            ],
            [
                'qty',
                function ($attribute, $params, Validator $validator) use ($lineItem) {
                    // no stock at all
                    if (!$this->hasStock()) {
                        $error = \Craft::t('commerce', '"{description}" is currently out of stock.', ['description' => $lineItem->purchasable->getDescription()]);
                        $validator->addError($lineItem, $attribute, $error);
                    }

                    $orderableQuantity = Plugin::getInstance()->productBundleService->getOrderableQuantity($lineItem);

                    // lineItem qty exceeds the quantity left
                    if ($this->hasStock() && $lineItem->qty > $orderableQuantity) {
                        $error = \Craft::t('commerce', 'There are only {num} "{description}" items left in stock.', [
                            'num' => $orderableQuantity,
                            'description' => $lineItem->purchasable->getDescription()
                        ]);
                        $validator->addError($lineItem, $attribute, $error);
                    }
                },
            ],
            [['qty'], 'integer', 'min' => 1, 'skipOnError' => false]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl('commerce/product-bundles/' . $this->id);
    }

    /**
     * @inheritdoc
     */
    public function getProduct()
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFieldLayout()
    {
        return \Craft::$app->getFields()->getLayoutByType(self::class);
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigException
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'taxCategory':
                $taxCategory = $this->getTaxCategory();

                return ($taxCategory ? \Craft::t('site', $taxCategory->name) : '');
            case 'shippingCategory':
                $shippingCategory = $this->getShippingCategory();

                return ($shippingCategory ? \Craft::t('site', $shippingCategory->name) : '');
            case 'defaultPrice':
                $code = CommercePlugin::getInstance()->getPaymentCurrencies()->getPrimaryPaymentCurrencyIso();

                return \Craft::$app->getLocale()->getFormatter()->asCurrency($this->$attribute, strtoupper($code));
            case 'promotable':
                return ($this->$attribute ? '<span data-icon="check" title="' . \Craft::t('commerce-product-bundles', 'Yes') . '"></span>' : '');
            default:
                return parent::tableAttributeHtml($attribute);
        }
    }

    /**
     * @return TaxCategory|null
     */
    public function getTaxCategory()
    {
        if ($this->taxCategoryId) {
            return CommercePlugin::getInstance()->getTaxCategories()->getTaxCategoryById($this->taxCategoryId);
        }

        return null;
    }

    /**
     * @return ShippingCategory|null
     */
    public function getShippingCategory()
    {
        if ($this->shippingCategoryId) {
            return CommercePlugin::getInstance()->getShippingCategories()->getShippingCategoryById($this->shippingCategoryId);
        }

        return null;
    }
}
