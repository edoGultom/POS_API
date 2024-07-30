<?php

use yii\db\Migration;

/**
 * Class m240603_183026_add_column_name_user
 */
class m240603_183026_add_column_name_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'name', $this->text()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240603_183026_add_column_name_user cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240603_183026_add_column_name_user cannot be reverted.\n";

        return false;
    }
    */
}
