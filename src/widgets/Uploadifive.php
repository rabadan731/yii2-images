<?php

namespace rabadan731\images\widgets;

use rabadan731\images\assets\UploadifiveAsset;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Url;

/**
 * Created by PhpStorm.
 * User: rabadan
 * Date: 09.11.15
 * Time: 23:32
 */

class Uploadifive extends Widget {

    /**
     * @var string table attribute
     */
    public $tableAttribute;
    /**
     * @var int id attribute
     */
    public $idAttribute;
    /**
     * @var string id attribute
     */
    public $uploadScriptUrl;
    public $buttonText = "Добавить фото";
    public $buttonClass = "btn btn-default btn-sm";
    public $onComplete = "";
    public $uploadType = 0;//R731Images::IMG_LIST;


    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init()
    {
        if ($this->tableAttribute === null) {
            throw new InvalidConfigException('The "tableAttribute" property must be set.');
        }
        if ($this->idAttribute === null) {
            throw new InvalidConfigException('The "idAttribute" property must be set.');
        }
        if ($this->uploadScriptUrl === null) {
            throw new InvalidConfigException('The "uploadScriptUrl" property must be set.');
        }

        $view = $this->getView();
        UploadifiveAsset::register($view);
        $view->registerJs("
		$(function() {
		$('#file_upload').uploadifive({
			'buttonText': '{$this->buttonText}',
			'buttonClass': '{$this->buttonClass}',
			'auto': true,
            'formData' : {
               'object'        : '{$this->tableAttribute}',
               'object_id'     : '{$this->idAttribute}',
               'uploadType'    : '{$this->uploadType}',
               '_csrf'         :  yii.getCsrfToken()
             },
			'dnd' :true,
			'fileObjName': 'file_upload',
			'fileType' : 'image/*',
			'queueID': 'queue',
			'uploadScript': '{$this->uploadScriptUrl}',
			'onUploadComplete': function(file, data) {
				if (data=='1') {
				    file.queueItem.find('.fileinfo').html(' - Файл успешно загружен! :) ');
				} else {
				    file.queueItem.find('.fileinfo').html(' - Ошибка в загрузке файла :( ');
					file.queueItem.find('.fileinfo').parent().parent().removeClass(\"complete\").addClass(\"error\");
					console.log(data);
				}
			},
			'onQueueComplete' : function(uploads) {
			    {$this->onComplete}
			}
		});
	});");
    }


    /**
     * Executes the widget.
     * @return string the result of widget execution to be outputted.
     */
    public function run()
    {
        return '<div id="queue"></div><input id="file_upload" name="file_upload" type="file" multiple="true">';
    }
} 