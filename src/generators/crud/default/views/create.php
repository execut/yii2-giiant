<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\web\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */

echo "<?php\n";
?>

use yii\helpers\Html;

/**
* @var yii\web\View $this
* @var <?= ltrim($generator->modelClass, '\\') ?> $model
*/

$this->title = <?= $generator->generateString('Create') ?>;
$this->params['breadcrumbs'][] = ['label' => '<?= Inflector::pluralize(
    Inflector::camel2words(StringHelper::basename($generator->modelClass))
) ?>', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="giiant-crud <?= Inflector::camel2id(StringHelper::basename($generator->modelClass), '-', true) ?>-create">

    <h1>
        <?=
        "<?= " . $generator->generateString(
            Inflector::camel2words(StringHelper::basename($generator->modelClass))
        ) . " ?>" ?>
        <small>
            <?php $label = StringHelper::basename($generator->modelClass); ?>
            <?= "<?= \$model->" . $generator->getModelNameAttribute($generator->modelClass) . " ?>" ?>
        </small>
    </h1>

    <div class="clearfix crud-navigation">
        <div class="pull-left">
            <?= "<?= " ?>
            Html::a(
            <?= $generator->generateString('Cancel') ?>,
            \yii\helpers\Url::previous(),
            ['class' => 'btn btn-default']) ?>
        </div>
    </div>

    <?= "<?= " ?>$this->render('_form', [
    'model' => $model,
    ]); ?>

</div>
