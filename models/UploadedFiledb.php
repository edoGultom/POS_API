<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "uploaded_file".
 *
 * @property int $id
 * @property string $name
 * @property string $filename
 * @property int $size
 * @property string $type
 */
class UploadedFiledb extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'uploaded_file';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['size'], 'integer'],
            [['captions'], 'string'],
            [['name', 'filename'], 'string', 'max' => 255],
            [['filename'], 'file', 'extensions' => 'jpg,png,jpeg,mp4,mkv', 'maxSize' => 1024 * 1024 * 25, 'tooBig' => 'Maksimal 20MB',],
            [['status'], 'default', 'value' => 1],
            [['type'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {

        return [
            'id' => 'ID',
            'name' => 'Name',
            'filename' => 'Filename',
            'size' => 'Size',
            'type' => 'Type',
            'captions' => 'Captions',
        ];
    }

    public function getFileApplication()
    {
        $query = UploadedFiledb::find()->where(['like', 'type', 'application'])->orderBy(['created_at' => SORT_DESC]);
        return $query;
    }

    public function deleteFile()
    {
        $query = UploadedFiledb::findOne($this->id);
        if ($query) {
            if (file_exists($query->filename)) {
                if (unlink($query->filename)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return true;
        }
    }
}
