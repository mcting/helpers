<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Mcting\Helpers\ApiException;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

if (!function_exists("api_error")) {
    /**
     * throw api error exception
     * @param int $code
     * @param string $message
     * @param array|null $error
     * @param array $headers
     * @author herry.yao<yuandeng@aliyun.com>
     */
    function api_error(int $code, string $message, array $error = null, array $headers = [])
    {
        throw new ApiException($code, $message, $error, null, $headers);
    }
}

if (!function_exists("api_request")) {

    /**
     * api request
     * @param string $uri
     * @param string $method
     * @param array $options
     * @param array $headers
     * @param int $timeOut
     * @param bool $ipSpoofing
     * @param string $contentType
     * @return mixed|\Psr\Http\Message\ResponseInterface|\Psr\Http\Message\StreamInterface|null
     * @throws \GuzzleHttp\Exception\GuzzleException|Exception
     * @author herry.yao<yuandeng@aliyun.com>
     */
    function api_request(string $uri, string $method, array $options = [], array $headers = [], string $contentType = "json", $timeOut = 15, $ipSpoofing = true)
    {
        if ($ipSpoofing) {
            $ip = ipv4_random();
            $headers = array_merge($headers, ["X-FORWARDED-FOR" => $ip, "CLIENT-IP" => $ip]);
        }
        $client = new Client(["headers" => $headers, "timeout" => $timeOut]);
        Log::channel("request")->info("api request send", ["uri" => $uri, "params" => $options]);
        $options = strtoupper($method) == 'GET' ? ['query' => $options] : [$contentType => $options];
        try {
            $response = $client->request($method, $uri, $options);
        } catch (RequestException $exception) {
            Log::channel("request")->error("api request error", ["uri" => $uri, "params" => $options, "status_code" => $exception->getCode(), "error_message" => $exception->getMessage()]);
            $response = $exception->getResponse();
        } catch (Exception $exception) {
            Log::channel("request")->error("api request error", ["uri" => $uri, "params" => $options, "status_code" => $exception->getCode(), "error_message" => $exception->getMessage()]);
            $response = null;
        }
        $statusCode = $response ? $response->getStatusCode() : 0;
        $response = $response ? $response->getBody() : null;
        Log::channel("request")->info("api request response", ["code" => $statusCode, "response" => $response]);
        return $response;
    }
}

if (!function_exists("ipv4_random")) {
    /**
     * ipv4 random
     * @return string
     * @throws Exception
     * @author herry.yao<yuandeng@aliyun.com>
     */
    function ipv4_random()
    {
        $ipLong = [
            ['607649792', '608174079'],         //36.56.0.0-36.63.255.255
            ['975044608', '977272831'],         //58.30.0.0-58.63.255.255
            ['999751680', '999784447'],         //59.151.0.0-59.151.127.255
            ['1019346944', '1019478015'],       //60.194.0.0-60.195.255.255
            ['1038614528', '1039007743'],       //61.232.0.0-61.237.255.255
            ['1783627776', '1784676351'],       //106.80.0.0-106.95.255.255
            ['1947009024', '1947074559'],       //116.13.0.0-116.13.255.255
            ['1987051520', '1988034559'],       //118.112.0.0-118.126.255.255
            ['2035023872', '2035154943'],       //121.76.0.0-121.77.255.255
            ['2078801920', '2079064063'],       //123.232.0.0-123.235.255.255
            ['-1950089216', '-1948778497'],     //139.196.0.0-139.215.255.255
            ['-1425539072', '-1425014785'],     //171.8.0.0-171.15.255.255
            ['-1236271104', '-1235419137'],     //182.80.0.0-182.92.255.255
            ['-770113536', '-768606209'],       //210.25.0.0-210.47.255.255
            ['-569376768', '-564133889'],       //222.16.0.0-222.95.255.255
        ];
        $randKey = random_int(0, count($ipLong) - 1);
        $ip = long2ip(mt_rand($ipLong[$randKey][0], $ipLong[$randKey][1]));
        return $ip;
    }
}

if (!function_exists("str_random")) {
    /**
     * string random
     * @param int $length
     * @return string
     * @throws Exception
     */
    function str_random(int $length)
    {
        $string = '';
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = random_bytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }
        return $string;
    }
}

if (!function_exists("numeric_random")) {
    /**
     * numeric random
     * @param int $length
     * @return string
     * @throws Exception
     * @author herry.yao<yuandeng@aliyun.com>
     */
    function numeric_random(int $length)
    {
        $numeric = '';
        while (($len = strlen($numeric)) < $length) {
            $numeric .= random_int(0, 9);
        }
        return $numeric;
    }
}


