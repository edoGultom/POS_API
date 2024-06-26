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
                'class' => CompositeAuth::className(),
                'authMethods' => [
                    ['class' => HttpBearerAuth::className()],
                    ['class' => QueryParamAuth::className(), 'tokenParam' => 'accessToken'],
                ]
            ],
            'exceptionFilter' => [
                'class' => ErrorToExceptionFilter::className()
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'upload'  => ['POST'],
                ],
            ],
        ]);
    }

    public function actionUpload()
    {
        $model = new UploadForm();
        echo 'a';
        exit();
        $model->imageFile =  UploadedFile::getInstanceByName('imageFile');
        $resp = $model->uploadProfile();

        return $resp;
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
