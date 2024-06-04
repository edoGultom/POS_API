<?php

use yii\db\Migration;

/**
 * Class m240604_033501_add_auth_item_user
 */
class m240604_033501_add_auth_item_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert(
            'auth_item',
            [
                'name',
                'type',
                'description',
                'rule_name',
                'data',
                'created_at',
                'updated_at'
            ],
            [

                [
                    'User', 1, NULL, NULL, NULL, time(), time()
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240604_033501_add_auth_item_user cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240604_033501_add_auth_item_user cannot be reverted.\n";

        return false;
    }
    */
}