if (!function_exists("current_date")) {
    /**
     * get current date
     * @param string $format
     * @return false|string
     * @author herry.yao<yuandeng@aliyun.com>
     */
    function current_date(string $format = "Y-m-d")
    {
        return date($format);
    }
}

if (!function_exists('current_time')) {
    /**
     * get current time
     * @param string $format
     * @return false|string
     * @author herry.yao<yuandeng@aliyun.com>
     */
    function current_time(string $format = "H:i:s")
    {
        return date($format);
    }
}

if (!function_exists("current_datetime")) {
    /**
     * get current datetime
     * @param string $format
     * @return false|string
     * @author herry.yao<yuandeng@aliyun.com>
     */
    function current_datetime(string $format = "Y-m-d H:i:s")
    {
        return date($format);
    }
}

if (!function_exists("get_datetime")) {
    /**
     * get datetime
     * @param string $time
     * @param string $format
     * @return false|string
     * @author herry.yao<yuandeng@aliyun.com>
     */
    function get_datetime(string $time = "-1 day", string $format = "Y-m-d H:i:s")
    {
        return date($format, strtotime($time));
    }
}

if (!function_exists("current_timestamp")) {
    /**
     * get current timestamp
     * @param bool $microTime micro time default true
     * @return int|double
     * @author herry.yao<yuandeng@aliyun.com>
     */
    function current_timestamp(bool $microTime = true)
    {
        return $microTime ? microtime($microTime) : time();
    }
}

if (!function_exists('get_millisecond')) {
    /**
     * get millisecond
     * @return float
     */
    function get_millisecond()
    {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
    }
}

if (!function_exists("id_card_verify")) {
    /**
     * id card number verify
     * @param string $idCard
     * @return bool
     * @author herry.yao<yuandeng@aliyun.com>
     */
    function id_card_verify(string $idCard)
    {
        //判断格式
        if (!preg_match("/^((1[1-5])|(2[1-3])|(3[1-7])|(4[1-6])|(5[0-4])|(6[1-5])|(71)|(8[1-3])|(91))\d{4}((1[89])|(2[0-3]))\d{2}((0[1-9])|(1[0-2]))((0[1-9])|([12][0-9])|(3[01]))\d{3}[0-9Xx]$/", $idCard)) return false;
        $birth = substr($idCard, 6, 8);
        if ($birth != date('Ymd', strtotime($birth))) return false;
        //加权因子
        $factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        //校验码对应值
        $code = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
        $checksum = 0;
        for ($i = 0; $i < 17; $i++) {
            $checksum += $idCard[$i] * $factor[$i];
        }
        return $code[$checksum % 11] == strtoupper($idCard[17]);
    }
}

if (!function_exists("get_age")) {
    /**
     * get age by birthday
     * @param string $birthday
     * @return false|int|string
     * @author herry.yao<yuandeng@aliyun.com>
     */
    function get_age(string $birthday)
    {
        $birthTime = strtotime($birthday);
        $age = date("Y") - date("Y", $birthTime);
        $age = (date("m") < date("m", $birthTime)
            || (date("m", $birthTime) == date("m")
                && date("d") < date("d", $birthTime))) ? $age - 1 : $age;

        return $age > 0 ? $age : 0;
    }
}

if (!function_exists("get_id_card_info")) {
    /**
     * get id card number info
     * @param string $idCard
     * @return array|bool
     * @author herry.yao<yuandeng@aliyun.com>
     */
    function get_id_card_info(string $idCard)
    {
        if (!id_card_verify($idCard)) return false;
        $birthday = date("Y-m-d", strtotime(substr($idCard, 6, 8)));
        return [
            "birthday" => $birthday,
            "age"      => get_age($birthday),
            "gender"   => $idCard[16] / 2 ? 1 : 2
        ];
    }
}

if (!function_exists("com_create_guid")) {
    /**
     * com create guid
     * @return string
     * @author herry.yao<yuandeng@aliyun.com>
     */
    function com_create_guid()
    {
        mt_srand((double)microtime() * 10000);
        $charId = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);
        $uuid = chr(123)
            . substr($charId, 0, 8) . $hyphen
            . substr($charId, 8, 4) . $hyphen
            . substr($charId, 12, 4) . $hyphen
            . substr($charId, 16, 4) . $hyphen
            . substr($charId, 20, 12)
            . chr(125);
        return $uuid;
    }
}

