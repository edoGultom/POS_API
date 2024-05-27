<?php
echo "<?php\n";
?>
use yii\bootstrap5\Html;
use kartik\datecontrol\DateControl;
use yii\bootstrap5\ActiveForm;
use kartik\select2\Select2;
$appendBtn = '<span class="ic-search"><i data-feather="search" width="16" height="16"></i></span>';
<?php
echo "?>\n";
?>
<div class="panel {type}">
    {panelHeading}
    {panelBefore}
    <div class="px-4 pt-4 py-4">
        <?= "<?php" ?> $form = ActiveForm::begin(['method' => 'get', 'action'=>'index']); <?= "?>\n" ?>
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex bd-highlight justify-content-between">
                    <div class="p-2 flex-grow-1 bd-highlight d-flex flex-row justify-content-start align-items-center gap-2">
                        <span class="mb-3">Tampilkan</span>
                        <div>
                            <?= "<?=
                                            " ?>$form->field($searchModel, 'rowdata')->dropdownlist(
                            [10 => 10, 20 => 20, 25 => 25, 100 => 100],
                            [
                            'onchange' => '$("#form-filter").submit();'
                            ],
                            )->label(false)
                            <?= "?>\n" ?>
                        </div>
                        <span class="mb-3">Data</span>
                    </div>
                    <span class="p-2 bd-highlight d-flex align-items-center mb-3">Pencarian:</span>
                    <div class="p-2 bd-highlight w-8 bd-highlight">
                        <?= "<?=
                                            " ?>$form->field($searchModel, 'cari')->textInput(
                        [
                        'class' => 'form-control',
                        'placeholder' => 'Pencarian...',
                        ]
                        )
                        ->label(false)
                        <?= "?>\n" ?>
                    </div>
                    <div class="p-2 bd-highlight d-flex flex-row gap-2 flex-shrink-1 justify-content-end align-items-center mb-3">
                        <?= "<?=
                                    " ?>Html::submitButton(
                        '<span class="material-symbols-outlined align-middle">search</span> Cari',
                        [
                        'class' => 'btn btn-primary rounded btn-search',
                        'data-pjax' => true
                        ]
                        )
                        <?= "?>\n" ?>
                    </div>
                </div>
            </div>
        </div>
        <?= "<?php" ?> ActiveForm::end(); <?= "?>\n" ?>
        {items}
    </div>
</div>
<div class="d-flex justify-content-between px-4 py-3 align-items-center bg-footer">
    <div class="text-muted">{summary}</div>
    <div class="pagination pagination-sm">{pager}</div>
</div>