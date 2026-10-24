<?php

namespace App\Providers;

use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerFirestore();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Firestore client memakai transport REST.
     *
     * Ekstensi php_grpc di Windows dapat menyebabkan access violation (0xC0000005),
     * sehingga transport REST dipaksa di sini alih-alih gRPC.
     */
    private function registerFirestore(): void
    {
        $this->app->singleton(FirestoreClient::class, function (): FirestoreClient {
            $credentials = (string) config('firebase.projects.app.credentials');

            if (! str_starts_with($credentials, '{') && ! preg_match('#^(?:[A-Za-z]:[\\\\/]|/)#', $credentials)) {
                $credentials = base_path($credentials);
            }

            $factory = (new Factory)
                ->withServiceAccount($credentials)
                ->withFirestoreClientConfig(['transport' => 'rest']);

            return $factory
                ->createFirestore(config('firebase.projects.app.firestore.database'))
                ->database();
        });
    }
}
