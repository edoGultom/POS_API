<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tbl_stok_transaksi_bahan_baku}}`.
 */
class m240717_061608_create_tbl_stok_transaksi_bahan_baku_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tbl_transaksi_stok}}', [
            'id' => $this->primaryKey(),
            'id_bahan_baku' => $this->integer(), //(Foreign Key ke tabel Ingredients)
            'transaction_type' => "ENUM('Masuk', 'Keluar')",
            'quantity' => $this->integer(),
            'transaction_time' => $this->dateTime(),
        ]);
        // add foreign key 
        $this->addForeignKey(
            'fk-transaksi-stok-id_bahan_baku',
            'tbl_transaksi_stok',
            'id_bahan_baku',
            'tbl_bahan_baku',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tbl_transaksi_stok}}');
    }
}
