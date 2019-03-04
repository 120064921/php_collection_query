<?php

namespace GL;

/**
 * Class Collections 聚合查询 集合类
 * @package GL
 */
class Collections{

    public $arrays;

    public function __construct($array=[])
    {
        $this->arrays = $array;
    }

    /**
     * 根据指定条件返回数据
     * 传参示例 where($array, ['id'=>1, ['value', '>=', 10]])
     * @param array $array
     * @param array $condition
     * @return $this
     */
    public function where($condition=[], $array=[])
    {
        $newArray = [];
        if(empty($array)){
            $array = $this->arrays;
        }
        foreach($array as $item){
            $flag = 1;
            foreach($condition as $k => $c){
                if (is_array($c)) {
                    $flag = $this->conditions($item, $c);
                    if ($flag == 0) {
                        break;
                    }
                }else{
                    if($item[$k] != $c){
                        $flag = 0;
                        if ($flag == 0) {
                            break;
                        }
                    }
                }
            }
            if($flag){
                $newArray[] = $item;
            }
        }
        $this->arrays = $newArray;

        return $this;
    }

    /**
     * 对数组的指定元素进行总计
     * @param $field
     * @return int
     */
    public function sum($field)
    {
        if (is_array($field)){
            $sum = null;
            foreach ($field as $field_i) {
                $sum[$field_i] = 0;
            }
            foreach($this->arrays as $item){
                foreach ($field as $field_i) {
                    $sum[$field_i] += $item[$field_i];
                }
            }

            return $sum;
        }else{
            $sum = 0;
            foreach($this->arrays as $item){
                $sum += $item[$field];
            }

            return $sum;
        }
    }

    /**
     * 对数组进行计数
     * @return int
     */
    public function count()
    {
        return count($this->arrays);
    }

    /**
     * 根据输入的条件对应判断
     * @param $item
     * @param $c
     * @return int
     */
    public function conditions($item, $c)
    {
        $flag = 0;
        switch ($c[1]) {
            case 'like':
            case 'LIKE':
                if(strstr($item[$c[0]], $c[2])){
                    $flag = 1;
                }
                break;
            case '<':
                if($item[$c[0]] < $c[2]){
                    $flag = 1;
                }
                break;
            case '>':
                if($item[$c[0]] > $c[2]){
                    $flag = 1;
                }
                break;
            case '<>':
            case '!=':
                if($item[$c[0]] != $c[2]){
                    $flag = 1;
                }
                break;
            case '<=':
                if($item[$c[0]] <= $c[2]){
                    $flag = 1;
                }
                break;
            case '>=':
                if($item[$c[0]] >= $c[2]){
                    $flag = 1;
                }
                break;
            case '=':
                if($item[$c[0]] == $c[2]){
                    $flag = 1;
                }
                break;
            default:
                break;
        }

        return $flag;
    }

    /**
     * 返回数据或返回指定字段数据
     * @param null $field
     * @return array
     */
    public function select($field=null)
    {
        if ($field){
            $newArray = [];
            if (is_array($field)) {
                foreach($this->arrays as $key => $item){
                    foreach ($field as $f){
                        if (array_key_exists($f, $item)){
                            $newArray[$key][$f] = $item[$f];
                        }
                    }
                }
            }elseif (is_string($field)){
                foreach($this->arrays as $key => $item){
                    if (array_key_exists($field, $item)){
                        $newArray[$key][$field] = $item[$field];
                    }
                }
            }
            return $newArray;
        }else{
            return $this->arrays;
        }
    }

    public function __set($name, $val)
    {
        $this->$name = $val;
    }

    public function __get($name)
    {
        return $this->$name = $this->arrays[0][$name];
    }

    /**
     * 返回第一条数据
     * @return null
     */
    public function one()
    {
        if(isset($this->arrays[0])){
            return $this->arrays[0];
        }else{
            return null;
        }
    }

