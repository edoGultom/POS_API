<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tbl_pembayaran}}`.
 */
class m240716_153354_create_tbl_pembayaran_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tbl_pembayaran}}', [
            'id' => $this->primaryKey(),
            'id_pemesanan' => $this->integer()->notNull(), //Foreign Key ke tabel Orders)
            'jumlah' => $this->integer(),
            'jumlah_diberikan' => $this->integer(), //Jumlah diberikan customer
            'jumlah_kembalian' => $this->integer(), //Jumlah dikembalikan customer
            'tipe_pembayaran' => $this->string(), //(cash, qris)
            'waktu_pembayaran' => $this->dateTime(),
            'id_kasir' => $this->integer()->notNull(),
            'id_transaksi_qris' => $this->text(), //ID Transaksi dari Payment Gateway
            'link_qris' => $this->text(), //link QRIS dari Payment Gateway
        ]);
        // add foreign key 
        $this->addForeignKey(
            'fk-pembayaran-id_pemesanan',
            'tbl_pembayaran',
            'id_pemesanan',
            'tbl_pemesanan',
            'id',
            'CASCADE'
        );
        // add foreign key 
        $this->addForeignKey(
            'fk-pembayaran-id_kasir',
            'tbl_pembayaran',
            'id_kasir',
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
        $this->dropTable('{{%tbl_pembayaran}}');
    }
}
