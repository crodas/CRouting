<?php
/*
  +---------------------------------------------------------------------------------+
  | Copyright (c) 2011 César Rodas                                                  |
  +---------------------------------------------------------------------------------+
  | Redistribution and use in source and binary forms, with or without              |
  | modification, are permitted provided that the following conditions are met:     |
  | 1. Redistributions of source code must retain the above copyright               |
  |    notice, this list of conditions and the following disclaimer.                |
  |                                                                                 |
  | 2. Redistributions in binary form must reproduce the above copyright            |
  |    notice, this list of conditions and the following disclaimer in the          |
  |    documentation and/or other materials provided with the distribution.         |
  |                                                                                 |
  | 3. All advertising materials mentioning features or use of this software        |
  |    must display the following acknowledgement:                                  |
  |    This product includes software developed by César D. Rodas.                  |
  |                                                                                 |
  | 4. Neither the name of the César D. Rodas nor the                               |
  |    names of its contributors may be used to endorse or promote products         |
  |    derived from this software without specific prior written permission.        |
  |                                                                                 |
  | THIS SOFTWARE IS PROVIDED BY CÉSAR D. RODAS ''AS IS'' AND ANY                   |
  | EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED       |
  | WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE          |
  | DISCLAIMED. IN NO EVENT SHALL CÉSAR D. RODAS BE LIABLE FOR ANY                  |
  | DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES      |
  | (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;    |
  | LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND     |
  | ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT      |
  | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS   |
  | SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE                     |
  +---------------------------------------------------------------------------------+
  | Authors: César Rodas <crodas@php.net>                                           |
  +---------------------------------------------------------------------------------+
*/

// Exception {{{
/**
 *  Default Exception class
 */
class CRouting_Exception extends Exception
{
}
// }}}

class CRouting
{
    protected $file;
    protected $dir;
    protected $tmp;
    protected $callback;
    protected static $parser;

    public function __construct($file, $dir='/tmp')
    {
        if (!is_file($file)) {
            throw new CRouting_Exception("{$file} must exists");
        }
        if (!is_dir($dir)) {
            throw new CRouting_Exception("{$dir} must be a directory");
        }
        $this->file = $file;
        $this->dir  = $dir;
        if (empty(self::$parser)) {
            self::$parser = 'sfYaml::load';
        }
        $this->callback = 'route' . md5($file);
        $this->tmp      = $dir . '/' . $this->callback . '.php';
        if (!is_callable($this->callback)) {
            if (!is_file($this->tmp) || filemtime($this->tmp) < filemtime($this->file) ) {
                $this->compile();
            }
            require $this->tmp;
        }
    }

    // setParser {{{
    /**
     *  Set Parser function
     *
     *  @param callback $callback
     *
     *  @return void
     */
    public static function setParser($callback)
    {
        if (!is_callable($callback)) 
        {
            throw new CRouting_Exception("\$callback should be a valid callback");
        }
        self::$parser = $callback;
    }
    // }}}

    function generate($type, $args=array())
    {
        return call_user_func($this->callback . 'Build', $type, $args);
    }

    // match {{{
    /**
     *  Public interface to test if a given URL matches
     *  with any of our rules.
     *
     *  @param string $url 
     *
     *  @return bool|array 
     */
    public function match($url)
    {
        return call_user_func($this->callback, $url);
    }
    // }}}

    // compile {{{
    /**
     *  Compile.
     *
     *  Load everything else and compile rule by rule.
     *
     *  @return void
     */
    protected function compile()
    {
        static $loaded = false;
        if (!$loaded) {
            /* load everything, don't relay on any autoloader */
            $base = dirname(__FILE__);
            if (self::$parser == 'sfYaml::load') {
                require_once $base . '/vendor/sfYaml.php';
            }
            require_once $base . '/CRouting/URL.php';
            require_once $base . '/CRouting/Segment.php';
            require_once $base . '/CRouting/Token.php';
            require_once $base . '/PHP.php';
            require_once $base . '/PHP/Generator.php';
            $loaded = true;
        }  
        $data = call_user_func(self::$parser, $this->file);

        if (!is_array($data)) {
            throw new CRouting_Exception('Parser function must return an array');
        }

        $data   = array_reverse($data);
        $rules  = array();
        $method = false;
        foreach ($data as $name => $def) {
            $def['name'] = $name;

            $url  = new CRouting_URL($def);
            $size = implode("_", $url->getSize());

            if (!isset($rules[$size])) {
                $rules[$size] = array();
            }
            //$rules[] = new PHP_Comment($def['pattern']);
            $rules[$size][] = $url->getMatchCode();

            /* do we ever need to check method ? */
            $method |= $url->requireMethodChecking();
        }

        /* create match function {{{ */
        $matchFunction = new PHP_Function($this->callback, array(PHP::Variable('url')), array());
        $matchFunction->addStmt(new PHP_Comment($this->file));

        /* clean up URL */
        $vlength = PHP::Variable('length');
        $vcurl   = PHP::Variable('curl');
        $matchFunction->addStmt(PHP::Assign($vcurl,   PHP::Exec('preg_replace', "/(\/)+|\?.*/", '$1', PHP::Variable('url'))));
        $matchFunction->addStmt(PHP::Assign($vlength, PHP::Exec('substr_count', PHP::Variable('curl'), PHP::String('/'))));

        $if = new PHP_If(PHP::Expr('==', PHP::Exec('substr', $vcurl, -1), PHP::String('/')));
        $if->addStmt(PHP::Assign($vlength, PHP::Expr('-', $vlength, 1)));

        $matchFunction->addStmt($if);

        //$matchFunction->addStmt(PHP::Exec('var_dump', $vlength, $vcurl, PHP::Variable('url')));
        
        if ($method) {
            /* check if the request_method is set, if not, set it to empty to avoid warnings  */
            $method = PHP::Assign('hasMethod', PHP::Exec('isset', PHP::Variable('_SERVER', 'REQUEST_METHOD')));
            $matchFunction->addStmt($method);
        }

        foreach ($rules as $size => $zrules) {
            list($min, $max) = explode('_', $size);
            if ($min == $max) {
                $expr = PHP::Expr('==', $vlength, $min);
            } else {
                $expr = PHP::ExprArray(array(
                        PHP::Expr('>=', $vlength, $min),
                        PHP::Expr('<=', $vlength, $max)
                ));
            }
            $if = new PHP_If($expr);
            foreach ($zrules as $rule) {
                $if->addStmt($rule);
            }
            $matchFunction->addStmt($if);
        }

        /* }}} */

        $matchFunction->addStmt(PHP::Exec('return', false));

        // array to URL function {{{
        $createFunction = new PHP_Function($this->callback . 'Build', array(PHP::Variable('name'), PHP::Variable('parts')));
        $createFunction->addStmt(new PHP_Comment('array to URL'));
        // }}}

        /* improve it later, to avoid concurrency issues (look at Haanga) */
        $code = "<?php\n" . $matchFunction . "\n" . $createFunction;
        file_put_contents($this->tmp, $code, LOCK_EX);
    }
    /* }}} */

}
