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

    public function actionLogin()
    {
        $response = Yii::$app->getModule('oauth2')->getServer()->handleTokenRequest();
        $result = $response->getParameters();

        $data = [];
        if (isset($result['access_token'])) {
            $model = OauthAccessTokens::find()->where(['access_token' => $result['access_token']])->one();

            $query = (new \yii\db\Query());
            $query->select('*')
                ->from('user')
                ->where(['=', 'id',  strtolower($model->user_id)])->one();
            $command = $query->createCommand();
            $user = $command->queryOne();

            $data['user'] = $user;

            $hakAkses = AuthAssignment::find()->select(['item_name'])->where(['user_id' => $model->user_id])->asArray()->all();
            $data['expires'] = strtotime($model->expires);
            $data['scope'] = ArrayHelper::getColumn($hakAkses, function ($m) {
                return str_replace(" ", "_", $m['item_name']);
            });

            $data['access_token'] = $result['access_token'];
            $data['token_type'] = $result['token_type'];
            $data['refresh_token'] = $result['refresh_token'];
            $model->expires = Yii::$app->formatter->asDate($data['expires'], 'php: Y-m-d H:i:s');
            $model->setAttribute('scope', implode(" ", $data['scope']));
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
    public function actionTest()
    {
        return 'a';
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
        $result = [];
        try {
            $user->auth_key = Yii::$app->security->generateRandomString();
            $user->username = $post['username'];
            $user->name = $post['name'];

            $user->email = $post['email'];
            $user->setPassword($post['password']);
            $user->generateAuthKey();
            $user->generateEmailVerificationToken();
            $user->status = 10;
            $user->address = $post['address'];
            $user->created_at = time();
            $user->updated_at = time();

            if ($user->validate() && $user->save()) {
                $connection->createCommand()->batchInsert('auth_assignment', [
                    'user_id',
                    'item_name',
                    'created_at'
                ], [
                    [$user->id, 'User', time()],
                ])->execute();
                $transaction->commit();
                $query = (new \yii\db\Query());
                $query->select('*')
                    ->from('user')
                    ->where(['like', 'lower(username)',  strtolower($user->username)])->one();
                $command = $query->createCommand();
                $data = $command->queryOne();

                $this->data = $data;
                $this->status = true;
                $this->pesan = 'register berhasil';
            } else {
                return 'a';
                $this->status = false;
                $this->pesan = $user->getErrors();
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            $this->pesan =  $e->getMessage();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            $this->pesan =  $e->getMessage();
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