if (!function_exists("charset_encode")) {
    /**
     * 实现多种字符编码方式
     * @param string $input 需要编码的字符串
     * @param string $outputCharset 输出的编码格式
     * @return mixed|string 编码后的字符串
     */
    function charset_encode(string $input, string $outputCharset)
    {
        $inputCharset = get_mb_detect_encoding($input);
        if (!isset($outputCharset)) $outputCharset = $inputCharset;
        if ($inputCharset == $outputCharset || $input == null) {
            $output = $input;
        } elseif (function_exists("mb_convert_encoding")) {
            $output = mb_convert_encoding($input, $outputCharset, $inputCharset);
        } elseif (function_exists("iconv")) {
            $output = iconv($inputCharset, $outputCharset, $input);
        } else die("sorry, you have no libs support for charset change.");
        return $output;
    }
}

if (!function_exists("get_mb_detect_encoding")) {
    /**
     * get string encoding
     * @param string $str
     * @return bool|false|mixed|string
     * @author herry.yao<yuandeng@aliyun.com>
     */
    function get_mb_detect_encoding(string $str)
    {
        return function_exists("mb_detect_encoding") ? mb_detect_encoding($str, ["ASCII", "UTF-8", "GB2312", "GBK", "BIG5"]) : false;
    }
}

if (!function_exists("get_mb_strlen")) {
    /**
     * get mb string length
     * @param $str
     * @return bool|int
     * @author herry.yao<yuandeng@aliyun.com>
     */
    function get_mb_strlen(string $str)
    {
        return mb_strlen($str, get_mb_detect_encoding($str));
    }
}

if (!function_exists("gen_mb_substr")) {
    /**
     * gen mb substr
     * @param $str
     * @param int $start
     * @param null|int $length
     * @param string $postfix
     * @return string
     * @author herry.yao<yuandeng@aliyun.com>
     */
    function gen_mb_substr(string $str, int $start = 0, int $length = null, string $postfix = "")
    {
        $strLg = get_mb_strlen($str);
        $length = $length ? $length : ($strLg - $start);
        $postfix = $strLg > ($length + $start) ? $postfix : "";
        return mb_substr($str, $start, $length, get_mb_detect_encoding($str)) . $postfix;
    }
}

if (!function_exists("filter_bom")) {
    /**
     * filter bom
     * @param string $str
     * @author herry.yao<yuandeng@aliyun.com>
     */
    function filter_bom(string &$str)
    {
        if (substr($str, 0, 3) == pack("CCC", 0xEF, 0xBB, 0xBF)) $str = substr($str, 3);
    }
}

if (!function_exists("uuid_short")) {
    /**
     * gent uuid short
     * @param string $prefix
     * @param bool $moreEntropy
     * @return string
     * @author herry.yao<yuandeng@aliyun.com>
     */
    function uuid_short($prefix = "", $moreEntropy = true)
    {
        return base_convert(uniqid($prefix, $moreEntropy), 16, 10);
    }
}

if (!function_exists("export_rsa_key")) {
    /**
     * @return array|bool
     * @author herry.yao<yuandeng@aliyun.com>
     */
    function export_rsa_key()
    {
        $config = array(
            "digest_alg"       => "sha512",
            "private_key_bits" => 4096,           //字节数  512 1024 2048  4096 等
            "private_key_type" => OPENSSL_KEYTYPE_RSA,   //加密类型
        );
        $res = openssl_pkey_new($config);
        if ($res === false) return false;
        openssl_pkey_export($res, $privateKey);
        $publicKey = openssl_pkey_get_details($res);
        openssl_free_key($res);
        return [
            "private_key" => $privateKey,
            "public_key"  => $publicKey["key"]
        ];
    }
}

