<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tbl_unit_bahan_baku}}`.
 */
class m240716_154620_create_tbl_unit_bahan_baku_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tbl_unit_bahan_baku}}', [
            'id' => $this->primaryKey(),
            'nama' => $this->text(),
        ]);

        $this->batchInsert(
            'tbl_unit_bahan_baku',
            [
                'nama',
            ],
            [
                ['ml'],
                ['gram'],
                ['buah'],
                ['cup'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tbl_unit_bahan_baku}}');
    }
}
