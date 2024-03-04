<?php

namespace oym\craft\commerce\bundles\migrations;

use Craft;
use craft\db\Migration;

class m201029_160701_product_field_qty extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%' . Install::TABLE_BUNDLES_PRODUCTS . '}}', 'qty', $this->integer()->notNull()->unsigned()->defaultValue(1));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m201029_160701_product_field_qty cannot be reverted.\n";
        return false;
    }
}
