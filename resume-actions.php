<?php
function resume_directory(): string {
 $path=getenv('HUSTLERS_UPLOAD_DIR')?:__DIR__.'/private/resumes';
 if(!is_dir($path)&&!mkdir($path,0700,true)&&!is_dir($path))throw new RuntimeException('Could not create résumé storage.');
 return $path;
}
if($action==='upload-resumes'){
 require_user('seeker');$files=$_FILES['resumes']??null;
 if(!$files||!is_array($files['name']))throw new RuntimeException('Choose one or more PDF résumés.');
 $n=count($files['name']);if($n>5)throw new RuntimeException('Upload up to five files at a time.');
 $existing=(int)query('SELECT COUNT(*) FROM resumes WHERE user_id=? AND is_active=1',[$u['id']])->fetchColumn();if($existing+$n>10)throw new RuntimeException('Keep up to ten active résumés. Archive an older one first.');
 $pending=[];$written=[];$total=0;
 for($i=0;$i<$n;$i++){
  if($files['error'][$i]!==UPLOAD_ERR_OK)throw new RuntimeException('An upload failed. Choose PDF files of 5 MB or less.');
  $tmp=$files['tmp_name'][$i];$size=(int)$files['size'][$i];$total+=$size;
  if(!is_uploaded_file($tmp)||$size<5||$size>5*1024*1024||$total>25*1024*1024)throw new RuntimeException('Each résumé must be a PDF of 5 MB or less.');
  $name=basename(str_replace('\\','/',$files['name'][$i]));$mime=(new finfo(FILEINFO_MIME_TYPE))->file($tmp);
  if(strtolower(pathinfo($name,PATHINFO_EXTENSION))!=='pdf'||$mime!=='application/pdf'||file_get_contents($tmp,false,null,0,5)!=='%PDF-')throw new RuntimeException('Only PDF résumés are accepted. Export your document as PDF first.');
  $label=trim(pathinfo($name,PATHINFO_FILENAME));$label=mb_substr($label?:'Résumé',0,100);$name=mb_substr($name,0,200);
  $pending[]=[$tmp,$size,$label,$name,bin2hex(random_bytes(32)).'.pdf'];
 }
 db()->beginTransaction();try{foreach($pending as [$tmp,$size,$label,$name,$key]){$dest=resume_directory().DIRECTORY_SEPARATOR.$key;if(!move_uploaded_file($tmp,$dest))throw new RuntimeException('Could not save résumé. Try again.');$written[]=$dest;query('INSERT INTO resumes(user_id,label,original_name,storage_key,size_bytes) VALUES(?,?,?,?,?)',[$u['id'],$label,$name,$key,$size]);}db()->commit();}catch(Throwable $e){if(db()->inTransaction())db()->rollBack();foreach($written as $file)if(is_file($file))unlink($file);throw $e;}
 flash('Your résumés are ready to select when applying.');go('profile.php#resumes');
}
if($action==='archive-resume'){
 require_user('seeker');query('UPDATE resumes SET is_active=0 WHERE id=? AND user_id=?',[(int)($_POST['resume_id']??0),$u['id']]);flash('Résumé archived. Applications already sent keep their selected version.');go('profile.php#resumes');
}
