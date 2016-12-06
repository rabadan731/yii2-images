<?php

namespace rabadan731\images\actions;

use Yii;
use rabadan731\images\models\Image;
use yii\helpers\FileHelper;
use yii\base\Action;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\base\DynamicModel;
use yii\helpers\Inflector;
use yii\web\UploadedFile;
use yii\web\BadRequestHttpException;
use yii\web\Response;


class UploadImperavi extends Action
{
    /**
     * @var string Path to directory where files will be uploaded
     */
    public $path;

    /**
     * @var string URL path to directory where files will be uploaded
     */
    public $url;
    public $dir;

    /**
     * @var string Validator name
     */
    public $uploadOnlyImage = true;

    /**
     * @var string Variable's name that Imperavi Redactor sent upon image/file upload.
     */
    public $uploadParam = 'file';

    /**
     * @var boolean If `true` unique filename will be generated automatically
     */
    public $unique = true;

    /**
     * @var array Model validator options
     */
    public $validatorOptions = [];

    /**
     * @var string Model validator name
     */
    private $_validator = 'image';

    public $saveDB = true;
    /**
     * @inheritdoc
     */
    public function init()
    {

        // Если загрузка идет в какуют конкретную модель, грузим файлы туда
        if ((Yii::$app->request->get('content_tab') !==null ) and (Yii::$app->request->get('content_id')!==null)) {
           $this->dir = (Yii::$app->request->get('content_tab'))."/".(Yii::$app->request->get('content_id'))."/".$this->dir;
        }


        if ($this->url === null) {
            throw new InvalidConfigException('The "url" attribute must be set.');
        } else {
            $this->url = rtrim(Yii::getAlias($this->url), '/') . '/' .($this->dir). '/';
        }
        if ($this->path === null) {
            throw new InvalidConfigException('The "path" attribute must be set.');
        } else {
            $this->path = rtrim(Yii::getAlias($this->path), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . (str_replace("/",DIRECTORY_SEPARATOR,$this->dir)) . DIRECTORY_SEPARATOR;

        if (!FileHelper::createDirectory($this->path)) {
            throw new InvalidCallException("Directory specified in 'path' attribute doesn't exist or cannot be created.");
        }
        }
        if ($this->uploadOnlyImage !== true) {
            $this->_validator = 'file';
        }


    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (Yii::$app->request->isPost) {

            $file = UploadedFile::getInstanceByName($this->uploadParam);
            $model = new DynamicModel(compact('file'));
            $model->addRule('file', $this->_validator, $this->validatorOptions)->validate();

            if ($model->hasErrors()) {
                $result = [
                    'error' => $model->getFirstError('file')
                ];
            } else {
                $baseName = $model->file->baseName;
                if ($this->unique === true && $model->file->extension) {
                    $model->file->name = Inflector::slug($baseName,'_'). "_". uniqid() . '.' . $model->file->extension;
                }
                if ($model->file->saveAs($this->path . $model->file->name)) {
                    $result = ['filelink' => Yii::getAlias("@frontendUrl"). $this->url . $model->file->name];
                    if ($this->uploadOnlyImage !== true) {
                        $result['filename'] = $model->file->name;
                    }
                    if ($this->saveDB) {
                        $newModel = new Image();
                        $newModel->title = $baseName;
                        $newModel->status = 1;
                        $newModel->type = Image::IMG_LIST;
                        $newModel->sitemap = 1;
                        $newModel->object_table = Yii::$app->request->get('content_tab');
                        $newModel->object_id = Yii::$app->request->get('content_id');
                        $newModel->file_name = $model->file->name;
                        $newModel->file_path = $this->url;
                        $newModel->save();
                    }
                } else {
                    $result = [
                        'error' => "ERROR_CAN_NOT_UPLOAD_FILE"
                    ];
                }
            }
            Yii::$app->response->format = Response::FORMAT_JSON;

            return $result;
        } else {
            throw new BadRequestHttpException('Only POST is allowed');
        }
    }
}
