<?php
if($action==='profile-details'){
 $name=trim($_POST['name']??'');$title=trim($_POST['title']??'');$location=trim($_POST['location']??'');$bio=trim($_POST['bio']??'');$website=trim($_POST['website']??'');
 if(strlen($name)<2||strlen($name)>100||strlen($title)>120||strlen($location)>120||strlen($bio)>3000||strlen($website)>255||($website!==''&&(!filter_var($website,FILTER_VALIDATE_URL)||!in_array(strtolower(parse_url($website,PHP_URL_SCHEME)??''),['http','https'],true))))throw new RuntimeException('Check field lengths and use an https:// or http:// website address.');
 $field=$_POST['field']??'Engineering';$skills=trim($_POST['skills']??'');if(!in_array($field,$categories,true)||strlen($skills)>2000)throw new RuntimeException('Choose a valid field and keep skills within 2,000 characters.');
 $age=trim($_POST['age']??'');$experience=trim($_POST['experience_years']??'');$education=trim($_POST['education']??'');$history=trim($_POST['work_history']??'');
 if($u['role']==='seeker'&&(($age!==''&&(!ctype_digit($age)||(int)$age<16||(int)$age>100))||($experience!==''&&(!is_numeric($experience)||(float)$experience<0||(float)$experience>60))||strlen($education)>500||strlen($history)>6000))throw new RuntimeException('Use age 16–100, experience 0–60 years, education up to 500 characters and work history up to 6,000 characters.');
 db()->beginTransaction();
 try{query('UPDATE '.($u['role']==='employer'?'euser':'juser').' SET name=? WHERE id=?',[$name,$u['id']]);
 if($u['role']==='seeker')query('UPDATE jprofile SET title=?,location=?,bio=?,website=?,field=?,skills=?,discoverable=?,age=?,experience_years=?,education=?,work_history=? WHERE user_id=?',[$title,$location,$bio,$website,$field,$skills,isset($_POST['discoverable'])?1:0,$age===''?null:(int)$age,$experience===''?null:(float)$experience,$education,$history,$u['id']]);
 else query('UPDATE euser SET company_name=?,title=?,location=?,bio=?,website=? WHERE id=?',[$name,$title,$location,$bio,$website,$u['id']]);
 db()->commit();}catch(Throwable $e){db()->rollBack();throw $e;}flash('Profile saved.');go('profile.php');
}
if($action==='invite'){
 require_user('employer');$target=(int)($_POST['seeker_id']??0);$jobId=(int)($_POST['job_id']??0);
 $person=query("SELECT p.user_id FROM jprofile p JOIN juser u ON u.id=p.user_id WHERE p.user_id=? AND u.role='seeker' AND (p.discoverable=1 OR EXISTS(SELECT 1 FROM applications a JOIN eprofile j ON j.id=a.job_id WHERE a.user_id=p.user_id AND j.employer_id=?))",[$target,$u['id']])->fetch();
 if(!$person||!query('SELECT id FROM eprofile WHERE id=? AND employer_id=?',[$jobId,$u['id']])->fetch())throw new RuntimeException('Choose an available candidate and one of your own jobs.');
 query('INSERT INTO hiring_invitations(employer_id,seeker_id,job_id) VALUES(?,?,?) ON DUPLICATE KEY UPDATE id=id',[$u['id'],$target,$jobId]);flash('Offer sent. The candidate can accept or decline from their workspace.');go('candidates.php?id='.$target);
}
if($action==='offer-response'){
 require_user('seeker');$status=$_POST['status']??'';if(!in_array($status,['Accepted','Declined'],true))throw new RuntimeException('Choose an offer response.');
 query("UPDATE hiring_invitations SET status=? WHERE id=? AND seeker_id=? AND status='Offered'",[$status,(int)($_POST['invitation_id']??0),$u['id']]);flash('Your response was saved.');go('dashboard.php');
}
