<?php

namespace oym\craft\commerce\bundles\migrations;

use craft\db\Migration;
use craft\helpers\MigrationHelper;

class Install extends Migration
{
    const TABLE_PREFIX = 'commerce_product_bundles_';
    const TABLE_BUNDLES = self::TABLE_PREFIX . 'bundles';
    const TABLE_BUNDLES_PRODUCTS = self::TABLE_BUNDLES . '_products';

    public function safeUp()
    {
        // start fresh
        $this->safeDown();

        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKeys();
        $this->dropTables();

        return true;
    }

    /**
     * @return void
     */
    protected function createTables()
    {
        $this->createTable('{{%' . self::TABLE_BUNDLES . '}}', [
            'id' => $this->primaryKey(),
            'taxCategoryId' => $this->integer()->notNull(),
            'shippingCategoryId' => $this->integer()->notNull(),
            'postDate' => $this->dateTime(),
            'expiryDate' => $this->dateTime()->null(),
            'sku' => $this->string()->notNull(),
            'price' => $this->decimal(12, 2)->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%' . self::TABLE_BUNDLES_PRODUCTS . '}}', [
            'id' => $this->primaryKey(),
            'productBundleId' => $this->integer(),
            'productId' => $this->integer(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
    }

    /**
     * @return void
     */
    protected function createIndexes()
    {
        $this->createIndex(
            $this->db->getIndexName('{{%' . self::TABLE_BUNDLES . '}}', 'sku', true),
            '{{%' . self::TABLE_BUNDLES . '}}',
            'sku',
            true
        );
        $this->createIndex(
            $this->db->getIndexName('{{%' . self::TABLE_BUNDLES . '}}', 'taxCategoryId', false),
            '{{%' . self::TABLE_BUNDLES . '}}',
            'taxCategoryId',
            false
        );
        $this->createIndex(
            $this->db->getIndexName('{{%' . self::TABLE_BUNDLES . '}}', 'shippingCategoryId', false),
            '{{%' . self::TABLE_BUNDLES . '}}',
            'shippingCategoryId',
            false
        );

        $this->createIndex(
            $this->db->getIndexName('{{%' . self::TABLE_BUNDLES_PRODUCTS . '}}', 'productBundleId', true),
            '{{%' . self::TABLE_BUNDLES_PRODUCTS . '}}',
            'productBundleId',
            false
        );
        $this->createIndex(
            $this->db->getIndexName('{{%' . self::TABLE_BUNDLES_PRODUCTS . '}}', 'productId', true),
            '{{%' . self::TABLE_BUNDLES_PRODUCTS . '}}',
            'productId',
            false
        );
    }

    /**
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%' . self::TABLE_BUNDLES . '}}', 'id'),
            '{{%' . self::TABLE_BUNDLES . '}}',
            'id',
            '{{%elements}}',
            'id',
            'CASCADE',
            null
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%' . self::TABLE_BUNDLES . '}}', 'shippingCategoryId'),
            '{{%' . self::TABLE_BUNDLES . '}}',
            'shippingCategoryId',
            '{{%commerce_shippingcategories}}',
            'id',
            null,
            null
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%' . self::TABLE_BUNDLES . '}}', 'taxCategoryId'),
            '{{%' . self::TABLE_BUNDLES . '}}',
            'taxCategoryId',
            '{{%commerce_taxcategories}}',
            'id',
            null,
            null
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%' . self::TABLE_BUNDLES_PRODUCTS . '}}', 'productBundleId'),
            '{{%' . self::TABLE_BUNDLES_PRODUCTS . '}}',
            'productBundleId',
            '{{%' . self::TABLE_BUNDLES . '}}',
            'id',
            'CASCADE',
            null
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%' . self::TABLE_BUNDLES_PRODUCTS . '}}', 'productId'),
            '{{%' . self::TABLE_BUNDLES_PRODUCTS . '}}',
            'productId',
            '{{%commerce_products}}',
            'id',
            'CASCADE',
            null
        );
    }

    /**
     * @return void
     */
    protected function dropForeignKeys()
    {
        if (\Craft::$app->db->schema->getTableSchema('{{%' . self::TABLE_BUNDLES . '}}')) {
            MigrationHelper::dropAllForeignKeysOnTable('{{%' . self::TABLE_BUNDLES . '}}', $this);
        }

        if (\Craft::$app->db->schema->getTableSchema('{{%' . self::TABLE_BUNDLES_PRODUCTS . '}}')) {
            MigrationHelper::dropAllForeignKeysOnTable('{{%' . self::TABLE_BUNDLES_PRODUCTS . '}}', $this);
        }
    }

    /**
     * @return void
     */
    protected function dropTables()
    {
        $this->dropTableIfExists('{{%' . self::TABLE_BUNDLES . '}}');
        $this->dropTableIfExists('{{%' . self::TABLE_BUNDLES_PRODUCTS . '}}');
    }
}
