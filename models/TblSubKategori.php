<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use Yii;

class TblSubKategori extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_sub_kategori';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_kategori'], 'integer'],
            [['nama_sub_kategori'], 'string', 'max' => 255],
        ];
    }
}
