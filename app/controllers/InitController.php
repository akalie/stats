<?php

class InitController extends BaseController {

	public function InitAll()
	{
        /** таблица с очередями */
        Schema::create('queues', function($table) {
            $table->increments('id');
            $table->integer('type');
            $table->integer('percent_done')->nullable();
            $table->integer('parent_queue_id')->nullable();
            $table->integer('status_id');
            $table->string('last_processed_id')->nullable();
            $table->string('public_id')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('locked_at')->nullable();
        });

        /** таблица с токенами */
        Schema::create('tokens', function($table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('status_id');
            $table->string('token');
            $table->dateTime('created_at');
        });

        /** таблица с id постов для парса */
        Schema::create('post_ids', function($table) {
            $table->increments('id');
            $table->string('label');
            $table->string('posts');
        });

    }





}
