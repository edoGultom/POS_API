<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use Yii;
use yii\db\Exception;
use yii\web\NotFoundHttpException;

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
            TimestampBehavior::class,
        ];
    }
    /**
     * {@inheritdoc}
     */
    public $type;
    public $updateStok;
    public function rules()
    {
        return [
            [['nama_barang', 'harga', 'stok', 'type'], 'required'],
            [['created_at', 'updated_at', 'id_satuan', 'id_kategori', 'harga', 'stok'], 'integer'],
            [['nama_barang', 'type'], 'string', 'max' => 255],
            ['path', 'string'],
            [['type', 'updateStok'], 'safe'] //ex. addition/ sale
        ];
    }
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $stock = new TblStokBarang();
            $stock->id_barang = $this->id;
            $stock->tipe = $this->type;
            $stock->tanggal = date('Y-m-d');

            if ($insert) {
                $stock->perubahan_stok = $this->stok;
                if (!$stock->save()) {
                    throw new Exception('Failed to save the stock: ');
                }
            } else {
                if ($this->type === 'sales') {
                    $stock->perubahan_stok -= $this->updateStok;
                    if (!$stock->save()) {
                        throw new Exception('Failed to save the stock: ');
                    }
                }
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw new Exception('Failed to save the stock menus: ' . $e->getMessage());
        }
    }
    public function beforeDelete()
    {

        $stock =  TblStokBarang::findAll(['id_barang' => $this->id]);
        if (count($stock) < 1) {
            throw new NotFoundHttpException('Data Tidak Ditemukan.');
        }
        TblStokBarang::deleteAll(['id_barang' => $this->id]);
        // Call the parent implementation (optional)
        return parent::beforeDelete();
    }
    public function getNewStok()
    {
        //UPDATE
        $stockExists = TblStokBarang::findOne(['id_barang' => $this->id]);
        if (!$stockExists) {
            throw new NotFoundHttpException('Data Tidak Ditemukan.');
        }
        $model = new TblStokBarang();
        $model->id_barang = $this->id;
        $model->tipe = $this->type;
        $model->tanggal = date('Y-m-d');

        if ($this->type === 'addition') {
            $model->perubahan_stok =  ($this->stok - ($stockExists->barang->stok ?? 0)); //contoh stok awal 50 ditambah 3 yang diinput harus 53 dari mobile (v1)
            // echo "<pre>";
            // print_r($model);
            // echo "</pre>";
            // exit();
        } else if ($this->type === 'sales') {
            if ($stockExists->perubahan_stok <= $this->stok) {
                throw new NotFoundHttpException('Stock Tidak Cukup. minimal 1 stok tersedia');
            }
            $model->perubahan_stok = $stockExists->perubahan_stok - $this->stok;
        } else {
            $model->perubahan_stok = $this->stok;
        }

        if (!$model->save()) {
            throw new Exception('Failed to save the stock: ');
        }
        return true;
    }
    public function getSatuan()
    {
        return $this->hasOne(TblSatuan::class, ['id' => 'id_satuan'])->orderBy(['id' => SORT_DESC]);
    }
    public function getKategori()
    {
        return $this->hasOne(TblKategori::class, ['id' => 'id_kategori'])->orderBy(['id' => SORT_DESC]);
    }

    public function fields()
    {
        $fields = parent::fields();
        // Add extra field
        $fields['nama_satuan']  = function ($model) {
            return $this->satuan->nama_satuan ?? '';
        };
        $fields['nama_kategori']  = function ($model) {
            return $this->kategori->nama_kategori ?? '';
        };
        return $fields;
    }
    public function getRequiredAttributes()
    {
        $requiredAttributes = [];
        foreach ($this->getActiveValidators() as $validator) {
            if ($validator instanceof \yii\validators\RequiredValidator) {
                $requiredAttributes = array_merge($requiredAttributes, $validator->attributes);
            }
        }
        return $requiredAttributes;
    }
}
