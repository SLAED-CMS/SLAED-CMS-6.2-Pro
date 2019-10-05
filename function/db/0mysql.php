<?php
if (!defined("FUNC_FILE")) die("Illegal File Access");

class sql_db {
	var $db_connect_id;
	var $query_result;
	var $row = array();
	var $rowset = array();
	var $num_queries = 0;
	var $total_time_db = 0;
	var $time_query = "";
	
	# Открывает соединение с сервером MySQL
	function sql_db($sqlserver, $sqluser, $sqlpassword, $database, $charset = false) {
		$this->db_connect_id = @mysqli_connect($sqlserver, $sqluser, $sqlpassword, $database);
		#if ($charset) @mysqli_set_charset($charset);
		if ($this->db_connect_id) {
			/*
			if ($database != "" && !@mysqli_select_db($database)) {
				@mysqli_close($this->db_connect_id);
				$this->db_connect_id = false;
			}
			*/
			return $this->db_connect_id;
		} else {
			return false;
		}
	}
	
	# Закрывает соединение с сервером MySQL
	function sql_close() {
		if ($this->db_connect_id) {
			if ($this->query_result) @mysqli_free_result($this->query_result);
			$result = @mysqli_close($this->db_connect_id);
			return $result;
		} else {
			return false;
		}
	}
	
	# Выполняет SQL запрос
	function sql_query($query = "") {
		if ($this->query_result) unset($this->query_result);
		if ($query != "") {
			$st = array_sum(explode(" ", microtime()));
			$this->query_result = @mysqli_query($this->db_connect_id, $query);
			$total_tdb = round(array_sum(explode(" ", microtime())) - $st, 5);
			$this->total_time_db += $total_tdb;
			$color = ($total_tdb > 0.01) ? "sl_red" : "igreen";
			$this->time_query .= "<span class=\"".$color."\">".$total_tdb."</span> "._SEC.". => [".$query."]";
		}
		if ($this->query_result) {
			$this->time_query .= "<br>";
			$this->num_queries += 1;
			unset($this->row[$this->query_result]);
			unset($this->rowset[$this->query_result]);
			return $this->query_result;
		} else {
			$this->time_query .= " => <span class=\"sl_red\">"._ERROR.": ".mysqli_errno()." - ".mysqli_error()."</span><br>";
			if (function_exists("error_sql_log")) error_sql_log(@mysqli_errno($this->db_connect_id), @mysqli_error($this->db_connect_id), $query);
			return false;
		}
	}
	
	# Возвращает количество строк в результате
	function sql_numrows($query_id = 0) {
		if (!$query_id) $query_id = $this->query_result;
		return ($query_id) ? @mysqli_num_rows($query_id) : false;
	}
	
	# Получает количество рядов, задействованных в предыдущей операции MySQL
	function sql_affectedrows() {
		return ($this->db_connect_id) ? @mysqli_affected_rows($this->db_connect_id) : false;
	}
	
	# Возвращает количество столбцов для последнего запроса
	function sql_numfields($query_id = 0) {
		if (!$query_id) $query_id = $this->query_result;
		return ($query_id) ? @mysqli_num_fields($query_id) : false;
	}
	
	# Возвращает название указанной колонки результата запроса
	function sql_fieldname($offset, $query_id = 0) {
		if (!$query_id) $query_id = $this->query_result;
		return ($query_id) ? @mysqli_field_name($query_id, $offset) : false;
	}
	
	# Возвращает тип указанного поля результата запроса
	function sql_fieldtype($offset, $query_id = 0) {
		if (!$query_id) $query_id = $this->query_result;
		return ($query_id) ? @mysqli_field_type($query_id, $offset) : false;
	}
	
	# Возвращает результат как численный массив
	function sql_fetchrow($query_id = 0) {
		if (!$query_id) $query_id = $this->query_result;
		if ($query_id) {
			$this->row[$query_id] = @mysqli_fetch_array($query_id);
			return $this->row[$query_id];
		} else {
			return false;
		}
	}
	
	# Возвращает численный и ассоциативный массивы
	function sql_fetchrowset($query_id = 0) {
		if (!$query_id) $query_id = $this->query_result;
		if ($query_id) {
			unset($this->rowset[$query_id]);
			unset($this->row[$query_id]);
			while ($this->rowset[$query_id] = @mysqli_fetch_array($query_id)) $result[] = $this->rowset[$query_id];
			return $result;
		} else {
			return false;
		}
	}
	
	# Возвращает данные из указанной строки и поля
	function sql_fetchfield($field, $rownum = -1, $query_id = 0) {
		if (!$query_id) $query_id = $this->query_result;
		if ($query_id) {
			if ($rownum > -1) {
				$result = @mysqli_result($query_id, $rownum, $field);
			} else {
				if (empty($this->row[$query_id]) && empty($this->rowset[$query_id])) {
					if ($this->sql_fetchrow()) $result = $this->row[$query_id][$field];
				} else {
					if ($this->rowset[$query_id]) {
						$result = $this->rowset[$query_id][0][$field];
					} elseif ($this->row[$query_id]) {
						$result = $this->row[$query_id][$field];
					}
				}
			}
			return $result;
		} else {
			return false;
		}
	}
	
	# Перемещение указателя на произвольную строку в результате запроса
	function sql_rowseek($rownum, $query_id = 0) {
		if (!$query_id) $query_id = $this->query_result;
		return ($query_id) ? @mysqli_data_seek($query_id, $rownum) : false;
	}
	
	# Возвращает ID, сгенерированный при последнем INSERT-запросе
	function sql_nextid() {
		return ($this->db_connect_id) ? @mysqli_insert_id($this->db_connect_id) : false;
	}
	
	# Освобождает память от результата запроса
	function sql_freeresult($query_id = 0){
		if (!$query_id) $query_id = $this->query_result;
		if ($query_id) {
			unset($this->row[$query_id]);
			unset($this->rowset[$query_id]);
			@mysqli_free_result($query_id);
			return true;
		} else {
			return false;
		}
	}
	
	# Возвращает код и строку описания ошибки последнего соединения
	function sql_error($query_id = 0) {
		$result["code"] = @mysqli_errno($this->db_connect_id);
		$result["message"] = @mysqli_error($this->db_connect_id);
		return $result;
	}
}
?>