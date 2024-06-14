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

class ReportController extends Controller
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
                    'by-date'  => ['POST'],
                ],
            ],
        ]);
    }
    protected function findByDateRange($start, $end)
    {
        $model = Yii::$app->db->createCommand(
            "
                SELECT 
                    to_char(to_timestamp(s.created_at), 'YYYY-mm-dd') AS date,
                    p.id, -- ID produk
                    p.nama_barang, -- Nama produk
                    SUM(si.qty) AS total_quantity_sold, -- Total kuantitas yang terjual
                    SUM(si.harga * si.qty) AS total_sales_amount, -- Total penjualan dalam nilai uang
                    SUM(CASE WHEN py.payment_method = 'CASH' THEN si.harga * si.qty ELSE 0 END) AS cash_sales, -- Total penjualan tunai
                    SUM(CASE WHEN py.payment_method = 'QRIS' THEN si.harga * si.qty ELSE 0 END) AS qris_sales -- Total penjualan via QRIS
                FROM 
                    tbl_penjualan s
                JOIN 
                    tbl_penjualan_barang si ON s.id = si.id_penjualan -- Gabung tabel Sales dan Sale_Items
                JOIN 
                    tbl_barang p ON si.id_barang = p.id -- Gabung tabel Sale_Items dan Products
                LEFT JOIN 
                    tbl_pembayaran py ON s.id = py.id_penjualan -- Gabung tabel Sales dan Payments
                WHERE 
                     to_char(to_timestamp(s.created_at), 'YYYY-mm-dd') BETWEEN '$start' AND '$end' -- Filter berdasarkan tanggal
                GROUP BY 
                    to_char(to_timestamp(s.created_at), 'YYYY-mm-dd'), p.id, p.nama_barang -- Kelompokkan berdasarkan tanggal, ID produk, dan nama produk
                ORDER BY 
                    to_char(to_timestamp(s.created_at), 'YYYY-mm-dd'), p.id; -- Urutkan berdasarkan tanggal dan ID produk
            "
        )->queryAll();
        return array_values($model);
    }
    protected function findAllModel()
    {
        $model = TblBarang::find()->all();
        if (count($model) > 0) {
            return $model;
        }
        throw new NotFoundHttpException('Data Tidak Ditemukan.');
    }
    public function actionByDate()
    {
        $res = [];
        $connection = Yii::$app->db;
        $request = Yii::$app->request;
        $data = $request->bodyParams; // Get the body of the request
        $dateStart = $data['start'];
        $dateEnd = $data['end'];
        $model = $this->findByDateRange($dateStart, $dateEnd);

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
