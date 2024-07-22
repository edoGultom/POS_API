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
class TblMeja extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_meja';
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nomor_meja'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => ['Available', 'Occupied']],
            ['status', 'default', 'value' => 'Available']
        ];
    }
    // public function fields()
    // {
    //     $fields = parent::fields();
    //     $fields['number']  = function ($model) {
    //         return $this->maxNumber ?? 0;
    //     };
    //     return $fields;
    // }
    public function getMaxNumber()
    {
        return TblMeja::find()->max('id');
    }
}
