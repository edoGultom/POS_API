<?php

namespace app\controllers;

use Yii;
use \yii\web\Response;
use app\models\User;
use yii\helpers\ArrayHelper;
use app\models\ResetPasswordForm;
use app\models\AuthAssignment;
use app\models\AuthItem;
use app\models\OauthAccessTokens;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;
use yii\web\NotFoundHttpException;
use OAuth2\Request;
use OAuth2\Response as OAuth2Response; // Buat alias untuk Response
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

    protected function findRoles()
    {
        $model = AuthItem::find()->where(['type' => 1])->all();
        if (count($model) > 0) {
            return $model;
        }
        throw new NotFoundHttpException('Data Tidak Ditemukan.');
    }
    public function actionRoles()
    {
        $res = [];
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $model = $this->findRoles();
            if ($model) {
                $newData = [];
                foreach ($model as $item) {
                    $newData[] = [
                        'id' => $item->name,
                        'nama' => $item->name
                    ];
                }

                $transaction->commit();
                $res['status'] = true;
                $res['data'] = $newData;
                $res['message'] = 'Berhasil mengambil data!';
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
        return $res;
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
            $data['user']['scope'] = ArrayHelper::getColumn($hakAkses, function ($m) {
                return str_replace(" ", "_", $m['item_name']);
            });
            $data['expires'] = strtotime($model->expires);
            $data['access_token'] = $result['access_token'];
            $data['token_type'] = $result['token_type'];
            $data['refresh_token'] = $result['refresh_token'];
            $model->expires = Yii::$app->formatter->asDate($data['expires'], 'php: Y-m-d H:i:s');
            $model->setAttribute('scope', implode(" ", $data['user']['scope']));
            $model->save();
            return $data;
        }
        return false;
    }

    public function actionRefreshToken()
    {
        $response = new OAuth2Response(); // Gunakan alias di sini
        $oauth2Server = Yii::$app->getModule('oauth2')->getServer();
        $oauth2Server->handleTokenRequest(Request::createFromGlobals(), $response);
        $res = $response->getParameters();
        if (isset($res['access_token'])) {
            return $res;
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
                    [$user->id, $post['role'], time()],
                ])->execute();
                $transaction->commit();
                $query = (new \yii\db\Query());
                $query->select('*')
                    ->from('user')
                    ->where(['like', 'lower(username)',  strtolower($user->username)])->one();
                $command = $query->createCommand();
                $data = $command->queryOne();
                $hakAkses = AuthAssignment::find()->select(['item_name'])->where(['user_id' => $user->id])->asArray()->all();
                $data['user']['scope'] = ArrayHelper::getColumn($hakAkses, function ($m) {
                    return str_replace(" ", "_", $m['item_name']);
                });
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
