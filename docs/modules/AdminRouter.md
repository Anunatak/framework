# Admin Router

We've also got something called, in lack of a better name, Admin Router. This simply is a convenient way of creating admin pages.

# Usage

First we'll create a controller.

```php
namespace MyPluginNamespace\Controllers;

use Anunatak\Framework\Http\Controller;

class MySettingsController extends Controller {

    protected $type = 'admin';
    protected $admin_page = array(
        'page_title' => 'My Plugin',
        'menu_title' => 'My Plugin Settings',
        'capability' => 'edit_posts',
        'menu_slug' => 'settings',
        'icon_url' => '',
        'position' => 30
    );

}
```

Then we tell WordPress to spin up a admin page.

```php
$myFramework->make('admin_router')->init();

add_action('anunaframework_admin_routes', function($router) {
    $router->add('Controllers\MySettingsController');
});
```

More to come!
