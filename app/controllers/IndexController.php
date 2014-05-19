<?php

class IndexController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public function showIndex()
	{

	}

    public function showForm()
	{
        $from = Input::get('from');
        $to   = Input::get('to');
        $idString = Input::get('idString');
        $type  = Input::get('type');
        $errorMsg = null;
        $resultIds = null;

        if (!in_array($type, ['repost', 'likes', 'borderComments'])) {
            $errorMsg = 'Это какая-то неправильная статистика';
        }
        $id = false;
        if (is_null($errorMsg) && ($idString) && !($id = $this->parseIdString($type, $idString))) {
            $errorMsg = 'Не получилось распознать URL поста/топика.';
        }

        if (is_null($errorMsg)) {

            $resultIds = StatHelper::GetIds($type, $id);
        }
        return View::make('statIndex')
            ->with('resultIds', $resultIds)
            ->with('errorMsg', $errorMsg)
            ->with('idString', $idString)
            ->with('from', $from)
            ->with('to', $to);
	}

    public function parseIdString($type, $idString) {
        switch($type) {
            case 'repost':
            case 'likes' :
                if (!preg_match('/wall(-?\d+_\d+)/', $idString, $matches)) {
                    return false;
                }

                return $matches[1];
            case 'borderComments':
                if (!preg_match('/topic-(\d+_\d+)/', $idString, $matches)) {
                    return false;
                }
                return $matches[1];
            default:
                return false;
        }
    }



}
