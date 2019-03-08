<?php

// 应用公共文件


/**
 * 验证手机号是否正确
 * @param number $mobile
 * 支持的手机号为：
 * 移动：134、135、136、137、138、139、150、151、152、157、158、159、182、183、184、187、188、178(4G)、147(上网卡)；
 *联通：130、131、132、155、156、185、186、176(4G)、145(上网卡)；
 *电信：133、153、180、181、189 、177(4G)；
 *卫星通信：1349
 *虚拟运营商：170
 */
function isMobile($mobile) {
    if (!is_numeric($mobile)) {
        return false;
    }
    return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^16[6]{1}\d{8}$|^17[0,6,7,8,3]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
}

/**
 * 判断是否是正确的邮箱格式;
 * @param $email
 * @return bool
 */
function isEmail($email){
    $mode = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
    if (preg_match($mode, $email)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 登录密码加密
 * @param $password
 * @return string
 */
function password($password){
    return sha1($password);
}

/**
 * 检查词语是否合法
 * @param $word
 * @return mixed
 */
function check_word($word){
    return $word;
}

/**
 * 判断是否为合法的身份证号码
 * @param $shenfenzheng
 * @return int
 */
function isShenfenzheng($vStr){
    $vCity = array(
        '11','12','13','14','15','21','22',
        '23','31','32','33','34','35','36',
        '37','41','42','43','44','45','46',
        '50','51','52','53','54','61','62',
        '63','64','65','71','81','82','91'
    );
    if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $vStr)) return false;
    if (!in_array(substr($vStr, 0, 2), $vCity)) return false;
    $vStr = preg_replace('/[xX]$/i', 'a', $vStr);
    $vLength = strlen($vStr);
    if ($vLength == 18) {
        $vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
    } else {
        $vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
    }
    if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) return false;
    if ($vLength == 18) {
        $vSum = 0;
        for ($i = 17 ; $i >= 0 ; $i--) {
            $vSubStr = substr($vStr, 17 - $i, 1);
            $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr , 11));
        }
        if($vSum % 11 != 1) return false;
    }
    return true;
}