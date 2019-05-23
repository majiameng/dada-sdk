<?php

namespace common\service;
use \tinymeng\Dada\DadaSdk;
/**
 * Class 达达逻辑处理
 * Author: Tinymeng <666@majiameng.com>
 * @package app\common\service
 */
class Dada
{
    public $obj;
    public $config;

    /**
     * Dada 初始化.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->config =  config('params.dada');
        $this->obj = new DadaSdk($this->config);
    }

    /**
     * Name: 查询订单运费接口
     * Author: Tinymeng <666@majiameng.com>
     * @param $info
     * @return mixed
     * @throws \Exception
     */
    public function queryDeliverFee($info){
        $data = [
            'origin_id' => self::createOrderSn(),//生成达达随机订单号
            'city_code' => "010", // 北京写死了 如果需要写活 调用 cityCode
            'cargo_price' => 0, //订单金额
            'is_prepay' => 0, //是否需要垫付
            'receiver_name' => $info['name'], //收货人姓名
            'receiver_address' => $info['address'], //	收货人地址
            'receiver_phone' => $info['mobile'], //收货人手机号
            'callback' => $this->config['callback'], //回调地址
            'receiver_lat' => $this->config['lat'],
            'receiver_lng' => $this->config['lon'],
        ];
        $result = $this->obj->queryDeliverFee($data);
        return $result;
    }

    /**
     * Name: 随机生成达达订单号
     * Author: Tinymeng <666@majiameng.com>
     * @return string
     */
    public static function createOrderSn()
    {
        list($usec, $sec) = explode(" ", microtime());
        $usec = substr(str_replace('0.', '', $usec), 0, 4);
        $str = rand(10, 99);
        return date("YmdHis") . $usec . $str;
    }

    /**
     * Name: 创建达达订单
     * Author: Tinymeng <666@majiameng.com>
     * @param $order
     * @return bool
     */
    public function addOrder($order){
        $data['origin_id'] = $order['order_id'];
        $data['city_code'] = "010"; // 北京写死了 如果需要写活 调用 cityCode
        $data['cargo_price'] = $order['order_amount'];
        $data['is_prepay'] = "0";
        $data['expected_fetch_time'] = time()+3600;
        $data['receiver_name'] = $order['ship_name'];
        $data['receiver_address'] = $order['ship_address'];
        $data['receiver_phone'] = $order['ship_mobile'];
        $data['receiver_lat'] = $order['ship_lat'];
        $data['receiver_lng'] = $order['ship_lon'];
        $data['callback'] = $this->config['callback'];

        $result = $this->obj->addOrder($data);
        return $result;
    }

    /**
     * Name: statusQuery
     * Author: Tinymeng <666@majiameng.com>
     * @param $order_id
     * @return mixed
     */
    public function statusQuery($order_id){
        $data = [
            'order_id' => $order_id
        ];
        $result = $this->obj->statusQuery($data);
        return $result;
    }

    /**
     * Name: 达达回调逻辑
     * Author: Tinymeng <666@majiameng.com>
     * @param $response
     * @return bool|string
     */
    public function callBack($response)
    {
        Db::startTrans();
        try{
            $response = json_decode($response,true);
            //添加达达callback 日志

            //添加配送详情
            $params = [
                'logi_no'=>$response['order_id'],
                'state'=>$response['order_status'],
                'context'=>$this->statusGetContext($response),
                'time'=>$response['update_time'],
            ];
            $detail = new BillDeliveryDetail();
            $detail->save($params);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }

    /**
     * Name: 通过达达状态获取配送描述
     * Author: Tinymeng <666@majiameng.com>
     * @param $response
     * @return string
     */
    public function statusGetContext($response){
        /**
         * 达达订单状态:
         * 待接单＝1,待取货＝2,配送中＝3,已完成＝4,已取消＝5, 已过期＝7,指派单=8,
         * 妥投异常之物品返回中=9, 妥投异常之物品返回完成=10,骑士到店=100,创建达达运单失败=1000
         */
        switch ($response['order_status']){
            case 1:
                $context = '已发送达达订单,骑手待接单!';
                break;
            case 2:
                $context = '骑手"'.$response['dm_name'].'"已接单,联系方式"'.$response['dm_mobile'].'",骑手待取货!';
                break;
            case 3:
                $context = '骑手正在努力配送中!';
                break;
            case 4:
                $context = '配送已完成!';
                break;
            case 5:
                $context = '订单已取消,取消原因:"'.$response['cancel_reason'].'"!';
                break;
            case 7:
                $context = '订单已过期!';
                break;
            case 8:
                $context = '指派单!';
                break;
            case 9:
                $context = '妥投异常之物品返回中!';
                break;
            case 10:
                $context = '妥投异常之物品返回完成!';
                break;
            case 100:
                $context = '骑士已到店取货!';
                break;
            case 1000:
                $context = '创建达达运单失败,请联系管理员!';
                break;
            default:
                $context = '配送状态异常 : order_status='.$response['order_status'].',请联系管理员!';
        }
        return $context;
    }

}