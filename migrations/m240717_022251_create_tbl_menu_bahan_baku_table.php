<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tbl_menu_bahan_baku}}`.
 */
class m240717_022251_create_tbl_menu_bahan_baku_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tbl_menu_bahan_baku}}', [
            'id' => $this->primaryKey(),
            'id_menu' => $this->integer(),
            'id_bahan_baku' => $this->integer(),
            'quantity' => $this->integer(), //(Jumlah bahan baku yang digunakan per satuan menu item)
        ]);
        // add foreign key 
        $this->addForeignKey(
            'fk-menu-bahan-baku-id_menu',
            'tbl_menu_bahan_baku',
            'id_menu',
            'tbl_menu',
            'id',
            'CASCADE'
        );
        // add foreign key 
        $this->addForeignKey(
            'fk-menu-bahan-baku-id_bahan_bakus',
            'tbl_menu_bahan_baku',
            'id_bahan_baku',
            'tbl_bahan_baku',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tbl_menu_bahan_baku}}');
    }
}
