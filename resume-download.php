<?php
$u=require_user();$resume=query('SELECT * FROM resumes WHERE id=?',[(int)($_GET['id']??0)])->fetch();
$allowed=$resume && $u['role']==='seeker' && (int)$resume['user_id']===(int)$u['id'];
if($resume && $u['role']==='employer')$allowed=(bool)query('SELECT a.id FROM applications a JOIN eprofile j ON j.id=a.job_id WHERE a.resume_id=? AND a.user_id=? AND j.employer_id=?',[$resume['id'],$resume['user_id'],$u['id']])->fetch();
if(!$allowed){http_response_code(404);exit('Résumé unavailable.');}
$directory=getenv('HUSTLERS_UPLOAD_DIR')?:__DIR__.'/private/resumes';$key=$resume['storage_key'];
if(!preg_match('/^[a-f0-9]{64}\.pdf$/',$key)||!is_file($directory.DIRECTORY_SEPARATOR.$key)){http_response_code(404);exit('Résumé unavailable.');}
header('Content-Type: application/pdf');header('Cache-Control: private, no-store');header('Content-Disposition: attachment; filename="resume.pdf"; filename*=UTF-8\'\''.rawurlencode($resume['original_name']));header('Content-Length: '.filesize($directory.DIRECTORY_SEPARATOR.$key));readfile($directory.DIRECTORY_SEPARATOR.$key);exit;
