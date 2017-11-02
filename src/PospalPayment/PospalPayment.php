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

	public function __construct($appId,$appKey,$urlPreFix) {
        $this->appId = $appId;
        $this->appKey = $appKey;
        $this->urlPreFix = $urlPreFix;
	}

//	//test return
//	public function test()
//	{
//		echo "test";
//	}

    /**
     * 获取所有的系统会员
     *
     */
    public function members()
    {
        $http = $this->urlPreFix.$this->getMembersUrl;

        $arr = [
                'appId'=>$this->appId
        ];

        $jsondata = json_encode($arr);

        $signature = $this->get_signature($this->appKey,$jsondata);

        $result = $this->https_request($http,$jsondata,$signature);

        $obj = json_decode($result,true);

        return $obj;


    }

    /**
     * 获取单个会员信息
     */

    public function member($customerNum)
    {
        $http = $this->urlPreFix.$this->getMemberUrl;

        $arr = [
            "appId"=> $this->appId,	//Pospal配置的访问凭证
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
    public function member_create($number,$phone,$password)
    {

        $http = $this->urlPreFix.$this->createMemberUrl;

        $arr = [
            "appId"=> $this->appId,	//Pospal配置的访问凭证
            "customerInfo"=>[
                "categoryName"=>"",
                "number"=>$number,
                "name"=> "API创建",
                "point"=>0,
                "discount"=>0,
                "balance"=>0,
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
    public function update_member_profile($customerUid,$phone)
    {


        $http = $this->urlPreFix.$this->updateMemberProfileUrl;

        $arr = [
            "appId"=> $this->appId,	//Pospal配置的访问凭证
            "customerInfo"=>[
                "customerUid"=>$customerUid,
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
//        $http = $this->urlPreFix.$this->memberChargeUrl;

//        $http= "https://area13-win.pospal.cn:443/pospal-api2/openapi/v1/customerOpenApi/updateBalancePointByIncrement";
//        $appKey = '42877429933036127';
//        $appId =  '24415F0CC01D186B32992572C7B72311';
//        $arr = [
//            "appId"=> $appId,	//Pospal配置的访问凭证
//            "customerUid"=>1145711311356864400,
//            "balanceIncrement"=>1,
//            "pointIncrement"=>1,
//            "dataChangeTime"=>date("Y-m-d H:i:s")
//        ];
//
//        $jsondata = json_encode($arr);
//
//        $signature = strtoupper(md5($appKey.$jsondata));
//        $row = $this->https_request($http,$jsondata,$signature);
//        $obj = json_decode($row,true);
//
//        return $obj;



        



//        $arr = [
//            "appId"=> $this->appId,	//Pospal配置的访问凭证
//            "customerUid"=>(int)$customerUid,
//            "balanceIncrement"=>$amount,
//            "pointIncrement"=>$point,
//            "dataChangeTime"=>date("Y-m-d H:i:s")
//
//        ];
//
//        $jsondata = json_encode($arr);
//
//
//        $signature = $this->get_signature($this->appKey,$jsondata);
//
//        $result = $this->https_request($http,$jsondata,$signature);
//
//        $obj = json_decode($result,true);
//
//        return $obj;
    }

    /**
     *
     * 扣费 -余额
     *
     */
    public function member_discharge($customerUid,$amount)
    {

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
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);		// Post提交的数据包

        curl_setopt($curl, CURLOPT_POST, 1);		// 发送一个常规的Post请求

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 获取的信息以文件流的形式返回
        $output = curl_exec($curl); // 执行操作

        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话

        return $output; // 返回数据
    }
}