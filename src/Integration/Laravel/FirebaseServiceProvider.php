<?php namespace Firebase\Integration\Laravel;

use Illuminate\Support\ServiceProvider;

class FirebaseServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind('firebase', function ($app) {

            /** @var \Illuminate\Contracts\Config\Repository $config */
            $config = $app['config'];

            /** @var \Illuminate\Contracts\Foundation\Application $app */
            return $app->make('Firebase\Firebase', [
                $config->get('services.firebase'),
                $app->make('GuzzleHttp\Client')
            ]);

        }, true);
    }

}