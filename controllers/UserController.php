<?php

namespace app\controllers;

use Yii;
use \yii\web\Response;
use app\models\User;
use yii\helpers\ArrayHelper;
use app\models\ResetPasswordForm;
use app\models\AuthAssignment;
use app\models\OauthAccessTokens;
use app\modelssearch\PasswordResetRequestForm;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;


class UserController extends \yii\rest\Controller
{
    public $pesan = '';
    public $data = '';
    public $status = false;

    public function beforeAction($action)
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'exceptionFilter' => [
                'class' => ErrorToExceptionFilter::className()
            ],
        ]);
    }

    public function actionLoginUser()
    {
        $response = Yii::$app->getModule('oauth2')->getServer()->handleTokenRequest();
        $result = $response->getParameters();
        $data = [];
        if (isset($result['access_token'])) {
            $model = OauthAccessTokens::find()->where(['access_token' => $result['access_token']])->one();
            $user = User::find()->where(['id' => $model->user_id])->one();


            $data['user_id'] = $model->user_id;
            $data['email'] = $user->email;
            $data['username'] = $user->username;

            $data['foto'] = 0;
            $hakAkses = AuthAssignment::find()->select(['item_name'])->where(['user_id' => $model->user_id])->asArray()->all();
            $data['expires'] = strtotime($model->expires);
            $data['scope'] = ArrayHelper::getColumn($hakAkses, function ($m) {
                return str_replace(" ", "_", $m['item_name']);
            });

            $data['access_token'] = $result['access_token'];
            $data['refresh_token'] = $result['refresh_token'];
            $model->scope = implode(" ", $data['scope']);
            $model->save();
            return $data;
        }
        return false;
    }

    public function actionRefreshToken()
    {
        $response = Yii::$app->getModule('oauth2')->getServer()->handleTokenRequest();
        $result = $response->getParameters();
        $data = [];

        if (isset($result['access_token'])) {

            $model = OauthAccessTokens::find()->where(['access_token' => $result['access_token']])->one();
            $user = User::find()->where(['id' => $model->user_id])->one();
            $data['user_id'] = $model->user_id;
            $data['pin'] = $user->pin;

            $hakAkses = AuthAssignment::find()->select(['item_name'])->where(['user_id' => $model->user_id])->asArray()->all();
            $data['expires'] = strtotime($model->expires);
            $data['scope'] = ArrayHelper::getColumn($hakAkses, function ($m) {
                return str_replace(" ", "_", $m['item_name']);
            });

            $data['access_token'] = $result['access_token'];
            $data['refresh_token'] = $result['refresh_token'];
            $model->scope = implode(" ", $data['scope']);
            $model->save();
            return $data;
        }
        return false;
    }

    public function actionRegister()
    {
        $post = Yii::$app->request->post();
        $connection = Yii::$app->db;
        $model = User::find()->where(['username' => $post['username']])->orWhere(['email' => $post['email']])->one();
        if ($model) {
            return [
                'status' =>  $this->status,
                'data' => $this->data,
                'pesan' => 'Username atau email sudah digunakan, silahkan gunakan usarname atau email yang lain'
            ];
        }
        $transaction = $connection->beginTransaction();
        $user = new User();

        try {
            $user->auth_key = Yii::$app->security->generateRandomString();
            $user->username = $post['username'];

            $user->email = $post['email'];
            $user->setPassword($post['password']);
            $user->generateAuthKey();
            $user->generateEmailVerificationToken();
            $user->status = 9;
            $user->created_at = time();
            $user->updated_at = time();
            $user->save(false);
            // $user->sendEmail($user);

            $connection->createCommand()->batchInsert('auth_assignment', [
                'user_id',
                'item_name',
                'created_at'
            ], [
                [$user->id, 'admin', time()],
            ])->execute();

            $transaction->commit();
            $this->status = true;
            $this->pesan = 'Register Berhasil';
        } catch (\Exception $e) {
            $transaction->rollBack();
            return $e->getMessage();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return $e->getMessage();
        }

        return [
            'status' =>  $this->status,
            'data' => $this->data,
            'pesan' => $this->pesan
        ];
    }

    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        $post = Yii::$app->request->post();
        $model->email = $post['email'];
        if ($model->sendEmail()) {
            $this->status = true;
            $this->pesan = 'Berhasil, silahkan cek email anda';
        } else {
            $this->pesan = 'Gagal, silahkan coba beberapa saat lagi';
        }
        return [
            'status' =>  $this->status,
            'data' => $this->data,
            'pesan' => $this->pesan
        ];
    }

    public function actionResetPassword($token)
    {
        $post = Yii::$app->request->post();
        $model = new ResetPasswordForm($token);
        $model->password = $post['password'];
        $model->re_password = $post['repassword'];

        if ($model->validate() && $model->resetPassword()) {
            $this->status = true;
            $this->pesan = 'Berhasil, silahkan login menggunakan passwor baru anda';
        } else {
            $this->pesan = 'Ubah password gagal, silahkan lakukan request reset password';
        }
        return [
            'status' =>  $this->status,
            'data' => $this->data,
            'pesan' => $this->pesan
        ];
    }
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return [
            'status' =>  $this->status,
            'data' => $this->data,
            'pesan' => $this->pesan
        ];
    }
}
