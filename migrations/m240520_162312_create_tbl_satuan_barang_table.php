<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tbl_satuan_barang}}`.
 */
class m240520_162312_create_tbl_satuan_barang_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tbl_satuan_barang}}', [
            'id' => $this->primaryKey(),
            'nama_satuan' => $this->string(25),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
        $this->batchInsert(
            'tbl_satuan_barang',
            [
                'nama_satuan',
                'created_at',
                'updated_at'
            ],
            [
                ['Cup', time(), time()],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tbl_satuan_barang}}');
    }
}
