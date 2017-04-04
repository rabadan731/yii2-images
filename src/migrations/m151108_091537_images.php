<?php

use yii\db\Migration;

class m151108_091537_images extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%r731_images}}', [
            'image_id'                  => $this->primaryKey(),
            'title'                     => $this->string(256), //заголовок
            'description'               => $this->text(), //заголовок

            'status'                    => $this->smallInteger()->notNull()->defaultValue(1),       //публикация
            'position'                  => $this->smallInteger()->notNull()->defaultValue(1000),    //Позиция
            'type'                      => $this->smallInteger()->notNull()->defaultValue(0),       //Тип (Фото описания, фото объекта, фото альбома, логотип объекта и т.п
            'sitemap'                   => $this->smallInteger()->notNull()->defaultValue(1),       //Публикация в сайтмап

            'object_class'              => $this->string(1024),   //класс объекта
            'object_table'              => $this->string(256)->notNull(),   //таблица объекта
            'object_id'                 => $this->integer()->notNull(),     // Id объекта

            'file_name'                 => $this->string(256)->notNull(),   // только имя файла
            'file_folder'               => $this->string(256),              // относительный путь с корня до файла
            'file_url'                  => $this->string(1024)->notNull(),  // полный url
            'crop'                      => $this->string(256),              // фиксированый кроп изображения

            'created_at'                => $this->integer(),    //дата создания
            'updated_at'                => $this->integer(),    //дата редактирования
            'eventDate'                 => $this->integer(),    //дата редактирования

            'created_by'                => $this->integer(),    //кто создал
            'updated_by'                => $this->integer(),    //кто редактирования

        ], $tableOptions);


        $this->createTable('{{%r731_albums}}', [
            'album_id'                  => $this->primaryKey(),
            'title'                     => $this->string(256), //заголовок
            'description'               => $this->text(), //заголовок

            'status'                    => $this->smallInteger()->notNull()->defaultValue(1),       //публикация
            'position'                  => $this->smallInteger()->notNull()->defaultValue(1000),

            'created_at'                => $this->integer(),    //дата создания
            'updated_at'                => $this->integer(),    //дата редактирования
        ]);

    }

    public function safeDown()
    {
        $this->dropTable('{{%r731_albums}}');
        $this->dropTable('{{%r731_images}}');
    }

}
