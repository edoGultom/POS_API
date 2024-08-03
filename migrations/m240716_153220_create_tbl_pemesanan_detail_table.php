<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tbl_pemesanan_detail}}`.
 */
class m240716_153220_create_tbl_pemesanan_detail_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tbl_pemesanan_detail}}', [
            'id' => $this->primaryKey(),
            'id_pemesanan' => $this->integer()->notNull(),
            'id_menu' => $this->integer()->notNull(),
            'quantity' => $this->integer(),
            'temperatur' => "ENUM('HOT', 'COLD')", //('ordered', 'in_progress','ready','served','paid')
            'status' => "ENUM('ordered', 'in_progress','ready','served','paid')", //('ordered', 'in_progress','ready','served','paid')
            'id_chef' => $this->integer(), // (Foreign Key ke tabel Users)
        ]);

        // add foreign key 
        $this->addForeignKey(
            'fk-pemesanan-detail-id_pemesanan',
            'tbl_pemesanan_detail',
            'id_pemesanan',
            'tbl_pemesanan',
            'id',
            'CASCADE'
        );
        // add foreign key 
        $this->addForeignKey(
            'fk-pemesanan-detail-id_menu',
            'tbl_pemesanan_detail',
            'id_menu',
            'tbl_menu',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tbl_pemesanan_detail}}');
    }
}