if (!function_exists("is_mobile")) {
    /**
     * get current request is mobile
     * @return bool
     * @author herry.yao<yuandeng@aliyun.com>
     */
    function is_mobile()
    {
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) return true;
        if (isset($_SERVER['HTTP_VIA'])) return true;
        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $mobileAgents = [
            'mobile', 'iphone', 'android', 'phone', 'mobile', 'wap', 'netfront', 'java', 'opera mobi',
            'opera mini', 'ucweb', 'windows ce', 'symbian', 'series', 'webos', 'sony', 'blackberry', 'dopod',
            'nokia', 'samsung', 'palmsource', 'xda', 'pieplus', 'meizu', 'midp', 'cldc', 'motorola', 'foma',
            'docomo', 'up.browser', 'up.link', 'blazer', 'helio', 'hosin', 'huawei', 'novarra', 'coolpad',
            'techfaith', 'alcatel', 'amoi', 'ktouch', 'nexian', 'ericsson', 'philips', 'sagem', 'wellcom',
            'bunjalloo', 'maui', 'smartphone', 'iemobile', 'spice', 'bird', 'zte-', 'longcos', 'pantech',
            'gionee', 'portalmmm', 'jig browser', 'hiptop', 'benq', 'haier', '^lct', '320x320', '240x320',
            '176x220', 'windows phone', 'cect', 'compal', 'ctl', 'nec', 'tcl', 'daxian', 'dbtel', 'eastcom',
            'konka', 'kejian', 'lenovo', 'mot', 'soutec', 'sgh', 'sed', 'capitel', 'panasonic', 'sonyericsson',
            'sharp', 'panda', 'zte', 'acer', 'acoon', 'acs-', 'abacho', 'ahong', 'airness', 'anywhereyougo.com',
            'applewebkit/525', 'applewebkit/532', 'asus', 'audio', 'au-mic', 'avantogo', 'becker', 'bilbo',
            'bleu', 'cdm-', 'danger', 'elaine', 'eric', 'etouch', 'fly ', 'fly_', 'fly-', 'go.web', 'goodaccess',
            'gradiente', 'grundig', 'hedy', 'hitachi', 'htc', 'hutchison', 'inno', 'ipad', 'ipaq', 'ipod',
            'jbrowser', 'kddi', 'kgt', 'kwc', 'lg', 'lg2', 'lg3', 'lg4', 'lg5', 'lg7', 'lg8', 'lg9', 'lg-', 'lge-',
            'lge9', 'maemo', 'mercator', 'meridian', 'micromax', 'mini', 'mitsu', 'mmm', 'mmp', 'mobi', 'mot-',
            'moto', 'nec-', 'newgen', 'nf-browser', 'nintendo', 'nitro', 'nook', 'obigo', 'palm', 'pg-',
            'playstation', 'pocket', 'pt-', 'qc-', 'qtek', 'rover', 'sama', 'samu', 'sanyo', 'sch-', 'scooter',
            'sec-', 'sendo', 'sgh-', 'siemens', 'sie-', 'softbank', 'sprint', 'spv', 'tablet', 'talkabout',
            'tcl-', 'teleca', 'telit', 'tianyu', 'tim-', 'toshiba', 'tsm', 'utec', 'utstar', 'verykool', 'virgin',
            'vk-', 'voda', 'voxtel', 'vx', 'wellco', 'wig browser', 'wii', 'wireless', 'xde', 'pad', 'gt-p1000'];
        foreach ($mobileAgents as $device) {
            if (stristr($userAgent, $device)) {
                return true;
            }
        }
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false)
                && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false
                    || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }
}


if (!function_exists("str_to_bytes")) {
    /**
     * string to bytes array
     * @param string $string
     * @return array
     */
    function str_to_bytes(string $string)
    {
        $len = strlen($string);
        $bytes = array();
        for ($i = 0; $i < $len; $i++) {
            if (ord($string[$i]) >= 128) {
                $byte = ord($string[$i]) - 256;
            } else {
                $byte = ord($string[$i]);
            }
            $bytes[] = $byte;
        }
        return $bytes;
    }
}

if (!function_exists("bytes_to_string")) {
    /**
     * bytes array to string
     * @param array $bytes
     * @return string
     */
    function bytes_to_string(array $bytes)
    {
        $str = '';
        foreach ($bytes as $ch) {
            $str .= chr($ch);
        }
        return $str;
    }
}

if (!function_exists("integer_to_bytes")) {
    /**
     * 转换一个int为byte数组
     * @param int $val
     * @return array
     */
    function integer_to_bytes(int $val)
    {
        $byt = array();
        $byt[0] = ($val & 0xff);
        $byt[1] = ($val >> 8 & 0xff);
        $byt[2] = ($val >> 16 & 0xff);
        $byt[3] = ($val >> 24 & 0xff);
        return $byt;
    }
}

if (!function_exists("bytes_to_integer")) {
    /**
     * 从字节数组中指定的位置读取一个integer类型的数据
     * @param int $bytes
     * @param int $position
     * @return int
     */
    function bytes_to_integer(int $bytes, $position)
    {
        $val = $bytes[$position + 3] & 0xff;
        $val <<= 8;
        $val |= $bytes[$position + 2] & 0xff;
        $val <<= 8;
        $val |= $bytes[$position + 1] & 0xff;
        $val <<= 8;
        $val |= $bytes[$position] & 0xff;
        return $val;
    }
}

