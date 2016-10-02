<?php

class Model {
	/**
	 * Name of the table that the model is stored in
	 * @var string
	 */
	protected static $table;

	/**
	 * Name of the database config
	 * @var string
	 */
	protected static $dbConfig;

	/**
	 * Array of attributes
	 * @var array
	 */
	protected static $attributes=array();

	/**
	 * String that represents the primary key, or an array of attributes if it's a composite key
	 * @var string
	 */
	protected static $primaryKey="id";

	/**
	 * length of time in seconds to cache
	 */
	protected static $cacheTime = 0;

	/**
	 * whether or not to use cache
	 * cache is only used when fetching data using exact same queries
	 */
	protected static $useCache = true;

	/**
	 * Preserved original attributes from a get method
	 * @var array
	 */
	private $originalAttributes=null;

	/**
	 * Manipulate this to query (2.0)
	 */
	public static $meadowQuery = null;

	/**
	 * Singleton to chain methods
	 */
	protected static $_instance = null;

	public function __construct(){
		if(is_null(static::$table) || is_null(static::$attributes) || is_null(static::$primaryKey)){
			throw new Exception('$table, $attributes, and $primaryKey must be set.');
		}else{
			$this->initAttributes();
		}
	}

	/**
	 * Promote attributes defined in $attributes to actual attributes
	 */
	protected function initAttributes(){
		foreach(static::$attributes as $attribute){
			$this->{$attribute} = null;
		}
	}

	/**
	 * Get an array of models after processing using the passed in $func. Pass in an anonymous function with 1 parameter to manipulate $query.
	 * @return array
	 */
	public static function query($func = null){
		if(static::$_instance === null){
			$calledClass=get_called_class();
			static::$_instance = new $calledClass();
		}
		static::$_instance->meadowQuery = DB::query(static::$dbConfig, static::$table);
		return static::$_instance;
	}

	/**
	 * Get a single row based on primary key
	 * @param mixed $param Parameter which corresponds to the order of attributes in the defined primary key. Can have multiple.
	 * @return Model
	 */
	public static function getByPk(){
		$args=func_get_args();
		//Number of arguments passed does not match number of attributes in primary key

		if(!is_array(static::$primaryKey)) $primaryKey = array(static::$primaryKey);
		else $primaryKey = static::$primaryKey;

		if(count($args) != count($primaryKey)){
			throw new Exception("Primary key consists of " . count($primaryKey) . " attributes, " . count($args) . " passed.");
		}else{
			$query = DB::query(static::$dbConfig, static::$table);
			foreach($primaryKey as $attribute){
				if(is_string(current($args))) $comparator = "LIKE";
				else if(is_numeric(current($args))) $comparator = "=";
				$query->where($attribute, $comparator, current($args));
				next($args);
			}

			// Load from cache if not done yet
			$cacheName = sha1(serialize($query));
			if(static::$cacheTime > 0 && static::$useCache){
				$models = Cache::load($cacheName, "/models/" . get_called_class());
				if($models){
					return $models[0];
				}
			}

			$result = $query->get(static::$attributes,1);
			if($result){

				$calledClass = get_called_class();
				$model = new $calledClass();
				$model->originalAttributes = $result[0];

				foreach($result[0] as $key => $value){
					$model->$key = $value;
				}

				if(static::$cacheTime > 0 && static::$useCache){
					Cache::save($cacheName, static::$cacheTime, array($model), "/models/" . get_called_class());
				}

				return $model;
			}else{
				return false;
			}
		}
	}

	/**
	 * Insert or update model
	 * @return bool true if succeed
	 */
	public function save(){
		$attributes = $this->serializeAttributes();
		if(is_null($this->originalAttributes)){

			//remove nulls
			$attributeList = static::$attributes;
			$i = 0;

			for($i = 0; $i < sizeof($attributeList);){
				if(is_null($attributes[$attributeList[$i]])){
					unset($attributes[$attributeList[$i]]);
					array_splice($attributeList, $i, 1);
				}else{
					$i++;
				}
			}

			//insert new record
			DB::query(static::$dbConfig, static::$table)->insert($attributeList, array($attributes));
			return true;
		}else if($attributes != $this->originalAttributes){
			//update record
			//get primary key
			if(!is_array(static::$primaryKey)) $primaryKey = array(static::$primaryKey);
			else $primaryKey = static::$primaryKey;

			$query = DB::query(static::$dbConfig, static::$table);
			foreach($primaryKey as $attribute){
				$query->where($attribute, "=", $this->$attribute);
			}

			$query->update($attributes);
			return true;
		}
	}

	/**
	 * Delete model
	 */
	public function delete(){
		if(!is_array(static::$primaryKey)) $primaryKey = array(static::$primaryKey);
		else $primaryKey = static::$primaryKey;

		$query = DB::query(static::$dbConfig, static::$table);
		foreach($primaryKey as $attribute){
			$query->where($attribute, "=", $this->$attribute);
		}
		$query->delete();
	}

	protected function serializeAttributes(){
		$arr = array();
		foreach(static::$attributes as $attribute){
			$arr[$attribute] = $this->$attribute;
		}

		return $arr;
	}

	/**
	 * 2.0 MeadowQuery middlemen
	 */
	public function orderBy($column, $sort){
		if($this->meadowQuery == null) throw new Exception("MeadowQuery not initialised");
		$this->meadowQuery->orderBy($column, $sort);
		return $this;
	}

	public function get($skip = 0, $count = 0){
		if($this->meadowQuery == null) throw new Exception("MeadowQuery not initialised");

		// Load from cache if not done yet
		$cacheName = sha1(serialize($this->meadowQuery));
		if(static::$cacheTime > 0 && static::$useCache){
			$models = Cache::load($cacheName, "/models/" . get_called_class());
			if($models){
				$this->clearQuery();
				return $models;
			}
		}
		// Continue if no cache
		$results = $this->meadowQuery->get("*", $skip, $count);

		$models = array();
		foreach($results as $result){
			$calledClass = get_called_class();
			$model = new $calledClass();
			$model->originalAttributes = $result;

			foreach($result as $key => $value){
				$model->$key = $value;
			}

			array_push($models, $model);
		}
		if(static::$cacheTime > 0 && static::$useCache){
			Cache::save($cacheName, static::$cacheTime, $models, "/models/" . get_called_class());
		}

		$this->clearQuery();
		return $models;
	}

	public function getFirst(){
		return $this->get(1)[0];
	}

	/**
	 * Get an array of models matching the criteria.
	 * @return array
	 */
	public function where($column, $operator, $value){
		if($this->meadowQuery == null) throw new Exception("MeadowQuery not initialised");
		$this->meadowQuery->where($column, $operator, $value);
		return $this;
	}

	public function insert(array $columns=null,array $values){
		if($this->meadowQuery == null) throw new Exception("MeadowQuery not initialised");
		$this->meadowQuery->insert($columns, $values);

		$this->clearQuery();

		return $this;
	}

	public function update($args){
		if($this->meadowQuery == null) throw new Exception("MeadowQuery not initialised");
		$this->meadowQuery->update($args);

		$this->clearQuery();

		return $this;
	}

	public function clearQuery(){
		$this->meadowQuery = null;
		static::$_instance = null;
		return $this;
	}

	/**
	 * Turn cache on and off
	 */
	
	public static function setCache(bool $isOn){
		static::$useCache = $isOn;
	}

	/**
	 * Reset back to original state
	 */
	public function reset(){
		if($this->originalAttributes != null){
			foreach($this->originalAttributes as $oa => $value){
				$this->$oa = $value;
			}
		}
	}
}