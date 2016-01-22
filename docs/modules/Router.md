# Router

The framework is bundled with a neat router. Unlike different PHP frameworks, WordPress is always bundled with a theme the user has selected. We've thought of this and made a router which uses the template the user has for pages in their theme.

This way you'll have no problem hooking in your own routes with the user's theme, not needing to worry about theme compatibility.

## Usage

The router will not work unless you tell the framework to make it, and instantiate it.

```php
$myFramework->make('router')->init();
```

Now you can define your routes like so.

```php
add_action('anunaframework_routes', function($router) {
    $router->get('/hello-world/', function($router) {
        $router->setTitle('Hello World');
        return $router->view('hello-world.twig');
    });
});
```

### Using a controller

You could also use a controller method as the function.

```php
$router->get('/hello-world/', 'HelloWorldController@index');
```

In your controller method.

```php
...
    public function index() {
        $this->router->setTitle('Hello World');
        return $this->view('hello-world.twig');
    }
...
```
