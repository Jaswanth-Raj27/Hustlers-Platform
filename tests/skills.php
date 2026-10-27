<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/skills.php';
function check(bool $condition,string $message): void { if(!$condition)throw new RuntimeException($message); }
check(skill_match('JavaScript, MySQL, C++','JS, mysql, cpp')['score']===100,'Alias or case match failed');
check(skill_match('Java, C, SQL','JavaScript, C++, MySQL')['score']===0,'Different skills must not match by substring');
check(count(skill_list('JS, JavaScript, js'))===1,'Duplicate requirements inflate the score');
check(skill_match('','Python')['total']===0,'Empty requirements must be safe');
$match=skill_match('Python, Linux, Git, Docker','Python, Git');
check($match['score']===50 && $match['missing']===['Linux','Docker'],'Partial score or missing skills are incorrect');
echo "Skill matching checks passed.\n";
