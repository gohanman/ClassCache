<?php

namespace Gohanman\ClassCache;
use \ReflectionClass;
use \RuntimeException;

class ClassCache
{
    /**
      Get definitions for several classes and write them out to
      a single file
      @param $classes [array] of class names or instantiated objects
      @param $cachefile [string] path to write definitions
    */
    public function cache($classes, $cachefile)
    {
        $fp = fopen($cachefile, 'w');
        if ($fp === false) {
            throw new RuntimeException("Cannot access cache file: {$cachefile}");
        }
        fwrite($fp, "<?php\n");
        foreach ($this->filesFromNames($classes) as $file) {
            $def = file_get_contents($file);
            if ($def === false) {
                throw new RuntimeException("Cannot access class file: {$file}");
            }
            fwrite($fp, $this->rewriteDefinition($def));
        }
        fclose($fp);
    }

    /**
      Strip PHP tags and use brackets for namespacing
    */
    private function rewriteDefinition($def)
    {
        $def = str_replace('<?php', '', $def);
        $def = str_replace('?>', '', $def);
        if (!preg_match('/\s*namespace\s+/', $def)) {
            $def = "\nnamespace {\n"
                . $def
                . "\n}\n";
        } elseif (preg_match('/\s*namespace\s+(.*);/', $def, $matches)) {
            $def = preg_replace('/\s*namespace\s+.*;/', "namespace {$matches[1]} {", $def);
            $def .= "\n}\n";
        }

        return $def;
    }

    /**
      Get files defining classes from class names
    */
    private function filesFromNames($classes)
    {
        $files = array();
        foreach ($this->dedupeNames($classes) as $class) {
            $ref = new ReflectionClass($class);
            $files[] = $ref->getFileName();
        }
    }

    /**
      Filter out duplicate class names
    */
    private function dedupeNames($names)
    {
        $ret = array();
        $lcase = array();
        foreach ($names as $name) {
            if (!in_array(strtolower($name), $lcase)) {
                $ret[] = $name;
                $lcase[] = strtolower($name);
            }
        }

        return $ret;
    }
}

