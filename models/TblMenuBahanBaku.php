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
    public function fields()
    {
        $fields = parent::fields();

        $fields['bahan_baku']  = function ($model) {
            return $this->bahanBaku->nama ?? '';
        };
        $fields['unit']  = function ($model) {
            return $this->bahanBaku->unit->nama ?? '';
        };
        return $fields;
    }
    public function getMenu()
    {
        return $this->hasOne(TblMenu::class, ['id' => 'id_menu'])->orderBy(['id' => SORT_DESC]);
    }
    public function getBahanBaku()
    {
        return $this->hasOne(TblBahanBaku::class, ['id' => 'id_bahan_baku'])->orderBy(['id' => SORT_DESC]);
    }
}