if (!function_exists("short_to_bytes")) {
    /**
     * short to bytes array
     * @param string $val
     * @return array
     */
    function short_to_bytes(string $val)
    {
        $byt = array();
        $byt[0] = ($val & 0xff);
        $byt[1] = ($val >> 8 & 0xff);
        return $byt;
    }
}

if (!function_exists("bytes_to_short")) {
    /**
     * 从字节数组中指定的位置读取一个short类型的数据。
     * @param int $bytes
     * @param int $position
     * @return int
     */
    function bytes_to_short(int $bytes, $position)
    {
        $val = $bytes[$position + 1] & 0xff;
        $val = $val << 8;
        $val |= $bytes[$position] & 0xff;
        return $val;
    }
}

if (!function_exists("get_cdn_url")) {
    /**
     * get cdn uri
     * @param string $file
     * @return string|string[]|null
     */
    function get_cdn_url(string $file)
    {
        $uri = trim(config("app.cdn_url"), "/") . "/" . ltrim($file, "/");
        if (config("app.env") == "production" && preg_match("/^((?!\.min\.(js|css)).)*$/", $file, $a)) {
            $uri = preg_replace("/\.(js|css)$/", ".min.$1", $uri);
        }
        return $uri;
    }
}

if (!function_exists("luhn")) {
    /**
     * @param string $cardNo
     * @return int
     */
    function luhn(string $cardNo)
    {
        $lastNo = 0;
        for ($i = strlen($cardNo) - 1; $i > 0; $i--) {
            if ($i % 2 == 0) {
                $n = strval($cardNo[$i] * 2);
                $lastNo += $n > 10 ? ($n[0] + $n[1]) : $n;
            } else {
                $lastNo += $cardNo[$i];
            }
        }
        $lastNo = $lastNo % 10;
        return $lastNo ? 10 - $lastNo : $lastNo;
    }
}


if (!function_exists("mq_send")) {
    /**
     * 消息发布
     * @param string|int|array $data
     * @param string $exchange 交换器名字
     * @param string $routingKey 路由
     * @param string $type 交换器类型
     *                      ’’  默认交换机 匿名交换器 未显示声明类型都是该类型
     *                      fanout  扇形交换器 会发送消息到它所知道的所有队列，每个消费者获取的消息都是一致的
     *                      headers 头部交换器
     *                      direct 直连交换器，该交换机将会对绑定键（binding key）和路由键（routing key）进行精确匹配
     *                      topic 话题交换器 该交换机会对路由键正则匹配，必须是*(一个单词)、#(多个单词，以.分割) 、      user.key .abc.* 类型的key
     * @param array $properties
     * @param bool $passive
     * @param bool $durable
     * @param bool $autoDelete
     * @return bool
     */
    function mq_send($data, string $exchange, string $routingKey, string $type, array $properties = null, bool $passive = false, bool $durable = false, bool $autoDelete = false)
    {
        try {
            $connection = new AMQPStreamConnection(config("rabbitmq.host"), config("rabbitmq.port"), config("rabbitmq.user"), config("rabbitmq.password"), config("rabbitmq.vhost"));
            $channel = $connection->channel();
            $channel->exchange_declare($exchange, $type, $passive, $durable, $autoDelete);
            $data = is_array($data) ? json_encode($data) : $data;
            $properties = $properties ?: ['content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT];
            $msg = new AMQPMessage($data, $properties);
            $channel->basic_publish($msg, $exchange, $routingKey);
            $channel->close();
            $connection->close();
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }
}

if (!function_exists("mq_receive")) {
    /**
     * @param string $exchange
     * @param string $queue
     * @param string $routingKey
     * @param $callback
     * @param string $type
     * @param bool $passive
     * @param bool $durable
     * @param bool $autoDelete
     * @return bool
     */
    function mq_receive(string $exchange, string $queue, string $routingKey, $callback, string $type, bool $passive = false, bool $durable = false, bool $autoDelete = false)
    {
        try {
            $connection = new AMQPStreamConnection(config("rabbitmq.host"), config("rabbitmq.port"), config("rabbitmq.user"), config("rabbitmq.password"), config("rabbitmq.vhost"));
            $channel = $connection->channel();
            $channel->exchange_declare($exchange, $type, $passive, $durable, $autoDelete);
            list($queue,) = $channel->queue_declare($queue, $passive, $durable, true, $autoDelete);
            $channel->queue_bind($queue, $exchange, $routingKey);
            $channel->basic_consume($queue, '', false, false, false, false, $callback);
            while (count($channel->callbacks)) {
                $channel->wait();
            }
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }
}
