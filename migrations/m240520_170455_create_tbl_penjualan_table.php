<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tbl_transaksi}}`.
 */
class m240520_170455_create_tbl_penjualan_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tbl_penjualan}}', [
            'id' => $this->primaryKey(),
            'id_user' => $this->integer(),
            'id_pelanggan' => $this->integer(),
            'total_transaksi' => $this->integer(),
            'status_pembayaran' => $this->string(100), //Pending, Paid, Failed
            'payment_gateway' => $this->string(100), //Menyimpan gateway pembayaran jika ada (null untuk cash dan qris)
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tbl_transaksi}}');
    }
}
