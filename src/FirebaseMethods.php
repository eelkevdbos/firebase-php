<?php namespace Firebase;


interface FirebaseMethods {

    public function set($path, $value);

    public function get($path, Criteria $criteria = null);

    public function push($path, $value);

    public function update($path, $value);

    public function delete($path);

} 