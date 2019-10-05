<?php
if (!defined("FUNC_FILE")) die("Illegal File Access");

class sql_db {
	var $db_connect_id;
	var $query_result;
	var $num_queries = 0;
	var $total_time_db = 0;
	var $time_query = "";

	function sql_db($sqlserver, $sqluser, $sqlpassword, $database, $charset = false) {
		$this->db_connect_id = @mysqli_connect($sqlserver, $sqluser, $sqlpassword, $database);
		if ($charset) mysqli_set_charset($charset);
		if ($this->db_connect_id) {
			#mysqli_query($this->db_connect_id,'SET NAMES cp1251');
			return $this->db_connect_id;
		} else {
			return false;
		}
	}

	function sql_close() {
		if ($this->db_connect_id) {
			if ($this->query_result) mysqli_free_result($this->query_result);
			$result = mysqli_close($this->db_connect_id);
			return $result;
		} else {
			return false;
		}
	}

	function sql_query($query = "", $transaction = false) {
		unset($this->query_result);
		if ($query != "") {
			$st = array_sum(explode(" ", microtime()));
			$this->query_result = mysqli_query($this->db_connect_id,$query);
			$total_tdb = round(array_sum(explode(" ", microtime())) - $st, 5);
			$this->total_time_db += $total_tdb;
			$color = ($total_tdb > 0.01) ? "red" : "green";
			$this->time_query .= "<span style=\"color: ".$color.";\">".$total_tdb."</span> "._SEC.". => [".$query."]<br>";
		}
		if ($this->query_result) {
			$this->num_queries += 1;
			return $this->query_result;
		} else {
			return ($transaction == END_TRANSACTION) ? true : false;
		}
	}

	function sql_numrows($query_id = 0) {
		if (!$query_id) $query_id = $this->query_result;
		return ($query_id) ? mysqli_num_rows($query_id) : false;
	}

	function sql_affectedrows() {
		return ($this->db_connect_id) ? mysqli_affected_rows($this->db_connect_id) : false;
	}

	function sql_fetchrow($query_id = 0) {
		if (!$query_id) $query_id = $this->query_result;
		if ($query_id)
			return mysqli_fetch_array($query_id);
		return false;
	}

	function sql_nextid() {
		return ($this->db_connect_id) ? @mysqli_insert_id($this->db_connect_id) : false;
	}

	function sql_freeresult($query_id = 0){
		if (!$query_id) $query_id = $this->query_result;
		if ($query_id) {
			mysqli_free_result($query_id);
			return true;
		}
			return false;
	}

	function sql_error($query_id = 0) {
		$result["message"] = mysqli_error($this->db_connect_id);
		$result["code"] = mysqli_errno($this->db_connect_id);
		return $result;
	}
}
?>