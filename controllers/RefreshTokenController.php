<?php

namespace app\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;
use OAuth2\Request;
use OAuth2\Response;

class RefreshTokenController extends \yii\rest\Controller
{
    public $pesan = '';
    public $data = '';
    public $status = false;


    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'exceptionFilter' => [
                'class' => ErrorToExceptionFilter::className()
            ],
        ]);
    }

    public function actionToken()
    {
        $request = Yii::$app->request;
        $response = new Response();
        $oauth2Server = Yii::$app->getModule('oauth2')->getServer();
        // Tangani permintaan token
        $oauth2Server->handleTokenRequest(Request::createFromGlobals(), $response);
        $response->send();
        Yii::$app->end();
    }
}
