<?php
/**
 * 达达开放平台  https://newopen.imdada.cn
 * api接口文档
 *      https://newopen.imdada.cn/#/development/file/index
 */
namespace tinymeng\Dada;

use tinymeng\tools\HttpRequest;

class DadaSdk
{
    private $url = 'http://newopen.imdada.cn';//api域名

    /**
     * 配置参数
     * @var array
     */
    protected $config;

    /**
     * 当前时间戳
     * @var int
     */
    protected $timestamp;

    protected $VERSION = '1.0';

    /**
     * 订单管理
     * Author: TinyMeng <666@majiameng.com>
     * @var string
     */
    protected $API_ADDORDER = '/api/order/addOrder';//新增订单
    protected $API_READDORDER = '/api/order/reAddOrder';//重新发布订单
    protected $API_QUERYDELIVERFEE = '/api/order/queryDeliverFee';//查询订单运费
    protected $API_ADDAFTERQUERY = '/api/order/addAfterQuery';//查询运费后发单
    protected $API_ADDTIP = '/api/order/addTip';//增加小费
    protected $API_STATUS_QUERY = '/api/order/status/query';//订单详情查询
    protected $API_FORMALCANCEL = '/api/order/formalCancel';//取消订单
    protected $API_CANCELREASONS = '/api/order/cancel/reasons';//获取取消原因
//    protected $API_APPOINT_EXIST = '/api/order/appoint/exist';//追加订单(暫未使用)
//    protected $API_APPOINT_CANCEL = '/api/order/appoint/cancel';//取消追加订单(暫未使用)
//    protected $API_APPOINT_LIST_TRANSPORTER = '/api/order/appoint/list/transporter';//查询追加配送员(暫未使用)
//    protected $API_COMPLAINT_DADA = '/api/complaint/dada';//商家投诉达达(暫未使用)
//    protected $API_COMPLAINT_REASONS = '/api/complaint/reasons';//获取商家投诉达达原因(暫未使用)
//    protected $API_CONFIRM_GOODS = '/api/order/confirm/goods';//妥投异常之物品返回完成(暫未使用)

    /**
     * 商户管理
     */
    protected $API_CITY_LIST = "/api/cityCode/list";//获取城市信息
    protected $API_MERCHANT_ADD = '/merchantApi/merchant/add';//注册商户
    protected $API_SHOP_ADD = '/api/shop/add';//新增门店
    protected $API_SHOP_UPDATE = '/api/shop/update';//新增门店
    protected $API_SHOP_DETAIL = '/api/shop/detail';//门店详情

    /**
     * 仅在测试环境供调试使用(模拟回调)
     * Author: TinyMeng <666@majiameng.com>
     * @var string
     */
    protected $API_ACCEPTORDER = '/api/order/accept';//接受订单
    protected $API_FETCHORDER = '/api/order/fetch';//完成取货
    protected $API_FINISHORDER = '/api/order/finish';//完成订单
    protected $API_CANCELORDER = '/api/order/cancel';//取消订单
    protected $API_EXPIREORDER = '/api/order/expire';//订单过期
    protected $API_ABNORMAL_BACK = '/api/order/delivery/abnormal/back';//异常妥投物品返还中

    /**
     * DadaSdk 初始化.
     * @param null $config
     * @throws \Exception
     */
    public function __construct($config = null)
    {
        if (!$config) {
            throw new \Exception('传入的配置不能为空');
        }
        /** @var array $_config 默认参数 */
        $_config = [
            'app_key'    => '',
            'app_secret' => '',
            'source_id'  => '',
            'shop_no'    => '',
            'is_sandbox' => false,//是否是沙箱环境
        ];
        $this->config    = array_merge($_config, $config);
        $this->timestamp = time();

        if($this->config['is_sandbox'] == true){
            $this->setSandbox();
        }
    }

    /**
     * Name: 设置沙箱环境
     * Author: Tinymeng <666@majiameng.com>
     * @return $this
     */
    public function setSandbox(){
        $this->url = 'http://newopen.qa.imdada.cn';//测试环境
        //在测试环境，使用官方统一商户和门店进行发单。其中，商户id：73753，门店编号：11047059。
        $this->config['source_id'] = '73753';
        $this->config['shop_no'] = '11047059';
        return $this;
    }

