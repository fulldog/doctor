<?php
/**
 * Created by PhpStorm.
 * User: weilone
 * Date: 2019/1/15
 * Time: 0:21
 */

namespace backend\controllers\doctors;


use backend\controllers\AdminUserController;
use backend\models\DadminUser;
use backend\models\search\DadminUserSearch;
use Yii;
use backend\models\User;
use backend\models\search\UserSearch;
use backend\actions\IndexAction;
use backend\actions\DeleteAction;
use backend\actions\SortAction;
use backend\actions\ViewAction;
use yii\web\Response;

class DadminUserController extends AdminUserController
{
    protected $name_fix = 'hospital_admin';

    public function actions()
    {
        return [
            'index' => [
                'class' => IndexAction::className(),
                'data' => function(){
                    /** @var UserSearch $searchModel */
                    $searchModel = Yii::createObject( DadminUserSearch::className() );
                    $dataProvider = $searchModel->search(Yii::$app->getRequest()->getQueryParams());
                    return [
                        'dataProvider' => $dataProvider,
                        'searchModel' => $searchModel,
                    ];
                }
            ],
            'view-layer' => [
                'class' => ViewAction::className(),
                'modelClass' => DadminUser::className(),
            ],
            'delete' => [
                'class' => DeleteAction::className(),
                'modelClass' => DadminUser::className(),
            ],
            'sort' => [
                'class' => SortAction::className(),
                'modelClass' => DadminUser::className(),
            ],
        ];
    }

    /**
     * 创建管理员账号
     *
     * @auth - item group=权限 category=管理员 description=创建 sort-get=524 sort-post=525 method=get,post
     * @return string|\yii\web\Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        /** @var User $model */
        $model = Yii::createObject( DadminUser::className() );
        $model->setScenario('create');
        if (Yii::$app->getRequest()->getIsPost()) {
            $data = Yii::$app->getRequest()->post();
            if ($data["DadminUser"]['username']){
                $data["DadminUser"]['username'] = $this->name_fix.$data["DadminUser"]['username'];
            }
            if ( $model->load($data) && $model->save() && $model->assignPermission() ) {
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'Success'));
                return $this->redirect(['index']);
            } else {
                $errors = $model->getErrors();
                $err = '';
                foreach ($errors as $v) {
                    $err .= $v[0] . '<br>';
                }
                Yii::$app->getSession()->setFlash('error', $err);
            }
        }
        $model->loadDefaultValues();
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * 创建医院管理员
     * @param $hospital_id
     * @return string|\yii\web\Response
     * @throws \yii\base\InvalidConfigException
     */
    function actionHospital($hospital_id){
        $model = Yii::createObject( DadminUser::className() );
        $model->setScenario('create');
//        $model->username = $this->name_fix.$hospital_id;
        $model->hospital_id = $hospital_id;
        $model->username = $model->password= $this->name_fix.$hospital_id;
        $model->permissions = [];

        if ($model->save() && $model->assignPermission() ) {
            Yii::$app->getSession()->setFlash('success', '创建成功');
        } else {
            Yii::$app->getSession()->setFlash('error', $model->getFirstError());
        }
        return true;
    }
}