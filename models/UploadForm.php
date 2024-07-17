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
    public $imageFilesMenu;

    public function rules()
    {
        return [
            [['imageFile', 'imageFilesMenu'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg'],
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
    public function uploadFileMenu($id, $type)
    {
        if (!empty($this->imageFilesMenu)) {
            // return $this->imageFilesMenu;
            $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();
            try {
                $no = 0;
                // return $this->imageFilesMenu;
                $arrIdFile = [];
                foreach ($this->imageFilesMenu as  $value) {
                    $no++;
                    $ext = pathinfo($value->name, PATHINFO_EXTENSION);
                    $nameFile =  'Menu_' . $id . '_file' . time() . '.' . $ext;
                    $value->saveAs('@temp/' . $nameFile);

                    $newNameFile =   'Menu_' . $id . '_file' . time() . '_compressed.' . $ext;
                    $newPath = Yii::getAlias('@files/' . $newNameFile);
                    $fileDb = new UploadedFiledb();
                    $fileDb->name = $value->name;
                    $fileDb->size = $value->size;
                    $fileDb->filename = 'files/' . $newNameFile;
                    $fileDb->type = $value->type;
                    if (!$fileDb->validate()) {
                        return $fileDb->getErrors();
                    }
                    if ($fileDb->save()) {
                        Image::getImagine()->open(Yii::getAlias('@temp/') . $nameFile)
                            ->thumbnail(new Box(800, 800))
                            ->save($newPath, ['quality' => 100]);
                        unlink(Yii::getAlias('@temp/') . $nameFile);
                        $value->saveAs($newPath);
                        array_push($arrIdFile, $fileDb->filename);
                    }
                }
                // $pengaduan = TblMenu::findOne(['id' => $id]);
                // $pengaduan->path =  implode(', ', $arrIdFile);
                // $pengaduan->type =  $type;
                // if ($pengaduan->save()) {
                $transaction->commit();
                return implode(', ', $arrIdFile);
                // }
                // return  $pengaduan->getErrors();
            } catch (\Exception $e) {
                $transaction->rollBack();
                return $e->getMessage();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                return $e->getMessage();
            }
        } else {
            return $this->getErrors();
        }
    }
}
