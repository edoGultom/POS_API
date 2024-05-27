<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tbl_pelanggan}}`.
 */
class m240520_170305_create_tbl_pelanggan_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tbl_pelanggan}}', [
            'id' => $this->primaryKey(),
            'nama_pelanggan' => $this->string(255),
            'email' => $this->string(255),
            'phone' => $this->string(20),
            'alamat' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tbl_pelanggan}}');
    }
}
