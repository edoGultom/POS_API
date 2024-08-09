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
class TblPemesanan extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public $arrMenu;
    public static function tableName()
    {
        return 'tbl_pemesanan';
    }
    public function rules()
    {
        return [
            [['id_meja', 'id_pelayan'], 'integer'],
            [['status'], 'in', 'range' => ['ordered', 'in_progress', 'ready', 'served', 'paid', 'pending payment', 'expired payment', 'canceled payment']],
            ['waktu', 'safe'],
        ];
    }
    private function formatString($string)
    {
        $string = str_replace('_', ' ', $string);
        $string = ucwords($string);
        return $string;
    }
    public function fields()
    {
        $fields = parent::fields();
        $fields['pelayan']  = function ($model) {
            return $this->user->name ?? '';
        };
        $fields['order_detail']  = function ($model) {
            return $this->orderDetail ?? [];
        };
        $fields['meja']  = function ($model) {
            return $this->meja ?? '';
        };
        $fields['total']  = function ($model) {
            return $this->sumOrderDetail ?? 0;
        };
        $fields['quantity']  = function ($model) {
            return $this->sumQty ?? 0;
        };
        $fields['waktu']  = function ($model) {
            return Yii::$app->formatter->asDateTime($this->waktu, 'php:d-m-Y H:i:s');
        };
        $fields['status']  = function ($model) {
            return $this->formatString($this->status);
        };
        return $fields;
    }
    public function restokBahan($listBahanBaku)
    {
        $iDs = array_column(TblTransaksiStok::find()->select('id')->where(['tanggal' => date("Y-m-d")])->asArray()->all(), 'id');

        if (!$iDs) {
            throw new \Exception('Data Stok Tidak Ditemukan');
        }
        $transaction = Yii::$app->db->beginTransaction();
        $idNewTrx = null;

        try {
            $bahanBaku = (array) $listBahanBaku;
            $bahanBaku = array_map(function ($item) {
                return (object) $item;
            }, $bahanBaku);

            // SAVE TRX FIRST
            $newTrx =  TblTransaksiStok::findOne(['tipe' => 'Keluar', 'tanggal' => date("Y-m-d")]);
            if (!$newTrx) {
                $newTrx = new TblTransaksiStok();
            }
            $newTrx->kode = $newTrx->setKode();
            $newTrx->tipe = 'Keluar';
            $newTrx->tanggal = date('Y-m-d');
            //END SAVE TRX FIRST
            if (!$newTrx->save()) {
                throw new Exception('Gagal Simpan Stok: ' . print_r($newTrx->errors, true));
            }
            $idNewTrx = $newTrx->id;

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            echo "Transaction failed: " . $e->getMessage() . "\n";
            return false;
        }

        if ($transaction::READ_COMMITTED) {
            foreach ($bahanBaku as $value) {
                $modelTrxStokBahan = TblTransaksiStokBahanBaku::find();

                $sumCurrentStcok = $modelTrxStokBahan
                    ->where(['IN', 'id_transaksi_stok', $iDs])
                    ->andWhere(['id_bahan_baku' => $value->id_bahan_baku])
                    ->sum('quantity');


                if ($sumCurrentStcok < $value->quantity) {
                    throw new \Exception('Stock Tidak Cukup! Harap lapor admin');
                }

                $newStock = $modelTrxStokBahan->where([
                    'id_transaksi_stok' => $idNewTrx,
                    'id_bahan_baku' => $value->id_bahan_baku
                ])->one();

                if (!$newStock) {
                    $newStock = new TblTransaksiStokBahanBaku();
                    $newStock->id_transaksi_stok = $idNewTrx;
                    $newStock->quantity -= $value->quantity;
                    $newStock->id_bahan_baku = $value->id_bahan_baku;
                    // echo "<pre>";
                    // print_r($newStock->quantity);
                    // echo "</pre>";
                    // exit();
                } else {
                    $quantityCurrent = $newStock->quantity;
                    $newStock->quantity = $quantityCurrent - $value->quantity;
                }

                if (!$newStock->save()) {
                    throw new Exception('Failed save stock: ' . print_r($newStock->errors, true));
                }
            }
            return true;
        }
        throw new Exception('Failed read trycatch ');
    }
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $connection = Yii::$app->db;
            $transaction = $connection->beginTransaction();
            try {
                $table = TblMeja::findOne(['id' => $this->id_meja]);
                if ($table) {
                    $table->status = 'Occupied';
                    $table->save();
                }
                foreach ($this->arrMenu as $value) {
                    $model = new TblPemesananDetail();
                    $model->id_pemesanan = $this->id;
                    $model->id_menu = $value['id'];
                    $model->quantity = $value['qty'];
                    $model->harga = $value['harga'] + $value['harga_ekstra'];
                    $model->total = $value['totalHarga'];
                    $model->temperatur = $value['temperatur'];
                    $model->status = $this->status;
                    if (!$model->save()) {
                        throw new \Exception('Failed to save data');
                    }
                }

                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
                throw new Exception('Failed to save ordered: ' . $e->getMessage());
            }
        }
    }
    public function isAllChange($status)
    {
        $countPemesananDetail = count($this->getOrderDetail()->all());

        $statusPemesananDetail = TblPemesananDetail::findAll([
            'id_pemesanan' => $this->id,
            'status' => $status
        ]);
        return $countPemesananDetail === count($statusPemesananDetail);
    }
    public function getOrderDetail()
    {
        return $this->hasMany(TblPemesananDetail::class, ['id_pemesanan' => 'id'])->orderBy(['id' => SORT_DESC]);
    }
    public function getSumOrderDetail()
    {
        return $this->hasMany(TblPemesananDetail::class, ['id_pemesanan' => 'id'])->sum('total');
    }
    public function getSumQty()
    {
        return $this->hasMany(TblPemesananDetail::class, ['id_pemesanan' => 'id'])->sum('quantity');
    }
    public function getMeja()
    {
        return $this->hasOne(TblMeja::class, ['id' => 'id_meja'])->orderBy(['id' => SORT_DESC]);
    }
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'id_pelayan']);
    }
}
