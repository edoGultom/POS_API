<?php

namespace app\models;

use yii\base\InvalidArgumentException;
use yii\base\Model;
use app\models\User;

/**
 * Password reset form
 */
class ResetPasswordForm extends Model
{
    public $password;
    public $re_password;
    private $_user;

    /**
     * {@inheritdoc}
     */
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            return 'Token Kosong';
        }
        $this->_user = User::findByPasswordResetToken($token);
        if (!$this->_user) {
            return 'Token Tidak Cocok';
        }
        parent::__construct($config);
    }

    public function rules()
    {
        return [
            [['password', 're_password'], 'required'],
            [['password', 're_password'], 'string', 'min' => 6],
            ['re_password', 'compare', 'compareAttribute' => 'password'],
        ];
    }

    public function resetPassword()
    {
        if (!$this->_user) {
            return false;
        }
        $user = $this->_user;
        $user->setPassword($this->password);
        $user->removePasswordResetToken();

        return $user->save(false);
    }
}
