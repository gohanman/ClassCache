<?php

use Gohanman\ClassCache\ClassCache;

class ClassCacheTest extends PHPUnit_Framework_TestCase
{
    public function testCaching()
    {
        include_once(__DIR__ . '/../src/ClassCache.php');
        include_once(__DIR__ . '/GlobalTest.php');
        include_once(__DIR__ . '/NameSpaceTest.php');

        $classes = array('GlobalTest', 'testing\\NameSpaceTest', 'globaltest');

        $c = new ClassCache();
        $out = __DIR__ . '/out.test.php';
        $c->cache($classes, $out);
        $file = file_get_contents($out);
        unlink($out);

        $this->assertEquals(true, strstr($out, "namespace {"));
        $this->assertEquals(true, strstr($out, "namespace testing {"));
        $this->assertEquals(true, strstr($out, "class GlobalTest"));
        $this->assertEquals(true, strstr($out, "class NameSpaceTest"));
    }
}

