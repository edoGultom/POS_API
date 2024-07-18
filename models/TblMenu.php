<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use Yii;

/**
 * This is the model class for table "partai".
 *
 * @property int $id
 * @property int|null $no_urut_partai
 * @property string|null $nama_partai
 * @property string|null $keterangan
 */
class TblMenu extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_menu';
    }
    public function behaviors()
    {
        return [
            TimestampBehavior::class
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['path'], 'string'],
            [['nama'], 'string', 'max' => 255],
            [['id_kategori', 'harga', 'created_at', 'updated_at'], 'integer'],
        ];
    }
}
