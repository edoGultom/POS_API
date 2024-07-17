<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%kategori_barang}}`.
 */
class m240520_162202_create_kategori_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tbl_kategori}}', [
            'id' => $this->primaryKey(),
            'nama_kategori' => $this->string(255),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
        $this->batchInsert(
            'tbl_kategori',
            [
                'nama_kategori',
                'created_at',
                'updated_at'
            ],
            [
                ['Makanan', time(), time()],
                ['Minuman', time(), time()],

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
