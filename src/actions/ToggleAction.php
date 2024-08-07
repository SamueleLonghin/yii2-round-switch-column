<?php

namespace samuelelonghin\grid\toggle\actions;

use samuelelonghin\grid\toggle\helpers\ModelHelper;
use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\web\BadRequestHttpException;
use samuelelonghin\grid\toggle\Module as RoundSwitchModule;

/**
 * Class ToggleAction
 * @author Nick Denry
 */
class ToggleAction extends Action
{
    /**
     * @var string name of the model
     */
    public $modelClass;

    /**
     * @var string default pk column name
     */
    public $pkColumn = 'id';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->modelClass === null) {
            throw new InvalidConfigException('The "modelClass" property must be set.');
        }
        parent::init();
    }

    /**
     * Change column value
     *
     * @param $id
     * @param $attribute
     *
     * @return \yii\web\Response
     *
     * @throws InvalidConfigException
     */
    public function run()
    {
        $request = Yii::$app->request;
        if ($request->isAjax) {
            \Yii::$app->response->format = 'json';
            $id = \Yii::$app->request->post('id');
            $attribute = \Yii::$app->request->post('attribute');
            $model = $this->findModel($this->modelClass, $id);
            $onValue = ModelHelper::getToggleValue($model, $attribute, RoundSwitchModule::SWITCH_KEY_ON);
            $offValue = ModelHelper::getToggleValue($model, $attribute, RoundSwitchModule::SWITCH_KEY_OFF);
            $model->{$attribute} = $model->{$attribute} == $onValue ? $offValue : $onValue;
            return $model->save(true, [$attribute]);
        } else {
            throw new BadRequestHttpException('Request must be XMLHttpRequest.');
        }
    }

    /**
     * Find Model
     *
     * @param $modelClass
     * @param $id
     *
     * @return ActiveRecord
     *
     * @throws BadRequestHttpException
     */

    public function findModel($modelClass, $id)
    {
        $params = \Yii::$app->request->get();
        $params[$this->pkColumn] = $id;

        if (($model = $modelClass::findOne($params)) !== null) {
            return $model;
        } else {
            throw new BadRequestHttpException('Entity not found by primary key ' . var_dump($params));
        }
    }
}
