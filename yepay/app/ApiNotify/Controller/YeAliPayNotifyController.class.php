<?php
/**
 * Created by PhpStorm.
 * User: xiaoye
 * Email: 415907483@qq.com
 * Date: 2020/9/25
 * Time: 20:32
 */

namespace ApiNotify\Controller;


class YeAliPayNotifyController extends  PaymentNotifyController
{

    private $payConfig=[];
    private $ojb;

    function _initialize()
    {
        parent::_initialize();
        $className = substr(__CLASS__, strrpos(__CLASS__,'\\') + 1 , -16);
        $class = new \ReflectionClass("Niaoyun\Payment\\$className");
        $this->ojb =$class->newInstance();
        $this->payConfig = $this->ojb->getConfig();
    }
    /**
     * 验证签名
     */
    public function checkSign($parameters)
    {
        if($parameters['sign'] == $this->ojb->sign($parameters,$this->payConfig['appkey'])){
            return true;
        }
        return false;
    }

    /**
     * 回调
     * 妈的不知道叼 鸟怎么写的,文档也不清不楚，这里怎么返回回调都不会成功！
     */
    public function getPayStatus($parameters)
    {
        $this->f($parameters);
    }

    private function f($parameters) {
        $out_trade_no = $parameters['out_trade_no'];
        $users_recharge =  M('users_recharge')->where(['orderNo' => $out_trade_no])->find();
        $userID =  $users_recharge['userid'];
//        $type = $users_recharge['type'];
        $type = 'alipay';//用alipay 前端会显示 支付宝

        $parameter = array(
            'userID' => $userID,
            'type' => $type,
            'orderNo' => $out_trade_no,
            'trade_no' => $parameters['trade_no'],
            'total_fee' => $parameters['total_amount'],
            'buyer_email' => $parameters['buyer_logon_id'],
        );
        M()->startTrans();
        if (!checkOrderStatus($out_trade_no)) {
            $orderHandleResult = orderHandle($parameter);
            if ($orderHandleResult) {
                M()->commit();
                exit('success');
            }
        }
        M()->rollback();
        exit('FAILED');
    }
}