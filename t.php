<?php
/**
 * vim:ft=php et ts=4 sts=4
 * @author z14 <z@arcz.ee>
 * @version
 * @todo
 */


$json = '{"code":0,"data":{"pageNum":0,"pageSize":10,"pages":1,"total":2,"list":[{"id":729,"createDate":"2022-04-22 17:27:48","deviceId":20792,"fingerUserId":"1","fingerUsername":"u1","fingerAddr":0,"isDeleted":false,"updateDate":"2022-04-22 17:27:48"},{"id":727,"createDate":"2022-04-21 15:35:45","deviceId":20792,"fingerUserId":"55","fingerUsername":"u2","fingerAddr":0,"isDeleted":false,"updateDate":"2022-04-21 15:35:45"}],"nextPage":1}}';


$o = json_decode($json, true);
// print_r($o['data']['total']);
print_r($o['data']['list']);
// print_r($o->data->list);
//
// print_r(array_search("u2", $o['data']['list']));
//
print_r(array_search("u2", array_column($o['data']['list'], 'fingerUsername')));
