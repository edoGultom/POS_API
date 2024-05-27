<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tbl_pembayaran}}`.
 */
class m240520_170946_create_tbl_pembayaran_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tbl_pembayaran}}', [
            'id' => $this->primaryKey(),
            'id_penjualan' => $this->integer(),
            'payment_method' => $this->string(100), //QRIS / CASH
            'payment_gateway' => $this->string(100), //Menyimpan gateway pembayaran jika ada (null untuk cash dan qris)
            'jumlah' => $this->integer(),
            'tanggal_pembayaran' => $this->dateTime(),
            'payment_status' => $this->string(100), //Completed, Failed
            'id_transaksi' => $this->integer(), //ID Transaksi dari Payment Gateway
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tbl_pembayaran}}');
    }
}
