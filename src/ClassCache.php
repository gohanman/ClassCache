<?php

namespace Gohanman\ClassCache;
use \ReflectionClass;
use \RuntimeException;

class ClassCache
{
    const BRACE_NS = 1;
    const SEMICOLON_NS = 2;
    const UNKNOWN_NS = -1;

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
        $tokens = token_get_all('<?php ' . $def);
        $numNS = $this->countNamespaces($tokens);
        if ($numNS > 1) {
            // class isn't cacheable
            return '';
        }
        if ($numNS == 0) {
            $def = "\nnamespace {\n"
                . $def
                . "\n}\n";
        } elseif ($numNS == 1 && $this->typeOfNamespace($tokens) == self::SEMICOLON_NS) {
            $def = preg_replace('/\s*namespace\s+(.*)\s*;/', "namespace $1 {", $def);
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

        return $files;
    }

    private function countNamespaces($tokens)
    {
        $count = 0;
        foreach ($tokens as $t) {
            if (is_array($t) && $t[0] == T_NAMESPACE) {
                $count++;
            }
        }

        return $count;
    }

    private function typeOfNamespace($tokens)
    {
        for ($i=0; $i<count($tokens); $i++) {
            if (is_array($tokens[$i]) && $tokens[$i][0] == T_NAMESPACE) {
                $j = $i+1;
                while (is_array($tokens[$j]) && ($tokens[$j][0] == T_WHITESPACE || $tokens[$j][0] == T_STRING)) {
                    $j++;
                }
                if ($tokens[$j] == ';') {
                    return self::SEMICOLON_NS;
                } elseif ($tokens[$j] == '{') {
                    return self::BRACE_NS;
                }
                break;
            }
        }

        return self::UNKNOWN_NS;
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

