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
            'nama' => $this->string(64),
            'id_kategori' => $this->integer()->notNull(),
            'harga' => $this->integer(),
            'path' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
        // add foreign key 
        $this->addForeignKey(
            'fk-menu-id_kategori',
            'tbl_menu',
            'id_kategori',
            'tbl_kategori',
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
