<?php

use yii\helpers\StringHelper;

/**
 * This is the template for generating a CRUD controller class file.
 *
 * @var yii\web\View $this
 * @var schmunk42\giiant\generators\crud\Generator $generator
 */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
$searchModelClassName = $searchModelClass;
if ($modelClass === $searchModelClass) {
	$searchModelAlias = $searchModelClass.'Search';
	$searchModelClassName = $searchModelAlias;
}

$pks = $generator->getTableSchema()->primaryKey;
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();
echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>\base;

use <?= ltrim($generator->modelClass, '\\') ?>;
<?php if($searchModelClass !== ""): ?>
use <?= ltrim($generator->searchModelClass, '\\') ?><?php if (isset($searchModelAlias)):?> as <?= $searchModelAlias ?><?php endif ?>;
<?php endif; ?>
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use yii\web\HttpException;
use yii\helpers\Url;
use yii\filters\AccessControl;
use dmstr\bootstrap\Tabs;

/**
 * <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
 */
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{
    /**
     * @var boolean whether to enable CSRF validation for the actions in this controller.
     * CSRF validation is enabled only when both this property and [[Request::enableCsrfValidation]] are true.
     */
    public $enableCsrfValidation = false;

	<?php if ($generator->accessFilter): ?>
	/**
	* @inheritdoc
	*/
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
				'rules' => [
					[
						'allow' => true,
						'matchCallback' => function ($rule, $action) {return \Yii::$app->user->can($this->module->id . '_' . $this->id . '_' . $action->id, ['route' => true]);},
					]
				]
			]
		];
	}
	<?php endif; ?>

	/**
	 * Lists all <?= $modelClass ?> models.
	 * @return mixed
	 */
	public function actionIndex()
	{
<?php if($searchModelClass !== ""){ ?>
		$searchModel  = new <?= $searchModelClassName ?>;
		$dataProvider = $searchModel->search($_GET);
<?php }else{ ?>
		$dataProvider = new ActiveDataProvider([
			'query' => <?= $modelClass ?>::find(),
		]);
<?php } ?>

		Tabs::clearLocalStorage();

        Url::remember();
        \Yii::$app->session['__crudReturnUrl'] = null;

		return $this->render('index', [
			'dataProvider' => $dataProvider,
<?php if($searchModelClass !== ""): ?>
			'searchModel' => $searchModel,
<?php endif; ?>
		]);
	}

	/**
	 * Displays a single <?= $modelClass ?> model.
	 * <?= implode("\n\t * ", $actionParamComments) . "\n" ?>
     *
	 * @return mixed
	 */
	public function actionView(<?= $actionParams ?>)
	{
        \Yii::$app->session['__crudReturnUrl'] = Url::previous();
        Url::remember();
        Tabs::rememberActiveState();

        return $this->render('view', [
			'model' => $this->findModel(<?= $actionParams ?>),
		]);
	}

	/**
	 * Creates a new <?= $modelClass ?> model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @return mixed
	 */
	public function actionCreate()
	{
		$model = new <?= $modelClass ?>;

		try {
            if ($model->load($_POST) && $model->save()) {
                return $this->redirect(Url::previous());
            } elseif (!\Yii::$app->request->isPost) {
                $model->load($_GET);
            }
        } catch (\Exception $e) {
            $msg = (isset($e->errorInfo[2]))?$e->errorInfo[2]:$e->getMessage();
            $model->addError('_exception', $msg);
		}
        return $this->render('create', ['model' => $model]);
	}

	/**
	 * Updates an existing <?= $modelClass ?> model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * <?= implode("\n\t * ", $actionParamComments) . "\n" ?>
	 * @return mixed
	 */
	public function actionUpdate(<?= $actionParams ?>)
	{
		$model = $this->findModel(<?= $actionParams ?>);

		if ($model->load($_POST) && $model->save()) {
            return $this->redirect(Url::previous());
		} else {
			return $this->render('update', [
				'model' => $model,
			]);
		}
	}

	/**
	 * Deletes an existing <?= $modelClass ?> model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * <?= implode("\n\t * ", $actionParamComments) . "\n" ?>
	 * @return mixed
	 */
	public function actionDelete(<?= $actionParams ?>)
	{
        try {
            $this->findModel(<?= $actionParams ?>)->delete();
        } catch (\Exception $e) {
            $msg = (isset($e->errorInfo[2]))?$e->errorInfo[2]:$e->getMessage();
            \Yii::$app->getSession()->addFlash('error', $msg);
            return $this->redirect(Url::previous());
        }

        // TODO: improve detection
        $isPivot = strstr('<?= $actionParams ?>',',');
        if ($isPivot == true) {
            return $this->redirect(Url::previous());
        } elseif (isset(\Yii::$app->session['__crudReturnUrl']) && \Yii::$app->session['__crudReturnUrl'] != '/') {
			Url::remember(null);
			$url = \Yii::$app->session['__crudReturnUrl'];
			\Yii::$app->session['__crudReturnUrl'] = null;

			return $this->redirect($url);
        } else {
            return $this->redirect(['index']);
        }
	}

	/**
	 * Finds the <?= $modelClass ?> model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * <?= implode("\n\t * ", $actionParamComments) . "\n" ?>
	 * @return <?= $modelClass ?> the loaded model
	 * @throws HttpException if the model cannot be found
	 */
	protected function findModel(<?= $actionParams ?>)
	{
<?php
if (count($pks) === 1) {
	$condition = '$'.$pks[0];
} else {
	$condition = [];
	foreach ($pks as $pk) {
		$condition[] = "'$pk' => \$$pk";
	}
	$condition = '[' . implode(', ', $condition) . ']';
}
?>
		if (($model = <?= $modelClass ?>::findOne(<?= $condition ?>)) !== null) {
			return $model;
		} else {
			throw new HttpException(404, 'The requested page does not exist.');
		}
	}
}
