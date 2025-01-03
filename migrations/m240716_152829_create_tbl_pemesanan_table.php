<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tbl_pemesanan}}`.
 */
class m240716_152829_create_tbl_pemesanan_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tbl_pemesanan}}', [
            'id' => $this->primaryKey(),
            'id_meja' => $this->integer()->notNull(), // (Foreign Key ke tabel Tables)
            'id_pelayan' => $this->integer()->notNull(), //(Foreign Key ke tabel Users)
            'status' => "ENUM('ordered', 'in_progress','ready','served','paid','pending payment','expired payment','canceled payment')", //(available, occupied)
            'waktu' => $this->datetime(),
        ]);
        // add foreign key 
        $this->addForeignKey(
            'fk-pemesanan-id_meja',
            'tbl_pemesanan',
            'id_meja',
            'tbl_meja',
            'id',
            'CASCADE'
        );
        // add foreign key 
        $this->addForeignKey(
            'fk-pemesanan-id_pelayan',
            'tbl_pemesanan',
            'id_pelayan',
            'user',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tbl_pemesanan}}');
    }
}
