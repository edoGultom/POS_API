<?php

namespace app\controllers;

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
use yii\web\UploadedFile;

class UploadFileController extends Controller
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
                    'upload'  => ['POST'],
                    'test'  => ['GET'],
                ],
            ],
        ]);
    }
    public function actionTest()
    {
        return 'a';
    }

    public function actionUpload()
    {
        $model = new UploadForm();

        $model->imageFile =  UploadedFile::getInstanceByName('imageFile');
        $resp = $model->uploadProfile();

        echo "<pre>";
        print_r($resp);
        echo "</pre>";
        exit();
        if ($resp) {
            // file is uploaded successfully
            $this->status = true;
            $this->pesan = "Berhasil Upload Dokumen";
            $this->data = [
                'path' => $resp
            ];
        } else {
            $this->status = false;
            $this->pesan = $model->uploadProfile();
        }


        return [
            'status' => $this->status,
            'pesan' => $this->pesan,
            'data' => $this->data,
        ];
    }
}
