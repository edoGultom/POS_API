<?php

use yii\db\Migration;

/**
 * Class m240606_080409_add_upload_file
 */
class m240606_080409_add_upload_file extends Migration
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
        echo "m240606_080409_add_upload_file cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240606_080409_add_upload_file cannot be reverted.\n";

        return false;
    }
    */
}
