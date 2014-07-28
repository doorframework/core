<?php

/*
 * Created by Sachik Sergey
 * box@serginho.ru
 */
namespace Door\Core\Database;
use MongoDB;
/**
 * Description of Helper
 *
 * @author serginho
 */
class Helper {
	
	public static function getNextSequence(MongoDB $db, $sequence_name){
		
		$sequence_name = str_replace(array('"',"'"), "", $sequence_name);
		$collection_name = "door_counters";
		$collection = $db->selectCollection($collection_name);
		$count = $collection->count(array("_id" => $sequence_name));
		
		if($count == 0)
		{
			$collection->insert(array(
				"_id" => $sequence_name,
				"seq" => 0
			));
		}
		
		$ret = $db->execute("function(){var ret = db.{$collection_name}"
		. ".findAndModify({query:{'_id':'{$sequence_name}'},"
		. "update:{\$inc:{seq:1}},new:true});return ret.seq;}");							
		
		return $ret['retval'];	
		
	}
	
}
