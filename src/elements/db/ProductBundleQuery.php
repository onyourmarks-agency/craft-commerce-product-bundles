<?php

namespace tde\craft\commerce\bundles\elements\db;

use craft\db\Query;
use craft\db\QueryAbortedException;
use craft\elements\db\ElementQuery;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use tde\craft\commerce\bundles\elements\ProductBundle;
use yii\base\UnknownPropertyException;

/**
 * Class ProductBundleQuery
 *
 * @package tde\craft\commerce\bundles\elements\db
 */
class ProductBundleQuery extends ElementQuery
{
    public $editable = false;
    public $postDate;
    public $expiryDate;

    /**
     * ProductBundleQuery constructor.
     *
     * @param string $elementType
     * @param array $config
     */
    public function __construct(string $elementType, array $config = [])
    {
        // Default status
        if (!isset($config['status'])) {
            $config['status'] = ProductBundle::STATUS_LIVE;
        }

        parent::__construct($elementType, $config);
    }

    /**
     * @param $name
     * @param $value
     *
     * @throws UnknownPropertyException
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'before':
                $this->before($value);
                break;
            case 'after':
                $this->after($value);
                break;
            default:
                parent::__set($name, $value);
        }
    }

    /**
     * @param $value
     * @return $this
     */
    public function before($value)
    {
        if ($value instanceof \DateTime) {
            $value = $value->format(\DateTime::W3C);
        }

        $this->postDate = ArrayHelper::toArray($this->postDate);
        $this->postDate[] = '<'.$value;

        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function after($value)
    {
        if ($value instanceof \DateTime) {
            $value = $value->format(\DateTime::W3C);
        }

        $this->postDate = ArrayHelper::toArray($this->postDate);
        $this->postDate[] = '>='.$value;

        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function editable(bool $value = true)
    {
        $this->editable = $value;

        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function postDate($value)
    {
        $this->postDate = $value;

        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function expiryDate($value)
    {
        $this->expiryDate = $value;

        return $this;
    }

    /**
     * @return bool
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('commerce_product_bundles_bundles');

        $this->query->select([
            'commerce_product_bundles_bundles.id',
            'commerce_product_bundles_bundles.postDate',
            'commerce_product_bundles_bundles.expiryDate',
            'commerce_product_bundles_bundles.sku',
            'commerce_product_bundles_bundles.price',
        ]);

        if ($this->postDate) {
            $this->subQuery->andWhere(Db::parseDateParam('commerce_product_bundles_bundles.postDate', $this->postDate));
        }

        if ($this->expiryDate) {
            $this->subQuery->andWhere(Db::parseDateParam('commerce_product_bundles_bundles.expiryDate', $this->expiryDate));
        }

        return parent::beforePrepare();
    }

    /**
     * @param string $status
     * @return array|false|string|\yii\db\ExpressionInterface|null
     * @throws \Exception
     */
    protected function statusCondition(string $status)
    {
        $currentTimeDb = Db::prepareDateForDb(new \DateTime());

        switch ($status) {
            case ProductBundle::STATUS_LIVE:
                return [
                    'and',
                    [
                        'elements.enabled' => true,
                        'elements_sites.enabled' => true
                    ],
                    ['<=', 'commerce_product_bundles_bundles.postDate', $currentTimeDb],
                    [
                        'or',
                        ['commerce_product_bundles_bundles.expiryDate' => null],
                        ['>', 'commerce_product_bundles_bundles.expiryDate', $currentTimeDb]
                    ]
                ];
            case ProductBundle::STATUS_PENDING:
                return [
                    'and',
                    [
                        'elements.enabled' => true,
                        'elements_sites.enabled' => true,
                    ],
                    ['>', 'commerce_product_bundles_bundles.postDate', $currentTimeDb]
                ];
            case ProductBundle::STATUS_EXPIRED:
                return [
                    'and',
                    [
                        'elements.enabled' => true,
                        'elements_sites.enabled' => true
                    ],
                    ['not', ['commerce_product_bundles_bundles.expiryDate' => null]],
                    ['<=', 'commerce_product_bundles_bundles.expiryDate', $currentTimeDb]
                ];
            default:
                return parent::statusCondition($status);
        }
    }
}
