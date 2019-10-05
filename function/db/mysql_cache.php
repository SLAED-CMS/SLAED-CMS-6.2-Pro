<?php
if (!defined("FUNC_FILE")) die("Illegal File Access");

class db_cached extends sql_db{
	var $active = "true";
	var $prefix = "";
	var $cached = array();
	var $pointer = array();
	var $cache_dir = "config/cache/db/";
	var $index_cache = array();
	var $index_filename = "index_cache";
	var $reset_comm = array('INSERT', 'UPDATE', 'DELETE');
	var $not_cache_tables = array('session', 'users');
	var $not_cache_func = array('NOW', 'UNIX_TIMESTAMP');
	var $not_cache_func_timeout = 300;

	function db_cached($sqlserver, $sqluser, $sqlpassword, $database, $charset, $prefix = ""){
		$this->prefix = $prefix;
		if (file_exists($this->cache_dir.$this->index_filename)){
			include_once($this->cache_dir.$this->index_filename);
			$this->index_cache=$data;
		}
		foreach($this->not_cache_tables as $key=>$item) $this->not_cache_tables[$key] = $this->prefix."_".$item;
		$this->sql_db($sqlserver, $sqluser, $sqlpassword, $database, $charset);
	}

	function sql_query($query = "") {
		if($this->active){
			unset($this->query_result);
			$is_cached=false;
			if ($query != ''){
				$file_name=md5($query);
				if(!isset($this->cached[$file_name])){
					$command=strtoupper(substr($query, 0, strpos($query, ' ')));
					if($command=='SELECT'){
						preg_match_all('#('.$this->prefix.'[^\s]+)#i', $query, $match);
						$tables=$match[1];
						if(count($tables)>0 && count(array_intersect($tables, $this->not_cache_tables))==0){
							$folder_name=implode('&', $tables).'_';
							if(!file_exists($this->cache_dir.$folder_name.$file_name)){
							#if (!file_exists($this->cache_dir.$folder_name)){
								#mkdir($this->cache_dir.$folder_name, 0777);
								#copy($this->cache_dir.'.htaccess', $this->cache_dir.$folder_name.'.htaccess');
								foreach($tables as $table){
									$this->index_cache[$table]['data'][]=$folder_name;
									$this->index_cache[$table]['linked']=array_diff($tables, $table);
								}
								$index='<?php '.$this->arr2str($this->index_cache).' ?>';
								$this->writefile($this->index_filename, $index);
							}
							if(file_exists($this->cache_dir.$folder_name.$file_name)){
								$last_mod=filemtime($this->cache_dir.$folder_name.$file_name);
								if(!preg_match('#('.implode('|',$this->not_cache_func).')#i', $query)){
									$this->query_result=$file_name;
									$this->readcache($folder_name.$file_name, $this->cached[$file_name]);
									$this->pointer[$file_name]=0;
									$is_cached=true;
								}elseif((time()-$last_mod<=$this->not_cache_func_timeout) && $this->not_cache_func_timeout){
									$this->query_result=$file_name;
									$this->readcache($folder_name.$file_name, $this->cached[$file_name]);
									$this->pointer[$file_name]=0;
									$is_cached=true;
								}
							}
							if(!$is_cached){
								$tdba = array_sum(explode(" ", microtime()));
								$this->query_result = @mysql_query($query, $this->db_connect_id);
								$tdbe = array_sum(explode(" ", microtime()));
								if ($this->query_result) {
									while ($tmp = @mysql_fetch_array($this->query_result)) $this->cached[$file_name][] = $tmp;
									$this->writecache($folder_name.$file_name, $this->cached[$file_name]);
									$this->pointer[$file_name]=0;
									@mysql_free_result($this->query_result);
									$this->query_result=$file_name;
								}
							}
						} else {
							$tdba = array_sum(explode(" ", microtime()));
							$this->query_result = @mysql_query($query, $this->db_connect_id);
							$tdbe = array_sum(explode(" ", microtime()));
						}
					} else {
						$tdba = array_sum(explode(" ", microtime()));
						$this->query_result = @mysql_query($query, $this->db_connect_id);
						$tdbe = array_sum(explode(" ", microtime()));
						if ($this->query_result && in_array($command, $this->reset_comm)) {
							$tmp=strstr($query, $this->prefix);
							$table=substr($tmp, 0, strpos($tmp, ' '));
							if ($table!='' && !in_array($table, $this->not_cache_tables)){
								foreach($this->index_cache[$table]['data'] as $folder){
									$this->cache_clear($folder);
									
									#echo "<h1>".$folder.$file_name."</h1>";
									
									foreach($this->index_cache[$table]['linked'] as $linked) unset($this->index_cache[$linked]['data'][$folder]);
								}
								unset($this->index_cache[$table]['data']);
								$index='<?php '.$this->arr2str($this->index_cache).' ?>';
								$this->writefile($this->index_filename, $index);
							}
						}
					}
				}else{
					$this->query_result=$file_name;
					$is_cached=true;
				}
				$total_tdb = round($tdbe - $tdba, 5);
				$this->total_time_db += $total_tdb;
				$color = ($total_tdb > 0.01) ? "sl_red" : "sl_green";
				
				$cinfo = ($is_cached) ? "<span class=\"sl_green\">Cache</span>" : "<span class=\"sl_red\">Live</span>";
				$this->time_query .= "<span class=\"".$color."\">".$total_tdb."</span> "._SEC.". => [".$cinfo."] => [".$query."]";
			
				if ($this->query_result) {
					$this->time_query .= "<br>";
				} else {
					$this->time_query .= " => <span class=\"sl_red\">"._ERROR.": ".mysql_errno()." - ".mysql_error()."</span><br>";
					if (function_exists("error_sql_log")) error_sql_log(@mysql_errno($this->db_connect_id), @mysql_error($this->db_connect_id), $query);
				}
			}
			if ($this->query_result) {
				if(!$is_cached)$this->num_queries += 1;
				unset($this->row[$this->query_result], $this->rowset[$this->query_result]);
				return $this->query_result;
			} else {
				return false;
			}
		} else {
			parent::sql_query($query);
		}
	}

