<?php namespace Firebase\Integration\Laravel;

use Illuminate\Support\Facades\Facade;

class Firebase extends Facade {

    public static function getFacadeAccessor()
    {
        return 'firebase';
    }

} 