    /**
     * Name: 设置门店编号
     * Author: Tinymeng <666@majiameng.com>
     * @param $shop_no
     * @return $this
     */
    public function setShopNo($shop_no){
        $this->config['shop_no'] = $shop_no;
        return $this;
    }

    /** 新增订单
     * @return bool
     */
    public function addOrder($data)
    {
        if(!isset($data['shop_no'])){
            $data['shop_no'] = $this->config['shop_no'];
        }
        return self::getResult($this->API_ADDORDER,$data);
    }

    /***
     * 订单详情查询
     * @param $data
     * @return mixed
     */
    public function statusQuery($data){
        return self::getResult($this->API_STATUS_QUERY,$data);

    }
    /**
     * 重新发布订单
     * 在调用新增订单后，订单被取消、过期或者投递异常的情况下，调用此接口，可以在达达平台重新发布订单。
     * @return bool
     */
    public function reAddOrder($data)
    {
        if(!isset($data['shop_no'])){
            $data['shop_no'] = $this->config['shop_no'];
        }
//        $data['origin_id'] = "12321";
//        $data['city_code'] = "029";
//        $data['cargo_price'] = "11.2";
//        $data['is_prepay'] = "0";
//        $data['expected_fetch_time'] = time()+3600;
//        $data['receiver_name'] = "仝帅";
//        $data['receiver_address'] = "南稍门中贸广场";
//        $data['receiver_phone'] = "13572420121";
//        $data['receiver_lat'] = "108.952931";
//        $data['receiver_lng'] = "34.248759";
//        $data['callback'] = "http://www.weilai517.com/index.php/Home/Test/callback/id/12321";
        return self::getResult($this->API_READDORDER,$data);
    }

    /**
     * Name: 查询订单运费接口
     * Author: Tinymeng <666@majiameng.com>
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function queryDeliverFee($data)
    {
        if(!isset($data['shop_no'])){
            $data['shop_no'] = $this->config['shop_no'];
        }
        return self::getResult($this->API_QUERYDELIVERFEE,$data);
    }

    /**
     * Name: 查询运费后发单接口
     * Author: Tinymeng <666@majiameng.com>
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function addAfterQuery($data)
    {
//        $data['deliveryNo'] = '';
        return self::getResult($this->API_ADDAFTERQUERY,$data);
    }

    /**
     * 取消订单(线上环境)
     * 在订单待接单或待取货情况下，调用此接口可取消订单。注意：订单接单后1-15分钟取消订单，会扣除相应费用补贴给接单达达
     * @return bool
     */
    public function formalCancel($data)
    {
//        $data['order_id'] = '12321';
//        $data['cancel_reason_id'] = '1';
//        $data['cancel_reason'] = "";
        return self::getResult($this->API_FORMALCANCEL,$data);
    }
    /**
     * 增加小费
     * 可以对待接单状态的订单增加小费。需要注意：订单的小费，以最新一次加小费动作的金额为准，故下一次增加小费额必须大于上一次小费额。
     * @return bool
     */
    public function addTip($data)
    {
//        $data['order_id'] = '12321';
//        $data['tips'] = '2.5';
//        $data['city_code'] = '029';
//        $data['info'] = '';
        return self::getResult($this->API_ADDTIP,$data);
    }
    /**
     * 新增门店
     * @return bool
     */
    public function addShop($data)
    {
//        $data['origin_shop_id'] = '';
//        $data['station_name'] = '';
//        $data['business'] = '';
//        $data['city_name'] = '';
//        $data['area_name'] = '';
//        $data['station_address'] = '';
//        $data['lng'] = '';
//        $data['lat'] = '';
//        $data['contact_name'] = '';
//        $data['phone'] = '';
//        $data['username'] = '';
//        $data['password'] = '';
        return self::getResult($this->API_SHOP_ADD,$data);
    }
    public function addMerchant($data)
    {
//        $data['mobile'] = '';
//        $data['city_name'] = '';
//        $data['enterprise_name'] = '';
//        $data['enterprise_address'] = '';
//        $data['contact_name'] = '';
//        $data['contact_phone'] = '';
//        $this->SOURCE_ID = '';
        return self::getResult($this->API_MERCHANT_ADD,$data);
    }
    /**
     * 获取取消订单原因列表
     * array {0 =>array{'reason' =>'没有达达接单','id' =>1},....}
     */
    public function cancelReasons()
    {
        $res = self::getResult($this->API_CANCELREASONS);
        return $res;
    }
    /**
     * 接单(仅在测试环境供调试使用)
     * @return bool
     */
    public function acceptOrder($data)
    {
//        $data['order_id'] = '12321';
        return self::getResult($this->API_ACCEPTORDER,$data);
    }
    /**
     * 完成取货(仅在测试环境供调试使用)
     * @return bool
     */
    public function fetchOrder($data)
    {
//        $data['order_id'] = '12321';
        return self::getResult($this->API_FETCHORDER,$data);
    }
    /**
     * 完成订单(仅在测试环境供调试使用)
     * @return bool
     */
    public function finishOrder($data)
    {
//        $data['order_id'] = '12321';
        return self::getResult($this->API_FINISHORDER,$data);
    }
    /**
     * 取消订单(仅在测试环境供调试使用)
     * @return bool
     */
    public function cancelOrder($data)
    {
//        $data['order_id'] = '12321';
        return self::getResult($this->API_CANCELORDER,$data);
    }
    /**
     * 订单过期(仅在测试环境供调试使用)
     * @return bool
     */
    public function expireOrder($data)
    {
//        $data['order_id'] = '12321';
        return self::getResult($this->API_EXPIREORDER,$data);
    }
    /**
     * 订单状态变化后，达达回调我们
     */
    public function processCallback()
    {
        $content = file_get_contents("php://input");
        //{"order_status":2,"cancel_reason":"","update_time":1482220973,"dm_id":666,"signature":"7a177ae4b1cf63d13261580e4f721cb9","dm_name":"测试达达","order_id":"12321","client_id":"","dm_mobile":"13546670420"}
        if($content){
            $arr = json_decode($content,true);
        }
    }
    /** 获取城市信息
     * @return bool
     */
    public function cityCode(){
        return self::getResult($this->API_CITY_LIST);
    }

