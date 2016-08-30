# ClassCache
PHP class definition caching

# Usage
```php
use Gohanman\ClassCache\ClassCache;

$c = new ClassCache();
$classes = array(
    'Some\\Class',
    'Some\\Other\\Class',
    'GlobalClass',
);
$cachefile = 'cache.php';
$c->cache($classes, $cachefile);
```

# Limitations
Files definition classes must be free of side effects
