<?php

use yii\db\Migration;

/**
 * Class m240603_164238_add_column_photo_user
 */
class m240603_164238_add_column_photo_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'profile_photo_path', $this->string()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240603_164238_add_column_photo_user cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240603_164238_add_column_photo_user cannot be reverted.\n";

        return false;
    }
    */
}
