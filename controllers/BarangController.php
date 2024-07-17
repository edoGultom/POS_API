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

class BarangController extends Controller
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
        $model = TblMenu::find()->where(['>', 'stok', 1])->all();
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
            $barang = $this->findAllModel();
            if ($barang) {
                $transaction->commit();
                $res['status'] = true;
                $res['data'] = $barang;
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
    public function actionDetail($id)
    {
        return 'Example!';
    }
    public function actionAdd()
    {
        $request = Yii::$app->request;
        $res = [];
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        $files = UploadedFile::getInstancesByName("file");

        try {
            $barang = new TblMenu();
            $data = $request->bodyParams; // Get the body of the request
            $barang->load($data, '');
            if ($barang->validate() &&  $barang->save()) {
                if (!empty($files)) {

                    $upload = new UploadForm();
                    $upload->imageFilesMenu = $files;
                    $resp = $upload->uploadFileMenu($barang->id, $barang->type);
                    // return $resp;
                    if (!$resp) {
                        $this->status = false;
                        $this->pesan = $resp;
                    }
                    $barang->path = $resp;
                    if (!$barang->save()) {
                        return [
                            'status' => false,
                            'message' => $barang->getErrors(),
                        ];
                    }
                    $transaction->commit();
                    $res['status'] = true;
                    $res['message'] = 'Berhasil menambah datsa!';
                    $res['data'] =  $this->findModel($barang->id);
                } else {
                    $res['status'] = false;
                    $res['pesan'] = 'file kosong!';
                }
            } else {
                return [
                    'status' => false,
                    // 'message' => $barang->getRequiredAttributes()
                    'message' => $barang->getErrors(),
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
        $request = Yii::$app->request;
        $transaction = $connection->beginTransaction();
        $data =  Yii::$app->request->getBodyParams();
        $files = UploadedFile::getInstancesByName("file");
        try {
            $barang =  $this->findModel($id);
            $currentStok = $barang->stok;
            if ($barang) {
                $barang->setAttributes($data); // Set the attributes manually
                if ($data['stok'] > $currentStok) {
                    $barang->type = 'addition';
                    $resStok = $barang->getNewStok();

                    if (!$resStok) {
                        return [
                            'status' => false,
                            'message' => $resStok,
                        ];
                    }
                }
                if ($barang->validate() && $barang->save()) {
                    if (!empty($files)) {
                        UploadedFiledb::find()->where(['filename' =>  $barang->path])->one()->delete();
                        unlink(Yii::getAlias('@' . $barang->path));
                        $upload = new UploadForm();
                        $upload->imageFilesMenu = $files;
                        $resp = $upload->uploadFileMenu($barang->id, $barang->type);
                        if (!$resp) {
                            $this->status = false;
                            $this->pesan = $resp;
                        }
                        $barang->path = $resp;
                        if (!$barang->save()) {
                            return [
                                'status' => false,
                                'message' => $barang->getErrors(),
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
                        'message' => $barang->getErrors(),
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
            $barang =  $this->findModel($id);
            UploadedFiledb::find()->where(['filename' =>  $barang->path])->one()->delete();
            unlink(Yii::getAlias('@' . $barang->path));
            if ($barang->delete()) {
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
