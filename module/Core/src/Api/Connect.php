<?php
    /**
     * Created by PhpStorm.
     * User: José Lucio
     * Date: 24/03/17
     * Time: 11:54
     */

    namespace Core\Api;


    class Connect
    {
        private $apiToken = "";

        /** @var Urls|null */
        private $urls = null;

        public function __construct(Urls $urls)
        {
            $this->apiToken = $urls->getApiSecret();
            $this->urls = $urls;
        }


        /**
         * @param $name
         * @return string
         */
        private function setApiToken($name)
        {
            return str_replace(":api_token", $this->apiToken, $this->urls->getUrl($name));
        }

        public function get($name, array $data, array $replace = [])
        {
            // set token de acesso para url
            $url = $this->setApiToken($name);
            $queryString = http_build_query($data);

            list($baseUrl, $query) = explode("?", $url);

            if (!empty($query)) {
                if (!empty($queryString))
                    $queryString .= "&" . $query;
                else
                    $queryString = $query;
            }

            foreach ($replace as $k => $item) {
                $baseUrl = str_replace(":" . $k, $item, $baseUrl);
            }

            $url = sprintf("%s?%s", $baseUrl, $queryString);

            //open connection
            $ch = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            //execute post
            $result = curl_exec($ch);
            //close connection
            curl_close($ch);

            return json_decode($result, true);

        }

        public function post($name, array $data, $json = false)
        {
            // set token de acesso para url
            $url = $this->setApiToken($name);

            $fields_string = "";
            if ($json) {
                $fields_string = json_encode($data);
            } else {
                //url-ify the data for the POST
                foreach ($data as $key => $value) {
                    $fields_string .= $key . '=' . $value . '&';
                }
                $fields_string = rtrim($fields_string, '&');
            }


            //open connection
            $ch = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, count($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

            //execute post
            $result = curl_exec($ch);
            //close connection
            curl_close($ch);

            return json_decode($result, true);

        }
    }