	function sql_numrows($query_id = 0){
		if($this->active){
			if (!$query_id) $query_id = $this->query_result;
			if ($query_id) {
				if(!isset($this->cached[$query_id])){
					$result = @mysql_num_rows($query_id);
				}else{
					$result=count($this->cached[$query_id]);
				}
				return $result;
			} else {
				return false;
			}
		}else parent::sql_numrows($query_id);
	}

	function sql_fetchrow($query_id = 0) {
		if($this->active){
			if (!$query_id) $query_id = $this->query_result;
			if ($query_id) {
				if(!isset($this->cached[$query_id])){
					$this->row[$query_id] = @mysql_fetch_array($query_id);
				}else{
					if($this->pointer[$query_id]<count($this->cached[$query_id])){
						$this->row[$query_id]=$this->cached[$query_id][$this->pointer[$query_id]];
						$this->pointer[$query_id]++;
					}else return false;
				}
				return $this->row[$query_id];
			} else {
				return false;
			}
		}else parent::sql_fetchrow($query_id);
	}

	function sql_fetchrowset($query_id = 0) {
		if($this->active){
			if (!$query_id) $query_id = $this->query_result;
			if ($query_id) {
				unset($this->rowset[$query_id]);
				unset($this->row[$query_id]);
				if(!isset($this->cached[$query_id])){
					while ($this->rowset[$query_id] = @mysql_fetch_array($query_id)) {
						$result[] = $this->rowset[$query_id];
					}
				}else{
					$result=$this->cached[$query_id];
				}
				return $result;
			} else {
				return false;
			}
		}else parent::sql_fetchrowset($query_id);
	}

	function arr2str(&$array, $arr_name='data', $level=0){
		if (is_array($array) && count($array)>0){
			foreach($array as $key=>$item){
				if(is_array($item) && count($item)>0) $result[]="'".$key."'=>".$this->arr2str($item, '', $level+1);
				else{
					if (gettype($item)=='string') $item=preg_replace("#\=\'([^']*)\'#is", '="$1"', $item);
					$result[]="'".$key."'=>'".str_replace("'", "&#39;", $item)."'";
				}
			}
		}else $result=array();
		if($level==0) return '$'.$arr_name.'=array('.implode(', ', $result).');';
		else return 'array('.implode(', ', $result).')';
	}

	function writecache($filename, &$data){
		$content='<?php if (!defined("FUNC_FILE")) die("Illegal File Access");';
		$content.=$this->arr2str($data);
		$content.=' ?>';
		return $this->writefile($filename, $content);
	}

	function writefile($filename, &$content){
		ignore_user_abort(1);
		$lockfile = $this->cache_dir.$filename . '.lock';
		if (file_exists($lockfile)) {
			if (time() - filemtime($lockfile) > 5) unlink($lockfile);
		}
		$lock_ex = @fopen($lockfile, 'x');
		for ($i=0; ($lock_ex === false) && ($i < 20); $i++) {
			clearstatcache();
			usleep(rand(5, 15));
			$lock_ex = @fopen($lockfile, 'x');
		}
		$success = false;
		if ($lock_ex !== false) {
			$fp = @fopen($this->cache_dir.$filename, 'wb');
			if (@fwrite($fp, $content)) $success = true;
			@fclose($fp);
			fclose($lock_ex);
			unlink($lockfile);
		}
		ignore_user_abort(0);
		return $success;
	}

	function readcache($filename, &$item){
		if (file_exists($this->cache_dir.$filename)) include($this->cache_dir.$filename);
		else $data=array();
		$item=$data;
	}

	function cache_clear($filename){
		$d = opendir($this->cache_dir);
		while (false!==($file = readdir($d))) {
			#echo $file."=".$filename."<br>";
			#$pos = strpos($file, $filename);
			if ($file!='.' && $file!='..' && strpos($file, $filename) !== false){
			
				#echo $file."=".$filename." BINGO!<br>";
				unlink($this->cache_dir.$file);
			} else {
				#echo $file."=".$filename."=".$pos."<br>";
			}
		}
		closedir($d);
		
		#!strpos($fname, $name)
		#if (file_exists($this->cache_dir.$filename)) unlink($this->cache_dir.$filename);
		/*
		if(is_dir($this->cache_dir.$path)){
			$d=opendir($this->cache_dir.$path);
			while (false!==($file = readdir($d))){
				if($file!='.' && $file!='..'){
					if(is_dir($this->cache_dir.$path.$file)){
						$this->cache_clear($file.'/');
						rmdir($this->cache_dir.$path.$file);
					}else{
						if($path!=''){
							unlink($this->cache_dir.$path.$file);
							//unset($this->cached[$file], $this->pointer[$file]);
						}elseif($file!='.htaccess') unlink($this->cache_dir.$path.$file);
					}
				}
			}
			closedir($d);
			if ($path!='') rmdir($this->cache_dir.$path);
		}else{
			unlink($this->cache_dir.$path);
			$file=basename($path);
			//unset($this->cached[$file], $this->pointer[$file]);
		}
		*/
	}
}
?>