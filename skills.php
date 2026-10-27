<?php
function skill_list(string $text): array {
 $items=preg_split('/[,;\r\n]+/u',$text);$out=[];
 foreach($items as $item){$item=trim(preg_replace('/\s+/u',' ',$item));if($item!=='')$out[skill_key($item)]=$item;}
 return array_values($out);
}
function skill_key(string $value): string {
 $key=mb_strtolower(trim(preg_replace('/\s+/u',' ',$value)),'UTF-8');
 $aliases=['js'=>'javascript','nodejs'=>'node.js','node js'=>'node.js','postgres'=>'postgresql','ros2'=>'ros 2','cpp'=>'c++','c plus plus'=>'c++','ms excel'=>'excel','microsoft excel'=>'excel','qgis gis'=>'qgis'];
 return $aliases[$key]??$key;
}
function skill_match(string $required,string $candidate): array {
 $needs=skill_list($required);$have=array_map('skill_key',skill_list($candidate));$matched=[];$missing=[];
 foreach($needs as $skill){if(in_array(skill_key($skill),$have,true))$matched[]=$skill;else $missing[]=$skill;}
 return ['matched'=>$matched,'missing'=>$missing,'total'=>count($needs),'score'=>$needs?(int)round(count($matched)*100/count($needs)):0];
}
