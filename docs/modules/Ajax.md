# Ajax

Ajax responses are made very simple in this framework.

## Usage

Each response is paired with a `Request` object, which comes from the Symfony Http Foundation package. This makes it really easy to handle your ajax responses.

```php
$myFramework->make('ajax')->create('my_ajax_action', function($request) {
    $var = $request->get('my_var');
    if($var) {
        echo $var;
    }
});
```

It's that simple. No need to `exit` or `die` your responses. The Ajax module takes care of all that for you, in addition to adding the appropriate actions for ajax requests. The above code would look something like this in "vanilla" WordPress.

```php
function my_ajax_response() {
    $var = isset($_POST['my_var']) ? $_POST['my_var'] : false;
    if($var) {
        echo $var;
    }
    die();
}
add_action('wp_ajax_my_ajax_action', 'my_ajax_response');
add_action('wp_ajax_nopriv_my_ajax_action', 'my_ajax_response');
```