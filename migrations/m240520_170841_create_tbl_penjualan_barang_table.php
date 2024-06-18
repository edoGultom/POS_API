<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tbl_penjualan_barang}}`.
 */
class m240520_170841_create_tbl_penjualan_barang_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tbl_penjualan_barang}}', [
            'id' => $this->primaryKey(),
            'id_penjualan' => $this->integer(),
            'id_barang' => $this->integer(),
            'temperatur' => $this->string(50),
            'qty' => $this->integer(),
            'harga' => $this->integer(),
            'total' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tbl_penjualan_barang}}');
    }
}
