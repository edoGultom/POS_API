<?php

namespace app\controllers;

use yii\rest\Controller;

use Yii;

class LihatFileController extends Controller
{
    public function actionProfile($path) // khusus lihat foto
    {
        $response = Yii::$app->getResponse();
        if ($path !== 'null') {
            $file = \Yii::getAlias("@"  . $path);
            if (file_exists($file)) {
                return $response->sendFile($file, 'file', [
                    'inline' => true
                ]);
            }
        }
        $file = \Yii::getAlias("@app/web/images/empty.png");
        return $response->sendFile($file, 'file', [
            'inline' => true
        ]);
    }
}
