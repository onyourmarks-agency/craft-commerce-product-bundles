<?php

namespace oym\craft\commerce\bundles\migrations;

use Craft;
use craft\db\Migration;

class m201029_165753_remove_shipping_tax_categories extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropForeignKey(
            $this->db->getForeignKeyName('{{%' . Install::TABLE_BUNDLES . '}}', 'shippingCategoryId'),
            '{{%' . Install::TABLE_BUNDLES . '}}'
        );

        $this->dropForeignKey(
            $this->db->getForeignKeyName('{{%' . Install::TABLE_BUNDLES . '}}', 'taxCategoryId'),
            '{{%' . Install::TABLE_BUNDLES . '}}'
        );

        $this->dropColumn('{{%' . Install::TABLE_BUNDLES . '}}', 'taxCategoryId');
        $this->dropColumn('{{%' . Install::TABLE_BUNDLES . '}}', 'shippingCategoryId');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m201029_165753_remove_shipping_tax_categories cannot be reverted.\n";
        return false;
    }
}
