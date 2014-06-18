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


/**
 * страницы
 */
Route::get('/', ['uses' => 'IndexController@showIndex']); // пустая

Route::match(['GET', 'POST'], '/tokens', ['uses' => 'IndexController@tokenForm']);

Route::match(['GET', 'POST'], '/form', ['uses' => 'IndexController@showForm']);

Route::match(['GET', 'POST'], '/post', ['uses' => 'IndexController@postForm']);

/**
 * скачивание csv
 */
Route::get('/download/{filename}', function($filename = 'qq') {
    $fullPath = public_path() . '/csv/' . $filename;
    if( is_file($fullPath) ) {
        return Response::download($fullPath);
    } else {
        return 'File not exists';
    }
});

/**
 * удаление токена
 */
Route::get('/deleteToken/{tokenId}', function($tokenId = -1) {
    if ($tokenId != -1 && is_numeric($tokenId)) {
        TokenRepository::deleteToken($tokenId);
    }

    return Redirect::to('/tokens');
});

/**
 * удаление паблика
 */
Route::get('/deleteQueue/{queueId}', function($queueId = -1) {
    if ($queueId != -1 && is_numeric($queueId)) {
        QueueRepository::deleteQueue($queueId);
    }

    return Redirect::to('/form');
});

/**
 * демоны
 */
Route::get('/daemons/boards-parser', ['uses' => 'DaemonsController@ParseBoardChunk']);
Route::get('/daemons/albums-parser', ['uses' => 'DaemonsController@ParseAlbumChunk']);
Route::get('/daemons/posts-parser',  ['uses' => 'DaemonsController@ParsePostChunk']);
Route::get('/daemons/csv-parser',    ['uses' => 'DaemonsController@CheckCSV']);

/**
 * инициализация проекта
 */
#Route::get('/init/all', ['uses' => 'InitController@InitAll']);