    /**
     * Name: 生成sign签名
     * Author: Tinymeng <666@majiameng.com>
     * @param $param
     * @return string
     */
    private function getSign($param)
    {
        //1.升序排序
//        ksort($param);

        //2.字符串拼接
        $str = '';
        foreach ($param as $k=>$v){
            $str .= $k.$v;
        }
        $str = $this->config['app_secret'].$str.$this->config['app_secret'];
        
        //3.MD5签名,转为大写
        return strtoupper(md5($str));
    }

    /**
     * Name: 获取参数
     * Author: Tinymeng <666@majiameng.com>
     * @param string $data
     * @return false|string
     */
    private function getParam($data='')
    {
        if(empty($data)){
            $data = '';
        }else{
            $data = json_encode($data);
        }
        $params = array(
            "app_key"=>$this->config['app_key'],
            "body"=>$data,
            "format"=>"json",
            "source_id"=>$this->config['source_id'],
            "timestamp"=>$this->timestamp,
            "v"=>$this->VERSION,
        );
        if(empty($this->config['source_id'])){
            unset($params['source_id']);
        }
        $sign = self::getSign($params);
        $params['signature'] = $sign;
        return json_encode($params);
    }

    /**
     * @param 获取请求数据
     * @param string $data
     * Author: TinyMeng <666@majiameng.com>
     * @return mixed
     * @throws \Exception
     */
    private function getResult($api,$data=''){
        $param = self::getParam($data);
        $httpHeaders = [
            'Content-Type: application/json',
        ];
        try{
            $result = HttpRequest::httpPost($this->url.$api,$param,$httpHeaders);
            if($result){
                $result = json_decode($result,true);
                return $result;
//            if($result['status'] == 'success'){
//                return $result['result'];
//            }
//            throw new \Exception("哒哒请求响应错误,返回数据：" . var_export($result));
            }
        }catch (\Exception $exception){
            throw new \Exception("哒哒请求出错!");
        }
    }
}