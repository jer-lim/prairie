<?php

class MeadowQuery {
	private $dbConfig;
	private $table = null;
	private $where = array();
	private $orderBy = array();
	private $queryParameters = array();

	function __construct($dbName){
		$this->dbConfig = $dbName;
	}

	/**
	 * Set table to query
	 * @param  string $table
	 * @return MeadowQuery $this
	 */
	public function table($table){
		$this->table = $table;
		return $this;
	}

	/**
	 * Set ... WHERE x part of the query
	 * @param  string $column
	 * @param  string $operator
	 * @param  string $value
	 * @return MeadowQuery $this
	 */
	public function where($column, $operator, $value){
		$clause = new StdClass();
		$clause->column = $column;
		$clause->operator = $operator;
		$clause->value = $value;
		array_push($this->where, $clause);
		return $this;
	}

	/**
	 * set ... ORDER BY x part of the query
	 * @param  string $column
	 * @param  string $sort
	 * @return MeadowQuery $this
	 */
	public function orderBy($column, $sort){
		$clause = new StdClass();
		$clause->column = $column;
		if(strtoupper($sort) === "ASC" || strtoupper($sort) === "DESC"){
			$clause->sort = strtoupper($sort);
		}else{
			throw new Exception('$sort can only be "ASC" or "DESC"');
		}
		array_push($this->orderBy, $clause);
		return $this;
	}

	/**
	 * Perform SELECT using set parameters
	 * @param  mixed   $parameters
	 * @param  integer $start start limit of LIMIT. If $count not set, $start is the count
	 * @param  integer $count number of rows to fetch
	 * @return array
	 */
	public function get($parameters = array(), $start=0, $count=0){

		//put operation
		$queryString = "SELECT ";

		if(empty($parameters) || $parameters === "*"){
			$queryString .= "* ";
		}else{
			$first = true;
			//put columns
			$queryString .= $this->formatParameterString($parameters) . " ";
		}

		//put FROM table
		$queryString .= "FROM `" . $this->table . "` ";

		//put WHERE
		$queryString .= $this->getWhereQueryString();

		//put ORDER BY
		if(!empty($this->orderBy)){
			$queryString .= "ORDER BY ";
			$first = true;
			foreach($this->orderBy as $clause){
				if(!$first) $queryString .= ", ";
				$first = false;
				$queryString .= "`" . $clause->column . "` " . $clause->sort;
			}
			$queryString .= " ";
		}

		if($count != 0){
			$queryString .= "LIMIT " . $start . ", " . $count;
		}else if($start != 0 && $count === 0){
			$queryString .= "LIMIT " . $start;
		}

		$db = DB::getDBO($this->dbConfig);
		$query = $db->prepare($queryString);
		$query->execute($this->queryParameters);
		$result = $query->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	}

	/**
	 * Perform INSERT INTO query using set parameters
	 * @param  array $columns Columns to set
	 * @param  array $values Array of rows, where each row is an array of values that corresponds to the columns defined
	 * @return MeadowQuery $this
	 */
	public function insert(array $columns=null, array $values){
		$queryString = "INSERT INTO `" . $this->table . "` ";

		//put columns
		if($columns !== null){
			$queryString .= "(" . $this->formatParameterString($columns) . ") ";
		}

		//put values
		$queryString .= "VALUES ";
		//if inserting a single row, nest the values in another array
		if((array)current($values) != current($values)){
			$values = array($values);
		}

		$first = true;
		foreach($values as $row){
			if(!$first) $queryString .= ", ";
			$first = false;

			$queryString .= "(";
			$first2 = true;
			foreach($row as $field){
				if(!$first2) $queryString .= ",";
				$first2 = false;
				$queryString .= "?";
				$this->addQueryParameter($field);
			}
			$queryString .= ")";
		}

		$db = DB::getDBO($this->dbConfig);
		$query = $db->prepare($queryString);
		$query->execute($this->queryParameters);

		return $this;
	}

	/**
	 * Perform UPDATE query
	 * @param array $args key-value pair of attributes to change
	 * @return MeadowQuery $this
	 */
	public function update($args){
		$queryString = "UPDATE `" . $this->table . "` SET ";
		
		$first = true;
		foreach($args as $key => $value){
			if(!$first) $queryString .= ", ";
			$first = false;
			$queryString .= "`" . $key . "` = ?";
			$this->addQueryParameter($value);
		}
		$queryString .= " ";
		$queryString .= $this->getWhereQueryString();
		$db = DB::getDBO($this->dbConfig);
		$query = $db->prepare($queryString);
		$query->execute($this->queryParameters);

		return $this;

	}

	/**
	 * Perform DELETE FROM query using set parameters
	 * @return MeadowQuery $this
	 */
	public function delete(){
		$queryString = "DELETE FROM `" . $this->table . "` " . $this->getWhereQueryString();

		$db = DB::getDBO($this->dbConfig);
		$query = $db->prepare($queryString);
		$query->execute($this->queryParameters);

		return $this;
	}

	/**
	 * Add a parameter to be passed into PDO::execute to replace placeholder
	 * @param string $parameter
	 */
	private function addQueryParameter($parameter){
		array_push($this->queryParameters, $parameter);
	}

	/**
	 * Format array of parameters as an SQL list, e.g. `a`, `b`, `c`
	 * @param  array $parameters
	 * @return string
	 */
	private function formatParameterString($parameters){
		$str = "";
		$first = true;
		foreach($parameters as $parameter){
			if(!$first) $str .= ", ";
			$first = false;
			$str .= "`" . $parameter . "`";
		}
		return $str;
	}

	/**
	 * Generate the WHERE x portion of the query based on clauses previously inserted with where()
	 * @return string
	 */
	private function getWhereQueryString(){
		$queryString = "";
		if(!empty($this->where)){
			$queryString .= "WHERE ";
			$first = true;
			foreach($this->where as $clause){
				if(!$first) $queryString .= "AND ";
				$first = false;
				$queryString .= "`" . $clause->column . "` " . $clause->operator . " ? ";
				$this->addQueryParameter($clause->value);
			}
		}

		return $queryString;
	}
}