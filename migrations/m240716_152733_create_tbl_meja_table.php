<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tbl_meja}}`.
 */
class m240716_152733_create_tbl_meja_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tbl_meja}}', [
            'id' => $this->primaryKey(),
            'nomor_meja' => $this->string(),
            'status' => "ENUM('Available', 'Occupied')", //(available, occupied)

        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tbl_meja}}');
    }
}
