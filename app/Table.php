<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    //数据表
    protected $table = 'table';
    //设置无时间戳字段
    public $timestamps = false;
}
