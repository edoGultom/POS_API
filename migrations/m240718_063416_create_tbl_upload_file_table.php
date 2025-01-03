<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tbl_upload_file}}`.
 */
class m240718_063416_create_tbl_upload_file_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('uploaded_file', [
            'id'                => $this->primaryKey(),
            'name'              => $this->string(),
            'filename'          => $this->string(),
            'captions'          => $this->text(),
            'size'              => $this->integer(),
            'type'              => $this->string(),
            'status'              => $this->tinyInteger(),
            'created_at'              => $this->integer(),
            'updated_at'              => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tbl_upload_file}}');
    }
}
