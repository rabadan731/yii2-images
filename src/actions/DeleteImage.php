<?php

namespace rabadan731\images\actions;

use rabadan731\images\components\SimpleImage;
use rabadan731\images\models\Image;
use Yii;

use yii\base\Exception;
use yii\helpers\FileHelper;

use yii\base\Action;
use yii\base\InvalidConfigException;

use yii\helpers\Inflector;
use yii\web\UploadedFile; 
use yii\web\Response;


class DeleteImage extends Action
{



    /**
     * @inheritdoc
     */
    public function init()
    {

    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isPost) {
            return 'Only POST is allowed';
        }

        $id = Yii::$app->request->post("delete_id");

        $model = Image::findOne($id);
        if (is_null($model)) {
            return "Запись изображения с ID: {$id} не найдена";
        }

        if (!$model->delete()) {
            return ("Ошибка удаление данных. ".print_r($model->errors, 1));
        }

        return 1;
    }


    public function getPathRoot()
    {
        return Yii::getAlias("@root/storage");
    }


}
