<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tbl_menu}}`.
 */
class m240520_162121_create_tbl_sub_kategori_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tbl_sub_kategori}}', [
            'id' => $this->primaryKey(),
            'id_kategori' => $this->integer()->notNull(), //Foreign Key ke tabel Orders)
            'nama_sub_kategori' => $this->string()
        ]);
        // add foreign key 
        $this->addForeignKey(
            'fk-tbl-sub-kategori-id_kategori',
            'tbl_sub_kategori',
            'id_kategori',
            'tbl_kategori',
            'id',
            'CASCADE'
        );
        $this->batchInsert(
            'tbl_sub_kategori',
            [
                'id_kategori',
                'nama_sub_kategori'
            ],
            [
                [1, 'Coffee'],
                [1, 'Non Coffee'],
                [2, 'Snack'],
            ],
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tbl_menu}}');
    }
}
