<?php

use yii\db\Migration;

/**
 * Class m200923_061804_user_admin
 */
class m200923_061804_user_admin extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('user', [
            'username' => 'admin',
            'auth_key' => 'ieKwCbx7j0tUA1XvFMDlVXUODds6OY7O',
            'password_hash' => '$2y$13$Xcijg79rt2F.hwlR3nEZru9rBT1PWYpGP.Gpvi3xeWXHdB2v37h.G',
            'password_reset_token' => '123123123',
            'email' => 'dfdsa@dfa.com',
            'status' => '10',
            'created_at' => time(),
            'updated_at' => time()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200923_061804_user_admin cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200923_061804_user_admin cannot be reverted.\n";

        return false;
    }
    */
}
