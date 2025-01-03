<?php

namespace app\controllers;

use app\models\TblMenu;
use app\models\UploadedFiledb;
use app\models\UploadForm;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\Response;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;
use filsh\yii2\oauth2server\filters\auth\CompositeAuth;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class MenuController extends Controller
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
            'authenticator' => [
                'class' => CompositeAuth::class,
                'authMethods' => [
                    ['class' => HttpBearerAuth::class],
                    ['class' => QueryParamAuth::class, 'tokenParam' => 'accessToken'],
                ]
            ],
            'exceptionFilter' => [
                'class' => ErrorToExceptionFilter::class
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index'  => ['GET'],
                    'add'  => ['POST'],
                    'update'  => ['POST'],
                    'delete'  => ['DELETE'],
                    'test'  => ['GET'],

                ],
            ],
        ]);
    }
    public function actionTest()
    {
        return 'a';
    }
    protected function findModel($id)
    {
        $model = TblMenu::findOne($id);
        if ($model !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Data Tidak Ditemukan.');
    }
    protected function findAllModel()
    {
        $model = TblMenu::find()->all();
        if (count($model) > 0) {
            return $model;
        }
        throw new NotFoundHttpException('Data Tidak Ditemukan.');
    }
    public function actionIndex()
    {
        $res = [];
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $menu = $this->findAllModel();
            if ($menu) {
                $transaction->commit();
                $res['status'] = true;
                $res['data'] = $menu;
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
    public function actionAdd()
    {
        $request = Yii::$app->request;
        $res = [];
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        $files = UploadedFile::getInstancesByName("file");

        try {
            $menu = new TblMenu();
            $data = $request->bodyParams; // Get the body of the request
            $menu->load($data, '');
            if ($menu->validate() &&  $menu->save()) {
                if (!empty($files)) {
                    $upload = new UploadForm();
                    $upload->imageFilesMenu = $files;
                    $resp = $upload->uploadFileMenu($menu->id);
                    // return $resp;
                    if (!$resp) {
                        $this->status = false;
                        $this->pesan = $resp;
                    }
                    $menu->path = $resp;
                    if (!$menu->save()) {
                        return [
                            'status' => false,
                            'message' => $menu->getErrors(),
                        ];
                    }
                    $transaction->commit();
                    $res['status'] = true;
                    $res['message'] = 'Berhasil menambah data!';
                    $res['data'] =  $this->findModel($menu->id);
                } else {
                    $res['status'] = false;
                    $res['pesan'] = 'file kosong!';
                }
            } else {
                return [
                    'status' => false,
                    // 'message' => $menu->getRequiredAttributes()
                    'message' => $menu->getErrors(),
                ];
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
    public function actionUpdate($id)
    {
        $res = [];
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        $data =  Yii::$app->request->getBodyParams();
        $files = UploadedFile::getInstancesByName("file");
        try {
            $menu =  $this->findModel($id);;
            if ($menu) {
                $menu->setAttributes($data); // Set the attributes manually
                if ($menu->validate() && $menu->save()) {
                    if (!empty($files)) {
                        UploadedFiledb::find()->where(['filename' =>  $menu->path])->one()->delete();
                        unlink(Yii::getAlias('@' . $menu->path));
                        $upload = new UploadForm();
                        $upload->imageFilesMenu = $files;
                        $resp = $upload->uploadFileMenu($menu->id);
                        if (!$resp) {
                            $this->status = false;
                            $this->pesan = $resp;
                        }
                        $menu->path = $resp;
                        if (!$menu->save()) {
                            return [
                                'status' => false,
                                'message' => $menu->getErrors(),
                            ];
                        }
                    }
                    $transaction->commit();
                    $res['status'] = true;
                    $res['message'] = 'Berhasil merubah data!';
                    $res['data'] = $this->findModel($id);
                } else {
                    return [
                        'status' => false,
                        'message' => $menu->getErrors(),
                    ];
                }
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
    public function actionDelete($id)
    {
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $menu =  $this->findModel($id);
            UploadedFiledb::find()->where(['filename' =>  $menu->path])->one()->delete();
            unlink(Yii::getAlias('@' . $menu->path));
            if ($menu->delete()) {
                $res['status'] = true;
                $res['message'] = 'Berhasil menghapus data!';
                // $res['data'] =  $this->findModel($id);
                $transaction->commit();
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
}
