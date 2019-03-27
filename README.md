## Core for FastDog Cms

<p style="text-align:center">

![](assets/css/fast-dog.png)

</p>

#####Add service provider
    
    FastDog\Core\CoreServiceProvider::class
    
#####Publish config and assets
    
    php artisan vendor:publish --tag=config
    php artisan vendor:publish --tag=public