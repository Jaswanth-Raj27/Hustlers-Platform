<?php
declare(strict_types=1);
function mysql_connection(bool $serverOnly=false): PDO {
 $local=is_file(__DIR__.'/private/database.php') ? require __DIR__.'/private/database.php' : [];
 $host=getenv('HUSTLERS_DB_HOST') ?: ($local['host']??'127.0.0.1');
 $port=getenv('HUSTLERS_DB_PORT') ?: ($local['port']??'3306');
 $name=getenv('HUSTLERS_DB_NAME') ?: ($local['name']??'job_portal');
 $username=getenv('HUSTLERS_DB_USER') ?: ($local['user']??'hustlers');
 $password=getenv('HUSTLERS_DB_PASSWORD'); if($password===false)$password=$local['password']??'';
 return new PDO("mysql:host=$host;port=$port;".($serverOnly?'':"dbname=$name;").'charset=utf8mb4',$username,$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
}
