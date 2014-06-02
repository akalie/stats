<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/daemons/board-parser', ['uses' => 'DaemonsController@ParserBoardsChunk']);

Route::get('/daemons/posts-parser', ['uses' => 'DaemonsController@ParsePostChunk']);

Route::get('/', ['uses' => 'IndexController@showIndex']);

Route::match(['GET', 'POST'],'/form', array('uses' => 'IndexController@showForm'));

