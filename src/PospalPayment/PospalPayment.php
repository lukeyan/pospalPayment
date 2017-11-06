<?php

namespace PospalPayment;

class PospalPayment {
    protected $appId;
    protected $appKey;
    protected $urlPreFix;

    protected $getMembersUrl="pospal-api2/openapi/v1/customerOpenApi/queryCustomerPages";
    protected $createMemberUrl = "pospal-api2/openapi/v1/customerOpenApi/add";
    protected $getMemberUrl="pospal-api2/openapi/v1/customerOpenApi/queryByNumber"; //获取单个用户资料
    protected $updateMemberProfileUrl = "pospal-api2/openapi/v1/customerOpenApi/updateBaseInfo";//修改单个用户资料
    protected $memberChargeUrl = "pospal-api2/openapi/v1/customerOpenApi/updateBalancePointByIncrement"; //充值
    protected $memberDischargeUrl = "pospal-api2/openapi/v1/customerOpenApi/updateBalancePointByIncrement"; //扣款


    protected $members=[];

    public function __construct($appId,$appKey,$urlPreFix) {
        $this->appId = $appId;
        $this->appKey = $appKey;
        $this->urlPreFix = $urlPreFix;
    }

//  //test return
//  public function test()
//  {
//      echo "test";
//  }

    /**
     * 获取所有的系统会员
     *
     */
    public function members($postBackParameter=[])
    {
        $http = $this->urlPreFix.$this->getMembersUrl;

        if(empty($postBackParameter))
        {
            $arr = [
                'appId'=>$this->appId
            ];
        }
        else
        {
            $arr = [
                'appId'=>$this->appId,
                'postBackParameter'=>[
                    'parameterType'=>$postBackParameter['parameterType'],
                    'parameterValue'=>$postBackParameter['parameterValue']
                ]
            ];
        }


        $jsondata = json_encode($arr);

        $signature = $this->get_signature($this->appKey,$jsondata);

        $result = $this->https_request($http,$jsondata,$signature);

        $obj = json_decode($result,true,512,JSON_BIGINT_AS_STRING);

        foreach ($obj['data']['result'] as $row)
        {
            array_push($this->members,$row);
        }

        if(count($obj['data']['result']) < $obj['data']['pageSize'])
        {

        }
        else
        {
            $this->members($obj['data']['postBackParameter']);
        }

        return $this->members;


    }

    /**
     * 获取单个会员信息
     */

    public function member($customerNum)
    {
        $http = $this->urlPreFix.$this->getMemberUrl;

        $arr = [
            "appId"=> $this->appId, //Pospal配置的访问凭证
            "customerNum"=>$customerNum
        ];

        $jsondata = json_encode($arr);

        $signature = $this->get_signature($this->appKey,$jsondata);

        $result = $this->https_request($http,$jsondata,$signature);

        $obj = json_decode($result,true);

        return $obj;
    }

    /**
     * 新建单个会员
     */
    public function member_create($number,$name,$phone,$password,$balance=0,$point=0,$discount=100)
    {

        $http = $this->urlPreFix.$this->createMemberUrl;

        $arr = [
            "appId"=> $this->appId, //Pospal配置的访问凭证
            "customerInfo"=>[
                "categoryName"=>"",
                "number"=>$number,
                "name"=> $name,
                "point"=>$point,
                "discount"=>$discount,
                "balance"=>$balance,
                "phone"=> $phone,
                "birthday"=> "",
                "qq"=> "",
                "email"=> "",
                "address"=> "",
                "remarks"=> "",
                "onAccount"=>0,
                "enable"=>1,
                "password"=> $password
            ]

        ];

        $jsondata = json_encode($arr);

        $signature = $this->get_signature($this->appKey,$jsondata);

        $result = $this->https_request($http,$jsondata,$signature);

        $obj = json_decode($result,true);

        return $obj;


    }

    /**
     *
     * 更新会员个人信息
     *
     */
    public function update_member_profile($customerUid,$enable,$phone)
    {


        $http = $this->urlPreFix.$this->updateMemberProfileUrl;

        $arr = [
            "appId"=> $this->appId, //Pospal配置的访问凭证
            "customerInfo"=>[
                "customerUid"=>$customerUid,
                "enable"=>$enable,
                "phone"=>$phone
            ]
        ];

        $jsondata = json_encode($arr);


        $signature = $this->get_signature($this->appKey,$jsondata);





//        echo "拼接的数据：\n";
//        print_r($jsondata);
//        echo "\n 加密后：\n";
//        echo $signature;
//
//        echo "\n 提交地址：\n";
//
//        echo $http;
//
//        echo "\n 提交后：\n";

        $result = $this->https_request($http,$jsondata,$signature);

        $obj = json_decode($result,true);

        return $obj;
    }


    /**
     * 充值+余额
     *
     * {
    "appId": "abcdefghijklmn",
    "customerUid":231607639459718452,
    "balanceIncrement":100,
    "pointIncrement": 100,
    "dataChangeTime": "2015-12-16 13:26:23"
    }
     *
     */
    public function member_charge($customerUid,$amount,$point)
    {
        $http = $this->urlPreFix.$this->memberChargeUrl;

        $arr = [
            "appId"=> $this->appId, //Pospal配置的访问凭证
            "customerUid"=>(int)$customerUid,
            "balanceIncrement"=>$amount,
            "pointIncrement"=>$point,
            "dataChangeTime"=>date("Y-m-d H:i:s")

        ];

        $jsondata = json_encode($arr);


        $signature = $this->get_signature($this->appKey,$jsondata);

        $result = $this->https_request($http,$jsondata,$signature);

        $obj = json_decode($result,true);

        return $obj;
    }

    /**
     *
     * 扣费 -余额
     *
     */
    public function  member_discharge($customerUid,$amount,$point)
    {

        $http = $this->urlPreFix.$this->memberChargeUrl;

        $arr = [
            "appId"=> $this->appId, //Pospal配置的访问凭证
            "customerUid"=>(int)$customerUid,
            "balanceIncrement"=>$amount*(-1),
            "pointIncrement"=>$point,
            "dataChangeTime"=>date("Y-m-d H:i:s")

        ];

        $jsondata = json_encode($arr);


        $signature = $this->get_signature($this->appKey,$jsondata);

        $result = $this->https_request($http,$jsondata,$signature);

        $obj = json_decode($result,true);

        return $obj;
    }

    /**
     * 签名
     */
    public function get_signature($appKey,$jsondata)
    {
        return $signature = strtoupper(md5($appKey.$jsondata));
    }

    /**
     * @param $url
     * @param $data
     * @param $signature
     * @return mixed
     *
     * 请求网络
     */

    public function https_request($url, $data,$signature)
    {
        $time = time();
        $curl = curl_init();// 启动一个CURL会话
        // 设置HTTP头
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "User-Agent: openApi",
            "Content-Type: application/json; charset=utf-8",
            "accept-encoding: gzip,deflate",
            "time-stamp: ".$time,
            "data-signature: ".$signature
        ));
        curl_setopt($curl, CURLOPT_URL, $url);         // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);      // Post提交的数据包

        curl_setopt($curl, CURLOPT_POST, 1);        // 发送一个常规的Post请求

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 获取的信息以文件流的形式返回
        $output = curl_exec($curl); // 执行操作

        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话

        return $output; // 返回数据
    }
}