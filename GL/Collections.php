<?php

namespace GL;

/**
 * Class Collections 聚合查询 集合类
 * @package GL
 */
class Collections{

    public $arrays;

    /**
     * 根据指定条件返回数据
     * 传参示例 where($array, ['id'=>1, ['value', '>=', 10]])
     * @param array $array
     * @param array $condition
     * @return $this
     */
    public function where($array=[], $condition=[])
    {
        $newArray = [];
        foreach($array as $item){
            $flag = 1;
            foreach($condition as $k => $c){
                if (is_array($c)) {
                    $flag = $this->conditions($item, $c);
                }else{
                    if($item[$k] != $c){
                        $flag = 0;
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
        $sum = 0;
        foreach($this->arrays as $item){
            $sum += $item[$field];
        }

        return $sum;
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
            case 'like' || 'LIKE':
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
            case '<>' || '!=':
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
}
