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
class TblMenuBahanBaku extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_menu_bahan_baku';
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_menu', 'id_bahan_baku', 'quantity'], 'integer'],
        ];
    }
}
