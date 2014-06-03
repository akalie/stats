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

Route::get('/daemons/csv-parser', ['uses' => 'DaemonsController@CheckCSV']);

Route::get('/', ['uses' => 'IndexController@showIndex']);

Route::match(['GET', 'POST'], '/form', array('uses' => 'IndexController@showForm'));

Route::get('/download/{filename}', function($filename = 'qq') {
    $fullPath = public_path() . '/csv/' . $filename;
    if( is_file($fullPath) ) {
        return Response::download($fullPath);
    } else {
        return 'File not exists';
    }
});