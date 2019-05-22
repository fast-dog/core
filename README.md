<p align="center">
  <img  src="/assets/img/fast-dog.png">
</p>

## Core for FastDog Cms

## Add service provider

```
FastDog\Core\CoreServiceProvider::class
```

## Publish config and assets

``` 
php artisan vendor:publish --provider="FastDog\Core\CoreServiceProvider" --tag=config
php artisan vendor:publish --provider="FastDog\Core\CoreServiceProvider" --tag=public
```