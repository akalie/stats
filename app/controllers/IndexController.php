<?php

class IndexController extends BaseController {

	public function showIndex()
	{
        Schema::create('dfsdf' , function($table) {
            $table->integer('user_id')->index();
        });
	}

    public function showForm()
	{

        $idString = Input::get('idString');
        $errorMsg = null;
        $resultIds = null;
        if ($idString) {
            $id = $this->parsePublicId($idString);
            if (!$id) {
                $errorMsg = 'Не получилось распознать URL поста/топика.';
            }
            if (QueueRepository::createNewPublicQueues($id)) {
                StatRepository::createTablesForPublic($id);
            } else {
                $errorMsg = 'Уже есть в сиситеме';
            }

        }
        $publics = QueueRepository::getAllQueues();
        return View::make('statIndex')
            ->with('resultIds', $resultIds)
            ->with('errorMsg', $errorMsg)
            ->with('idString', $idString)
            ->with('publics', $publics);
	}


    public function parsePublicId($stringId) {
        if (is_numeric($stringId)) {
            return $stringId;
        }
        if (strpos($stringId, '/') !== false) {
            $url = explode('/', $stringId);
            $shortlink = end($url);
        } else {
            $shortlink = $stringId;
        }

        $groupInfo = VkHelper::api_request('groups.getById', ['group_ids' => $shortlink, 'v' => '5.21']);
        if (isset($groupInfo[0]->id))
            return $groupInfo[0]->id;
        return false;

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
