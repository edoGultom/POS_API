<?php

namespace app\models;

use app\models\User;
use Yii;

use yii\base\Model;
use yii\web\UploadedFile;

use yii\imagine\Image;
use Imagine\Image\Box;

class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $imageFile;
    public $imageFilesPengaduan;

    public function rules()
    {
        return [
            [['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg'],
        ];
    }

    public function uploadProfile()
    {
        if ($this->validate()) {
            $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();
            try {
                $ext = $this->imageFile->extension;
                $nameFile =  'profil_' . Yii::$app->user->identity->id . '.' . $ext;
                $this->imageFile->saveAs('@temp/' . $nameFile);

                $newNameFile =   'profil_' . Yii::$app->user->identity->id . '_compressed.' . $ext;
                Image::getImagine()->open(Yii::getAlias('@temp/') . $nameFile)
                    ->thumbnail(new Box(200, 200))
                    ->save(Yii::getAlias('@files/' . $newNameFile), ['quality' => 100]);
                unlink(Yii::getAlias('@temp/') . $nameFile);
                $path = 'files/' . $newNameFile;
                $user = User::find()->where(['id' => Yii::$app->user->identity->id])->one();

                if ($user) {
                    $user->profile_photo_path = $path;
                    $user->save(false);
                    $transaction->commit();
                    return $path;
                }
                return  false;
            } catch (\Exception $e) {
                $transaction->rollBack();
                return $e->getMessage();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                return $e->getMessage();
            }
        } else {
            return $this->getErrors();
            return false;
        }
    }
}
