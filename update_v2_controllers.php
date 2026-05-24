<?php

// Script to update all V2 controllers to use V2 Resources and Requests

$controllers = [
    'BrandController' => [
        'requests' => ['StoreBrandRequest', 'UpdateBrandRequest'],
        'resources' => ['BrandCollection', 'BrandResource']
    ],
    'UserController' => [
        'requests' => ['StoreUserRequest', 'UpdateUserRequest'],
        'resources' => ['UserCollection', 'UserResource']
    ],
    'ProductController' => [
        'requests' => ['StoreProductRequest', 'UpdateProductRequest'],
        'resources' => ['ProductCollection', 'ProductResource']
    ],
    'OrderController' => [
        'requests' => ['StoreOrderRequest', 'UpdateOrderRequest'],
        'resources' => ['OrderCollection', 'OrderResource']
    ],
];

$basePath = 'app/Http/Controllers/Api/V2/Admin/';

foreach ($controllers as $controller => $config) {
    $filePath = $basePath . $controller . '.php';

    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);

        // Update requests
        foreach ($config['requests'] as $request) {
            $content = str_replace(
                "use App\\Http\\Requests\\V1\\{$request};",
                "use App\\Http\\Requests\\V2\\{$request};",
                $content
            );
        }

        // Update resources
        foreach ($config['resources'] as $resource) {
            $content = str_replace(
                "use App\\Http\\Resources\\V1\\{$resource};",
                "use App\\Http\\Resources\\V2\\{$resource};",
                $content
            );
        }

        // Update OpenAPI paths and tags
        $content = str_replace('/api/v1/', '/api/v2/', $content);
        $content = str_replace('tags={"', 'tags={"', $content); // Will be handled manually

        file_put_contents($filePath, $content);
        echo "Updated: $controller\n";
    }
}

echo "All controllers updated!\n";
?>
