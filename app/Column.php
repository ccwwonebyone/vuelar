<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Column extends Model
{
	//数据表
    protected $table = 'column';
    //设置无时间戳字段
    public $timestamps = false;


}