    /**
     * 返回所有数据
     * @return mixed
     */
    public function all()
    {
        return $this->arrays;
    }

    /**
     * 返回一个由指定元素新组成的二维数组
     * @param $field
     * @param array $arrays
     * @return array
     */
    public static function toArray($field, $arrays=[])
    {
        $newArray = [];
        foreach($arrays as $array){
            $newArray[] = $array[$field];
        }

        return $newArray;
    }

    /**
     * 返回数组的键
     * @param bool $toString
     * @return array|string
     */
    public function arrayKeys($toString=false)
    {
        $newArray = [];
        foreach ($this->arrays as $item) {
            $newArray[] = key($item);
        }

        return $toString ? implode(',', $newArray) : $newArray;
    }

    /**
     * 分组查询
     * @param $group
     * @param $callback
     * @param bool $returnGroupKey
     * @return $this
     */
    public function group($group, $callback, $returnGroupKey=true)
    {
        $newArray = [];
        $arrays = $callback($this);
        foreach ($arrays as $item) {
            if(empty($newArray[$item[$group]][$group])){
                $newArray[$item[$group]][] = $item;
            }
        }
        if($returnGroupKey){
            $this->arrays = $newArray;
        }else{
            $this->arrays = array_values($newArray);
        }

        return $this;
    }

    /**
     * 如果给定的 $value 是true，则应用回调的查询更改。
     * @param  mixed  $value
     * @param  callable  $callback
     * @param  callable  $default
     * @return mixed
     */
    public function when($value, $callback, $default = null)
    {
        if($value){
            return $callback($this, $value) ?: $this;
        }elseif($default){
            return $default($this, $value) ?: $this;
        }

        return $this;
    }

    /**
     * 对关联数据进行自定义排序
     * @param array $sortArray
     * @param $sortColumn
     * @return array
     */
    public function sortByKey(array $sortArray, $sortColumn)
    {
        $newArr = [];
        foreach ($sortArray as $sortKey) {
            foreach ($this->arrays as $item) {
                if ($item[$sortColumn] == $sortKey) {
                    $newArr[] = $item;
                }
            }
        }
        $this->arrays = $newArr;
        return $this;
    }

    /**
     * 二维数组排序
     * @param $sort_key
     * @param int $sort_order
     * @param int $sort_type
     * @return array|bool
     */
    public function arraySort($sort_key, $sort_order=SORT_DESC, $sort_type=SORT_NUMERIC )
    {
        if(!empty($this->arrays)){
            foreach ($this->arrays as $array){
                $key_arrays[] = $array[$sort_key];
            }
            if(!empty($key_arrays)){
                array_multisort($key_arrays,$sort_order,$sort_type,$this->arrays);
            }
        }
        return $this->arrays;
    }

    /**
     * 多个条件when
     *
     * @param $params
     * @return $this
     */
    public function whens($params)
    {
        foreach ($params as $param => $value) {
            $this->when($value, function($query)use($param, $value){
                return $query->where($this->arrays, [$param => $value]);
            });
        }

        return $this;
    }


    /**
     * 数组分段 | 将二维数组按指定粒度分段成一个三维数组
     *
     * @param array $array 数组
     * @param int $granularity 粒度数量
     * @return array
     */
    public function array_section($granularity, $array=[])
    {
    		if (empty($array)){
    			$array = $this->arrays;
    		}
        $total_num = count($array);
        if ($total_num < $granularity){
            $arrs[] = $array;
        }else{
            $arrs = [];
            foreach ($array as $k1 => $v1) {
                $arr[$k1] = $v1;
                if ((($k1+1) % $granularity) == 0){
                    $arrs[] = $arr;
                    unset($arr);
                }
                unset($array[$k1]);
                if (count($array) == $total_num % $granularity && !empty($array)){
                    $arrs[] = $array;
                    break;
                }
            }
        }

        return $arrs;
    }
}
