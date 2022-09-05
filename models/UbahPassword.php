<?php

namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;

/**
 * UbahPassword form
 */
class UbahPassword extends Model
{
    public $password;
    public $repassword;
    public $password_old;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['password', 'repassword'], 'required'],
            ['password', 'string', 'min' => 6],
            ['repassword', 'compare', 'compareAttribute' => 'password', 'message' => "Password dan Re-Password tidak sama"]
        ];
    }

    /**
     * Signs user up.
     *
     * @return bool whether the creating new account was successful and email was sent
     */
    public function gantiPassword($id)
    {
        //        print_r($this->password);
        if (!$this->validate()) {
            return false;
        }
        $user = User::findOne(['id' => $id]);
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->save(false);

        return true;
    }

    public function cariPassword($passwordOld)
    {
        $user =  User::findOne(['id' => Yii::$app->user->identity->id]);
        if (!Yii::$app->security->validatePassword($passwordOld, $user->password_hash)) {
            return false;
        } else return true;
    }
}
