<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%kategori_barang}}`.
 */
class m240520_162202_create_kategori_barang_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tbl_kategori_barang}}', [
            'id' => $this->primaryKey(),
            'nama_kategori' => $this->string(255),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
        $this->batchInsert(
            'tbl_kategori_barang',
            [
                'nama_kategori',
                'created_at',
                'updated_at'
            ],
            [
                ['Coffee', time(), time()],
                ['Non Coffee', time(), time()],

            ],
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%kategori_barang}}');
    }
}
