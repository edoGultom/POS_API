<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%kategori_barang}}`.
 */
class m240520_162002_create_kategori_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tbl_kategori}}', [
            'id' => $this->primaryKey(),
            'nama_kategori' => $this->string(255),
        ]);
        $this->batchInsert(
            'tbl_kategori',
            [
                'nama_kategori',
            ],
            [
                ['Makanan'],
                ['Minuman'],

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
