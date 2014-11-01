<?php
/**
 * Created by PhpStorm.
 * User: Eelke
 * Date: 3-6-14
 * Time: 15:10
 */

namespace Firebase;


trait Configurable {

    protected $config = array();

    /**
     * Set all configuration options at once
     * @param array $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->config = $options;
        return $this;
    }

    /**
     * Merge all configuration options at once
     * @param array $options
     * @return $this
     */
    public function mergeOptions($options)
    {
        $this->config = array_merge($this->config, $options);
        return $this;
    }

    /**
     * Get all configuration options at once
     * @return array
     */
    public function getOptions()
    {
        return $this->config;
    }

    /**
     * Setter for individual configuration option
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setOption($key, $value)
    {
        $this->config[$key] = $value;
        return $this;
    }

    /**
     * Getter for individual configuration option
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getOption($key, $defaultValue = null)
    {
        if(!isset($this->config[$key])) {
            return $defaultValue;
        }
        return $this->config[$key];
    }

} 