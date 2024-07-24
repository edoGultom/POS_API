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
    public function fields()
    {
        $fields = parent::fields();
        $fields['nama_kategori']  = function ($model) {
            return $this->kategori->nama_kategori ?? '';
        };
        $fields['list_bahan_baku']  = function ($model) {
            $data = $this->getMenuBahanBaku()->all();
            return $data;
        };
        return $fields;
    }
    public function getKategori()
    {
        return $this->hasOne(TblKategori::class, ['id' => 'id_kategori'])->orderBy(['id' => SORT_DESC]);
    }
    public function getMenuBahanBaku()
    {
        return $this->hasMany(TblMenuBahanBaku::class, ['id_menu' => 'id'])->orderBy(['id' => SORT_DESC]);
    }
}
