<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tbl_bahan_baku}}`.
 */
class m240716_154623_create_tbl_bahan_baku_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tbl_bahan_baku}}', [
            'id' => $this->primaryKey(),
            'nama' => $this->text(),
            'quantity' => $this->integer(),
            'id_unit_bahan_baku' => $this->integer()->notNull(),
        ]);
        // add foreign key 
        $this->addForeignKey(
            'fk-bahan-baku-id_unit_bahan_baku',
            'tbl_bahan_baku',
            'id_unit_bahan_baku',
            'tbl_unit_bahan_baku',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tbl_bahan_baku}}');
    }
}
