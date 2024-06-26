<?php

use yii\db\Migration;

/**
 * Class m240626_144249_add_column_qris_pembayaran
 */
class m240626_144249_add_column_qris_pembayaran extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%tbl_pembayaran}}', 'link_qris', $this->text()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240626_144249_add_column_qris_pembayaran cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240626_144249_add_column_qris_pembayaran cannot be reverted.\n";

        return false;
    }
    */
}
