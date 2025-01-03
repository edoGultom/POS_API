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
            'kode' => $this->string(),
            'tipe' => "ENUM('Masuk', 'Keluar')",
            'tanggal' => $this->date(),
        ]);
        $this->createTable('{{%tbl_transaksi_stok_bahan_baku}}', [
            'id' => $this->primaryKey(),
            'id_transaksi_stok' => $this->integer(),
            'id_bahan_baku' => $this->integer(), //(Foreign Key ke tabel Ingredients)
            'quantity' => $this->integer(),
        ]);
        // add foreign key 
        $this->addForeignKey(
            'fk-transaksi-stok-id_bahan_baku',
            'tbl_transaksi_stok_bahan_baku',
            'id_bahan_baku',
            'tbl_bahan_baku',
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-transaksi-stok-id_transaksi_stok',
            'tbl_transaksi_stok_bahan_baku',
            'id_transaksi_stok',
            'tbl_transaksi_stok',
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
