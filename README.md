# ClassCache
Concatenates several class definitions into a single file for quick loading.

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
Files defining classes must be free of side effects. Files defining classes may have zero or one namespaces but not more than one namespace.
