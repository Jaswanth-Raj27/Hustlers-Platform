<?php
// Development server: php -S 127.0.0.1:8085 router.php
$path=rawurldecode(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)??'/');
$routes=['index.php','frontpage.php','jlogin.php','jsignup.php','elogin.php','esignup.php','jsearch.php','job.php','dashboard.php','eprofile.php','jprofile.php','epage.php','esearch.php','profile.php','candidates.php','company.php','resume.php'];
if($path==='/'){$path='/index.php';}
if(str_contains($path,'..')){http_response_code(404);exit;}
if(preg_match('~^/assets/[a-z-]+\.(css|js|svg)$~',$path) && is_file(__DIR__.$path)){return false;}
if(in_array(ltrim($path,'/'),$routes,true)){$_SERVER['SCRIPT_NAME']=$path;require __DIR__.'/app.php';return true;}
http_response_code(404);echo 'Page not found.';
