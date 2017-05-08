<?php
/**
 * 环境要求
 */
return [
	['type'=>'function','require'=>'openssl_open','result'=>false],
	['type'=>'version','require'=>'5.6.0','result'=>false],
	['type'=>'function','require'=>'socket_connect','result'=>false]
];