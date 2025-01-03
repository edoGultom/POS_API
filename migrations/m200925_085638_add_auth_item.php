<?php

use yii\db\Migration;

/**
 * Class m200925_085638_add_auth_item
 */
class m200925_085638_add_auth_item extends Migration
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
                    '/*', 2, NULL, NULL, NULL, time(), time()
                ],
                [
                    'Admin', 1, NULL, NULL, NULL, time(), time()
                ],
                [
                    'Waiters', 1, NULL, NULL, NULL, time(), time()
                ],
                [
                    'Cashier', 1, NULL, NULL, NULL, time(), time()
                ],
                [
                    'Chef', 1, NULL, NULL, NULL, time(), time()
                ],
            ]
        );

        $this->batchInsert(
            'auth_item_child',
            [
                'parent',
                'child',
            ],
            [
                [
                    'Admin', '/*'
                ],
            ]
        );

        $this->batchInsert(
            'auth_assignment',
            [
                'item_name',
                'user_id',
                'created_at',
            ],
            [
                [
                    'Admin', '1', time()
                ],
            ]

        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200925_085638_add_auth_item cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200925_085638_add_auth_item cannot be reverted.\n";

        return false;
    }
    */
}
