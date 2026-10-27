<?php
declare(strict_types=1);
ini_set('session.use_strict_mode', '1');
session_set_cookie_params(['httponly'=>true,'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off','samesite'=>'Lax']);
session_name('hustlers_four_tables');
session_start();
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self'; script-src 'self'; img-src 'self' data:; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function go(string $url): void { header('Location: '.$url); exit; }
function db(): PDO {
 static $db;
 if (!$db) {
  require_once __DIR__.'/database.php';
  $db=mysql_connection();
 }
 return $db;
}
function query(string $sql,array $params=[]): PDOStatement { $s=db()->prepare($sql); $s->execute($params); return $s; }
function user() {
 if(!isset($_SESSION['uid'],$_SESSION['role']))return null;
 if($_SESSION['role']==='employer')return query('SELECT *,NULL AS field,NULL AS skills,0 AS discoverable FROM euser WHERE id=?',[$_SESSION['uid']])->fetch();
 if($_SESSION['role']==='seeker')return query("SELECT u.*,COALESCE(p.title,'') AS title,COALESCE(p.bio,'') AS bio,COALESCE(p.location,'') AS location,COALESCE(p.website,'') AS website,p.field,p.skills,p.discoverable,p.age,p.experience_years,p.education,p.work_history FROM juser u LEFT JOIN jprofile p ON p.user_id=u.id WHERE u.id=?",[$_SESSION['uid']])->fetch();
 return null;
}
function require_user(?string $role=null) { $u=user(); if(!$u) go('jlogin.php'); if($role && $u['role']!==$role){http_response_code(403);exit('This action is unavailable for this account.');} return $u; }
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
function csrf(): string { return '<input type="hidden" name="csrf" value="'.h($_SESSION['csrf']).'">'; }
function flash(string $message): void { $_SESSION['flash']=$message; }
function avatar(string $category): string { static $map; $map ??= query('SELECT name,avatar FROM categories')->fetchAll(PDO::FETCH_KEY_PAIR); $file=$map[$category]??'business'; if(!preg_match('/^[a-z-]+$/',$file)||!is_file(__DIR__.'/assets/'.$file.'.svg'))$file='business'; return 'assets/'.$file.'.svg'; }
