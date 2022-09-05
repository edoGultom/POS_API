<?php

use yii\db\Migration;

/**
 * Class m210612_030851_oaut_client_refreshtoken
 */
class m210612_030851_oaut_client_refreshtoken extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update('oauth_clients', ['grant_types' => 'client_credentials authorization_code password refresh_token implicit'], ['client_id' => 'testclient']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210612_030851_oaut_client_refreshtoken cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210612_030851_oaut_client_refreshtoken cannot be reverted.\n";

        return false;
    }
    */
}
