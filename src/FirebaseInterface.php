<?php namespace Firebase;


interface FirebaseInterface {

    public function set($path, $value);

    public function get($path);

    public function push($path, $value);

    public function update($path, $value);

    public function delete($path);

} 