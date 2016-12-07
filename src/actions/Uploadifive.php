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


class Uploadifive extends Action
{
    /**
     * @var string Path to directory where files will be uploaded
     */
    public $path;


    public $object;
    public $objectId;

    /**
     * @var string Validator name
     */
    public $uploadOnlyImage = true;

    /**
     * @var string Variable's name that Imperavi Redactor sent upon image/file upload.
     */
    public $uploadParam = 'file_upload';

    public $uploadType = Image::IMG_LIST;

    /**
     * @var boolean If `true` unique filename will be generated automatically
     */
    public $unique = true;

    public $watermark;
    public $cachePath;

    public $ifSavedBase = false;

    private $pathObject;
    private $newFileName;
    private $fileTitle;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (is_null($this->path)) {
            throw new InvalidConfigException('The "path" attribute must be set.');
        } else {

            //Получаем параметры
            if (is_null($this->object)) {
                $this->object = Yii::$app->request->post("object");
            }
            if (is_null($this->objectId)) {
                $this->objectId = Yii::$app->request->post("object_id");
            }
            if (is_null($this->uploadType)) {
                $this->uploadType = Yii::$app->request->post("upload_type");
            }

            $this->pathObject = rtrim("{$this->path}/{$this->object}/{$this->objectId}", "/");

            if (!file_exists($this->pathObject)) {
                if (!FileHelper::createDirectory($this->pathObject)) {
                    throw new InvalidConfigException(
                        "Directory specified in 'path' attribute doesn't exist or cannot be created."
                    );
                }
            }

            if ($this->ifSavedBase) {
                if (empty($this->object) || empty($this->objectId)) {
                    throw new InvalidConfigException(
                        "не указан объекет и его ID для записи в базу данных"
                    );
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $errors = [];

        if (!Yii::$app->request->isPost) {
            $errors[] = 'Only POST is allowed';
            return $errors;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        $file = UploadedFile::getInstanceByName('file_upload');

        $this->fileTitle = $file->baseName;
        $this->newFileName = (Inflector::slug($file->baseName, '_')).
            "_".(uniqid())."."."{$file->extension}";

        if (!$file->saveAs($this->getFilePath())) {
            $errors[] = "Error save file";
        }

        if (!is_null($this->watermark)) {
            $this->setWaterMark();
        }

        if ($this->ifSavedBase) {
            if (!$this->saveBase()) {
                $errors[] = "Error save file";
            } else {
                $errors[] = "Good save file";
            }
        }

        return $errors;
    }

    public function getFilePath()
    {
        return "{$this->pathObject}/{$this->newFileName}";
    }


    private function saveBase()
    {
        $newModel                   = new Image();
        $newModel->title            = $this->fileTitle;
        $newModel->status           = Image::STATUS_DRAFT;
        $newModel->type             = $this->uploadType;
        $newModel->sitemap          = 1;
        $newModel->object_table     = $this->object;
        $newModel->object_id        = $this->objectId;
        $newModel->file_name        = $this->newFileName;
        $newModel->file_url         = $this->pathObject;
        return $newModel->save();
    }

    public function setWaterMark($right = true)
    {
        if (!file_exists(Yii::getAlias($this->getFilePath()))) {
            throw new Exception('Image Path not detected!');
        }

        if (!file_exists(Yii::getAlias($this->watermark))) {
            throw new Exception('WaterMark not detected!');
        }

        $image = new SimpleImage($this->getFilePath());

        $wmMaxWidth = intval($image->get_width() * 0.4);
        $wmMaxHeight = intval($image->get_height() * 0.4);

        $waterMarkPath = Yii::getAlias($this->watermark);

        $waterMark = new SimpleImage($waterMarkPath);

        if (
            $waterMark->get_height() > $wmMaxHeight
            or
            $waterMark->get_width() > $wmMaxWidth
        ) {
            $waterMarkPathInfo = pathinfo($waterMark);

            $waterMarkPath = "{$this->cachePath}{${DIRECTORY_SEPARATOR}}{$waterMarkPathInfo['filename']}
                {$wmMaxWidth}x{$wmMaxHeight}.{$waterMarkPathInfo['extension']}";

            //throw new Exception($waterMarkPath);
            if (!file_exists($waterMarkPath)) {
                $waterMark->fit_to_width($wmMaxWidth);
                $waterMark->save($waterMarkPath, 100);
                if (!file_exists($waterMarkPath)) {
                    throw new Exception("Cant save watermark to {$waterMarkPath}!!!");
                }
            }
        }

        if ($right) {
            $image->overlay($waterMarkPath, "bottom right", .5, -10, -10);
        } else {
            $image->overlay($waterMarkPath, "bottom left", .5, 10, 10);
        }

        $image->save($this->getFilePath(), 100);
    }


}
