<?php

use yii\db\Migration;

/**
 * Class m240604_031904_add_column_address_user
 */
class m240604_031904_add_column_address_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'address', $this->string()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240604_031904_add_column_address_user cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240604_031904_add_column_address_user cannot be reverted.\n";

        return false;
    }
    */
}
