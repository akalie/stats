<?php
    define ( 'VK_API_URL' , 'https://api.vk.com/method/' );
    class vkException extends Exception {
        public $captchaSig;
        public $captchaImg;
    };
    class   VkHelper {

        const PERM_NOTIFY = 1;             //Пользователь разрешил отправлять ему уведомления.
        const PERM_FRIENDS = 2;            //Доступ к друзьям.
        const PERM_PHOTO = 4;              //Доступ к фотографиям.
        const PERM_AUDIO = 8;              //Доступ к аудиозаписям.
        const PERM_VIDEO = 16;             //Доступ к видеозаписям.
        const PERM_APPS = 32;              //Доступ к предложениям.
        const PERM_QUESTIONS = 64;         //Доступ к вопросам.
        const PERM_WIKI = 128;             //Доступ к wiki-страницам.
        const PERM_LEFTMENU = 256;         //Добавление ссылки на приложение в меню слева.
        const PERM_QUICKPUBLISH = 512;     //Добавление ссылки на приложение для быстрой публикации на стенах пользователей.
        const PERM_STATUS = 1024;          //Доступ к статусам пользователя.
        const PERM_NOTES = 2048;           //Доступ заметкам пользователя.
        const PERM_MSG_EXTENDED = 4096;    //(для Desktop-приложений) Доступ к расширенным методам работы с сообщениями.
        const PERM_WALL = 8192;            //Доступ к обычным и расширенным методам работы со стеной.
        const PERM_ADS = 32768;            //Доступ к функциям для работы с рекламным кабинетом.
        const PERM_OFFLINE = 65536;        //Оффлайн-доступ
        const PERM_DOCS = 131072;          //Доступ к документам пользователя.
        const PERM_GROUPS = 262144;        //Доступ к группам пользователя.
        const PERM_NOTIFY_ANSWER = 524288; //Доступ к оповещениям об ответах пользователю.
        const PERM_GROUP_STATS = 1048576;  //Доступ к статистике групп и приложений пользователя, администратором которых он является.

        const PAUSE   = 0.5;

        public static $tries = 0;

        public static  $open_methods = array(
            'wall.get'          => true,
            'groups.getById'    => true,
            'wall.getById'      => true,
            'photos.getAlbums'  => true,
        );

        public static function api_request( $method, $request_params )
        {
            $url = VK_API_URL . $method;
            $a = VkHelper::qurl_request( $url, $request_params );
            $res = json_decode(  $a );
            if( !$res )
                return array();
            if ( isset( $res->error ) ) {
                $Ex = new vkException('Error : ' . $res->error->error_msg . ' on params ' . json_encode( $request_params ) );
                if( isset($res->error->captcha_sid)) {
                    $Ex->captchaSig = $res->error->captcha_sid;
                    $Ex->captchaImg = $res->error->captcha_img;
                }
                throw $Ex;

            }
            return $res->response;
        }

        public static function qurl_request( $url, $arr_of_fields, $headers = '', $uagent = '')
        {
            if (empty( $url )) {
                return false;
            }
            $ch = curl_init( $url );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT , 180 );

            if (is_array( $headers )) { // если заданы какие-то заголовки для браузера
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }

            if (!empty($uagent)) { // если задан UserAgent
                curl_setopt($ch, CURLOPT_USERAGENT, $uagent);
            } else{
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1)');
            }

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            if (is_array( $arr_of_fields )) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arr_of_fields));

            } else return false;

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo "<br>error in curl: ". curl_error($ch) ."<br>";
                return 'error in curl: '. curl_error($ch);
            }

            curl_close($ch);
            return $result;
        }

        public static function get_vk_time( $access_token = '' )
        {
            return self::api_request( 'getServerTime', array( 'access_token' =>  $access_token ), 0 );
        }

        public static function multiget( $urls, &$result )
        {
            $timeout = 20; // максимальное время загрузки страницы в секундах
            $threads = 20; // количество потоков

            $all_useragents = array(
            "Opera/9.23 (Windows NT 5.1; U; ru)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.4;MEGAUPLOAD 1.0",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; Alexa Toolbar; MEGAUPLOAD 2.0; rv:1.8.1.7) Gecko/20070914 Firefox/2.0.0.7;MEGAUPLOAD 1.0",
            "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)",
            "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)",
            "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)",
            "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; WOW64; Maxthon; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; Media Center PC 5.0; InfoPath.1)",
            "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)",
            "Opera/9.10 (Windows NT 5.1; U; ru)",
            "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.2.1; aggregator:Tailrank; http://tailrank.com/robot) Gecko/20021130",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.8",
            "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0; MyIE2; Maxthon)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.8",
            "Opera/9.22 (Windows NT 6.0; U; ru)",
            "Opera/9.22 (Windows NT 6.0; U; ru)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.8",
            "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30)",
            "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; MRSPUTNIK 1, 8, 0, 17 HW; MRA 4.10 (build 01952); .NET CLR 1.1.4322; .NET CLR 2.0.50727)",
            "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9"
            );

            $useragent = $all_useragents[ array_rand( $all_useragents )];

            $i = 0;
            for( $i = 0; $i < count( $urls ); $i = $i + $threads )
            {
                $urls_pack[] = array_slice( $urls, $i, $threads );
            }
            foreach( $urls_pack as $pack )
            {
                $mh = curl_multi_init();
                unset( $conn );
                foreach ( $pack as $i => $url )
                {
                    $conn[$i]=curl_init( trim( $url ));
                    curl_setopt($conn[$i], CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($conn[$i], CURLOPT_TIMEOUT, $timeout );
                    curl_setopt($conn[$i], CURLOPT_USERAGENT, $useragent );
                    curl_multi_add_handle ( $mh,$conn[ $i ]);
                }
                do {
                    $n=curl_multi_exec( $mh,$active );
                    sleep( 0.01 ); }
                while ( $active );

                foreach ( $pack as $i => $url )
                {
                    $result[]=curl_multi_getcontent( $conn[ $i ]);
                    curl_close( $conn[$i] );
                }
                curl_multi_close( $mh );
            }
        }



        public static function check_at( $access_token )
        {
            $res = self::get_vk_time( $access_token );
            sleep( self::PAUSE );
            if ( isset( $res->error )) {
                //self::deactivate_at( $access_token );
                return false;
            }
            return true;
        }

           public static function connect( $link, $cookie=null, $post=null, $includeHeader = true)
        {
            $ch = curl_init();

            curl_setopt( $ch, CURLOPT_URL, $link );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $ch, CURLOPT_TIMEOUT, 0 );
            if ($includeHeader) {
                curl_setopt( $ch, CURLOPT_HEADER, 1 );
            }
            curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0 );
            curl_setopt($ch, CURLOPT_USERAGENT,
                'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.57 Safari/537.17');
            if( $cookie !== null )
                curl_setopt( $ch, CURLOPT_COOKIE, $cookie );
            if( $post !== null )
            {
                curl_setopt( $ch, CURLOPT_POST, 1 );
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );
            }
            $res = curl_exec( $ch );
            curl_close( $ch );
            return $res;
        }

        public static function vk_authorize( $login = null, $pass = null )
        {
            if( !$login) {
                shuffle( self::$serv_bots);
                $login = self::$serv_bots[0]['login'];
                $pass  = self::$serv_bots[0]['pass'];
            }
            $res = self::connect("http://login.vk.com/?act=login&email=$login&pass=$pass");
            if( !preg_match("/hash=([a-z0-9]{1,32})/", $res, $hash )) {
                return false;
            }
            $res = self::connect("http://vk.com/login.php?act=slogin&hash=" . $hash[1] );
            if( preg_match( "/remixsid=(.*?);/", $res, $sid ))
                return "remixchk=5; remixsid=$sid[1]";
            return false;
        }

        public static function send_alert( $message, $reciever_vk_ids )
        {
            if( !is_array( $reciever_vk_ids )) {
                $reciever_vk_ids = array( $reciever_vk_ids );
            }
            foreach( $reciever_vk_ids as $vk_id) {
                $params = array(
                    'uid'           =>   $vk_id,
                    'message'       =>   $message . ' ' . md5(time()) ,
                    'access_token'  =>   self::ALERT_TOKEN,
                );
                VkHelper::api_request( 'messages.send', $params );
                sleep( self::PAUSE );
            }
        }

    }
?>