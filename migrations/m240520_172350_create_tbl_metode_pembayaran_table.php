<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tbl_metode_pembayaran}}`.
 */
class m240520_172350_create_tbl_metode_pembayaran_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tbl_metode_pembayaran}}', [
            'id' => $this->primaryKey(),
            'metode' => $this->string(100),
        ]);
        $this->batchInsert(
            'tbl_metode_pembayaran',
            [
                'metode',
            ],
            [
                ['CASH'],
                ['QRIS'],

            ],
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tbl_metode_pembayaran}}');
    }
}
