<?php namespace Firebase;

/**
 * Class Criteria
 *
 * Possible params:
 *  Limit Queries: limitToFirst, limitToLast
 *  Range Queries: startAt, endAt, equalTo
 *
 * Important: Before using Criteria, make sure you define the keys you use in the .indexOn rule
 *
 * @package Firebase
 */
class Criteria
{

    /**
     * @var string
     */
    protected $orderBy;

    /**
     * @var array
     */
    protected $params;

    /**
     * @param $orderBy
     * @param array $params
     */
    public function __construct($orderBy, $params = [])
    {
        $this->orderBy = static::addDoubleQuotes($orderBy);
        $this->params = $params;
    }

    /**
     * @param array $params
     * @return static
     */
    public static function orderByKey($params = [])
    {
        return new static('$key', $params);
    }

    /**
     * @param array $params
     * @return static
     */
    public static function orderByValue($params = [])
    {
        return new static('$value', $params);
    }

    /**
     * @param array $params
     * @return static
     */
    public static function orderByPriority($params = [])
    {
        return new static('$priority', $params);
    }

    /**
     * @return string
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Adds double quotes where necessary
     * @param $input
     * @return string
     */
    public static function addDoubleQuotes($input)
    {
        $output = $input;

        if (substr($input, 0, 1) !== '"') {
            $output = '"' . $output;
        }

        if (substr(strrev($input), 0, 1) !== '"') {
            $output = $output . '"';
        }

        return $output;
    }

}