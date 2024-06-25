<?php

namespace app\controllers;

use app\models\TblBarang;
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

class StokController extends Controller
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
                    'current-stock'  => ['GET'],
                ],
            ],
        ]);
    }
    protected function findCurrentStock()
    {
        $model = Yii::$app->db->createCommand(
            "
            SELECT 
                    b.nama_barang,
                    COALESCE(SUM(CASE WHEN sb.tipe = 'initialstok' THEN sb.perubahan_stok ELSE 0 END), 0)as stok_awal,
                    COALESCE(SUM(CASE WHEN sb.tipe = 'addition' THEN sb.perubahan_stok ELSE 0 END), 0) AS masuk,
                    COALESCE(SUM(CASE WHEN sb.tipe = 'sales' THEN sb.perubahan_stok ELSE 0 END), 0) AS keluar,
                    (COALESCE(SUM(CASE WHEN sb.tipe = 'initialstok' THEN sb.perubahan_stok ELSE 0 END), 0) +
                    COALESCE(SUM(CASE WHEN sb.tipe = 'addition' THEN sb.perubahan_stok ELSE 0 END), 0) +
                    COALESCE(SUM(CASE WHEN sb.tipe = 'sales' THEN sb.perubahan_stok ELSE 0 END), 0)) AS stok_akhir
            FROM 
                    tbl_barang b
            LEFT JOIN 
                    tbl_stok_barang sb ON b.id = sb.id_barang
            GROUP BY 
                    b.id, b.nama_barang;
            "
        )->queryAll();
        return array_values($model);
    }

    public function actionCurrentStock()
    {
        $res = [];
        $connection = Yii::$app->db;
        $request = Yii::$app->request;
        $model = $this->findCurrentStock();

        $transaction = $connection->beginTransaction();
        try {
            if ($model) {
                $transaction->commit();
                $res['status'] = true;
                $res['data'] = $model;
                $res['message'] = 'Berhasil mengambil data!';
            } else {
                $res['status'] = true;
                $res['message'] = 'Data Kosong';
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
