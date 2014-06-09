<?php

trait ProtectedCaller {

    /**
     * Helper to call protected methods
     * @param $object
     * @param $method
     * @param array $args
     * @return mixed
     */
    public static function callProtected($object, $method, $args = array())
    {
        $reflectionMethod = new ReflectionMethod(get_class($object), $method);
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invokeArgs($object, $args);
    }

} 