<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tbl_stok_barang}}`.
 */
class m240520_173258_create_tbl_stok_barang_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tbl_stok_barang}}', [
            'id' => $this->primaryKey(),
            'id_barang' => $this->integer(),
            'perubahan_stok' => $this->integer(), // Jumlah perubahan stok (positif untuk penambahan, negatif untuk pengurangan) (e.g. +10 atau -10)
            'tipe' => $this->string(100), //tipe pergerakan (e.g., 'addition', 'sale', 'return', dll)
            'tanggal' => $this->date(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tbl_stok_barang}}');
    }
}
