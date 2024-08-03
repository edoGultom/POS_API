<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tbl_menu}}`.
 */
class m240520_162422_create_tbl_menu_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tbl_menu}}', [
            'id' => $this->primaryKey(),
            'nama' => $this->string(),
            'id_kategori' => $this->integer()->notNull(),
            'id_sub_kategori' => $this->integer()->notNull(),
            'harga' => $this->integer(),
            'harga_ekstra' => $this->integer(),
            'path' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
        // add foreign key 
        $this->addForeignKey(
            'fk-menus-id_kategori',
            'tbl_menu',
            'id_kategori',
            'tbl_kategori',
            'id',
            'CASCADE'
        );
        // add foreign key 
        $this->addForeignKey(
            'fk-menus-id_sub_kategori',
            'tbl_menu',
            'id_sub_kategori',
            'tbl_sub_kategori',
            'id',
            'CASCADE'
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
