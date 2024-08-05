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
class TblPemesananDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tbl_pemesanan_detail';
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id_pemesanan', 'id_menu', 'quantity', 'harga', 'total', 'id_chef'], 'integer'],
            [['temperatur'], 'in', 'range' => ['HOT', 'COLD']],
            [['status'], 'in', 'range' => ['ordered', 'in_progress', 'ready', 'served', 'paid']],
        ];
    }
    public function fields()
    {
        $fields = parent::fields();
        $fields['menu']  = function ($model) {
            return $this->menu ?? '';
        };
        return $fields;
    }
    public function getMenu()
    {
        return $this->hasOne(TblMenu::class, ['id' => 'id_menu'])->orderBy(['id' => SORT_DESC]);
    }
}
