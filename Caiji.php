<?php
/*
 * 采集类
 */
class CaiJi{
    private $reg;
    private $url;
    private $ip;
    private $refer;
    private $cookiefile;
    private $agent;
    private $post;
    private $header;
    private $time;
    private $ssl;
     
    public function __construct($url){
        $this->url = $url;
        $this->ip = '';
        $this->refer = '';
        $this->cookiefile = '';
        $this->agent = '';
        $this->post = array();
        $this->header = array();
        $this->time = 0;
        $this->ssl = false;
    }
     
    /**
     * 设置方法
     */
    public function setIp($ip){
        $this->ip = $ip;
    }
     
    public function setRefer($refer){
        $this->refer = $refer;
    }
     
    public function setCookieFile($cookiefile){
        $this->cookiefile  = $cookiefile;
    }
     
    public function setAgent($agent){
        $this->agent = $agent;
    }
     
    public function setPost($post){
        $this->post = $post;
    }
     
    public function setHeader($header){
        $this->header = $header;
    }
     
    public function setTime($time){
        $this->time = $time;
    }
    
    public function setSsl($ssl){
        $this->ssl = $ssl;
    }
 
    /**
     * 获取网址内容
     * @param $cookiefile cookie文件存放目录
     * @param $agent 浏览器标识
     * @param $post 数组格式 POST数据
     * @param $time 超时时间
     * @param $ip 模拟ip
     * @param $refer 模拟来源
     * @param $header 模拟请求
     */
    public function getRes(){
        //使用curl获取内容
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (!empty($this->time)){
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->time);
        }
        //设置浏览器
        if (!empty($this->agent)){
            curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);
        }
        //设置cookie
        if (!empty($this->cookiefile)){
            if (!file_exists($this->cookiefile)){
                file_put_contents($this->cookiefile, '');//创建cookie文件
            }
            $cookiefile = realpath($this->cookiefile);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
        }
        //post发送
        if (!empty($this->post)){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->post);
        }
        if (!empty($this->header)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
        }
        //伪造ip
        if (!empty($this->ip)){
            if (!empty($this->header)){
                $this->header[] = 'X-FORWARDED-FOR:'.$this->ip;
                $this->header[] = 'CLIENT-IP:'.$this->ip;
            } else {
                $this->header = array(
                    'X-FORWARDED-FOR:'.$this->ip,
                    'CLIENT-IP:'.$this->ip
                );
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
        }
        //伪造来源
        if (!empty($this->refer)){
            curl_setopt($ch, CURLOPT_REFERER, $this->refer);
        }
        
        if ($this->ssl){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
 
    /**
     * 获取网址内容2
     */
    public function getRes2(){
        $res = file_get_contents($this->url);
        if ($res !== false){
            return $res;
        }
        return '';
    }
     
    /**
     * 下载文件
     * @param $dir  下载文件存放的目录
     * @param $file 下载文件存放的文件名
     */
    public function down($dir='',$file=''){
        if (empty($dir)){
            $dir = $this->getDir(1);
        }
        if (empty($file)){
            $file = $this->getDir(2);
        }
        if (file_exists($dir.'/'.$file)){
            return true;
        }
        if (!is_dir($dir)){
            mkdir($dir,0777,true);
        }
        $dir = rtrim($dir,"/");
        $fp = fopen($dir.'/'.$file, 'w');
        fwrite($fp, $this->getRes());
        fclose($fp);
        return $dir.'/'.$file;
    }
     
    /**
     * 获取url的目录文件结构
     * @param $all 0 获取目录+文件  1 获取目录  2 获取文件名+后缀  3  获取后缀  4获取文件名
     */
    public function getDir($all = 0){
        $parse = parse_url($this->url);
        if ($all == 0){
            return $parse['path'];
        }
        $path = pathinfo($parse['path']);
        if ($all == 1){
            return $path['dirname'];
        }
        if ($all == 2){
            return $path['basename'];
        }
        if ($all == 3){
            return $path['extension'];
        }
        if ($all == 4){
            return $path['filename'];
        }
        return '';
    }
     
    /**
     * 获取请求网址状态
     * @return true 可以访问   false 不可访问
     */
    public function getState(){
        $state = get_headers($this->url);
        if (isset($state[0]) && $state[0] == 'HTTP/1.1 200 OK'){
            return true;
        }
        return false;
    }
     
    /**
     * 获取正则表达式匹配结果
     * @param $reg 正则表达式
     * @param $checked 获取匹配的第几个值
     */
    public function getOne($reg,$checked=0){
        if (preg_match($reg, $this->getRes(),$mat)){
            if (isset($mat[$checked])){
                return $mat[$checked];
            }
        }
        return false;
    }
     
    /**
     * 获取正则表达式匹配的结果
     */
    public function getAll($reg,$checked=0){
        if (preg_match_all($reg, $this->getRes(),$mat)){
            if (isset($mat[$checked])){
                return $mat[$checked];
            }
        }
        return false;
    }
}