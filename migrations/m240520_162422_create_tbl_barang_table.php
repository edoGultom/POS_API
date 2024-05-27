<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tbl_barang}}`.
 */
class m240520_162422_create_tbl_barang_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tbl_barang}}', [
            'id' => $this->primaryKey(),
            'nama_barang' => $this->string(64),
            'id_satuan' => $this->integer(),
            'id_kategori' => $this->integer(),
            'harga' => $this->integer(),
            'stok' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tbl_barang}}');
    }
}
