<?php
/*
  +---------------------------------------------------------------------------------+
  | Copyright (c) 2010 César Rodas and Menéame Comunicacions S.L.                   |
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

/**
 *  Pretty simple set of classes which helps to generate code in a
 *  programmatic way.
 *
 *  It uses the Visitor Pattern: http://en.wikipedia.org/wiki/Visitor_pattern
 */

/**
 *  Base Abstraction
 */
abstract class PHP 
{
    protected $line;
    protected $attrs;
    protected $nodes;
    protected $parent;

    protected static $generator;

    public function __construct($nodes, $attrs=array(), $line=0)
    {
        if (!is_array($nodes)) {
            $nodes = array($nodes);
        }
        if (!is_array($attrs)) {
            $attrs = array($attrs);
        }

        // add reference to parent node
        foreach ($nodes as $node) {
            if ($node instanceof PHP) {
                $node->parent = $this;
            }
        }

        $this->nodes = $nodes;
        $this->attrs = $attrs;
        $this->line  = $line;
    }

    public function replaceAttributes($find, $replace) {
        $that = clone $this;
        foreach($that->attrs as $id => $value) { 
           if ($value === $find) {
                $that->attrs[$id] = $replace;
                continue;
            }
            if ($value instanceof PHP) {
                $newobj = clone $value;
                $that->attrs[$id] = $newobj->replaceAttributes($find, $replace);
            }
        }
        return $that;
    }

    public function getAttributes()
    {
        return $this->attrs;
    }

    public function setAttribute($obj)
    {
        $this->attrs = $obj;
    }

    public function addAttribute($obj)
    {
        $this->attrs[] = self::convertNative($obj);
    }

    public function setNodes($nodes)
    {
        $this->nodes = $nodes;
    }

    public function addNode(PHP $node)
    {
        $this->nodes[] = $node;
    }

    public function getNodeSize()
    {
        return count($this->nodes);
    }

    /**
     *  Get Parent node.
     *
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function getType()
    {
        return substr(get_class($this), strlen(__CLASS__) +1);
    }

    public function setLine($line)
    {
        $this->line = $line;
    }

    final public function __toString()
    {
        if (empty(self::$generator)) {
            self::$generator = new PHP_Generator;
        }
        $generator = self::$generator;

        $class = 'generate' . $this->getType();

        $code = $generator->$class($this->getAttributes(), $this->nodes);

        return $code;
    }

    public static function convertNative($value)
    {
        if ($value instanceOf PHP) {
            return $value;
        } 
        
        if (is_numeric($value)) {
            $value = new PHP_Number($value);
        } else if (is_string($value)) {
            switch ($value) {
            case '+': case '++': case '-': 
            case '*': case '/': case '%':
            case '==': case '!=': case '===': case '!==':
            case '>': case '<': case '>=': case '<=':
            case '?':
            case 'AND': case 'OR':
                return new PHP_Operator($value);
            }
            $value = new PHP_String($value);
        } elseif ($value === true || $value === false) {
            return new PHP_Bool($value);
        } else {
            throw new Exception("Don't know how to convert {$value}");
        }
        return $value;
    }

    public static function operator($param) {
        return new PHP_Operator($param);
    }

    public static function Expr()
    {
        return new PHP_Expr(func_get_args());
    }

    public static function zArray($array)
    {
        $obj = new PHP_Array;
        foreach ($array as $key => $value) {
            $obj->addMember($key, $value);
        }
        return $obj;
    }

    public static function ExprArray($array, $operation='AND') {
        $tmp = array();
        foreach (array_chunk($array,2) as $rule) {
            $tmp[] = $rule[0];
            $tmp[] = $operation;
            if (!empty($rule[1])) {
                $tmp[] = $rule[1];
                $tmp[] = $operation;
            }
        }
        // remove the last $operator
        array_pop($tmp);
        return new PHP_Expr($tmp);
    }

    public static function Exec($name)
    {
        $args = func_get_args();
        array_shift($args);
        return new PHP_Exec($name, new PHP_StmtList($args));
    }

    public static function String($name)
    {
        return new PHP_String($name);
    }

    public static function Bool($name)
    {
        return new PHP_Bool($name);
    }

    public static function Assign($var, $expr)
    {
        if (!$var instanceof PHP_Variable) {
            $var = new PHP_Variable($var);
        }
        return new PHP_Assign($var, $expr);
    }

    public static function Variable() {
        return new PHP_Variable(func_get_args());
    }

}

abstract class PHP_Simple extends PHP
{
    public function __construct($name, $line=0)
    {
        parent::__construct(array(), array($name), $line);
    }
}

/**
 *  Abstraction for Basic nodes (those which requires 
 *  just one text parameter).
 *
 */
abstract class PHP_BlockSimple extends PHP
{
    public function __construct($text, $line=0)
    {
        if (!is_array($text)) {
            $text = array($text);
        } else {
            foreach ($text as &$value) {
                $value = self::convertNative($value);
            }
        }
        parent::__construct(array(), $text, $line);
    }
}

/**
 *  Abstraction for Basic nodes with one parameter
 *  and nodes.
 *
 */
abstract class PHP_Blocks extends PHP
{
    function __construct($expr, $stmts=array(), $line=0)
    {
        parent::__construct($stmts, $expr, $line);
    }

    public function setBody(Array $stmts)
    {
        $this->nodes = $stmts;
    }

    public function addStmt(PHP $stmt)
    {
        $this->nodes[] = $stmt;
    }
}

final class PHP_Bool extends PHP_BlockSimple
{
}

final class PHP_Number extends PHP_BlockSimple
{
}

final class PHP_Constant extends PHP_BlockSimple
{
}

final class PHP_String extends PHP_BlockSimple
{
    function append($part) 
    {
        $this->attrs[] = $part;
    }
}

final class PHP_Comment extends PHP_BlockSimple
{
}

final class PHP_Operator extends PHP_BlockSimple 
{
}

final class PHP_StmtList extends PHP_BlockSimple
{
    /**
     *  Push an Node into the StmtList
     */
    public function push(PHP $param)
    {
        $this->attrs[] = $param;
    }
}

final class PHP_Property extends PHP_Simple
{
}

final class PHP_doReturn extends PHP_Simple
{
}


final class PHP_Variable extends PHP
{
    public function __construct($def="", $line=0)
    {
        if (is_string($def)) {
            $def = array($def);
        } else {
            foreach ($def as $id => $value) {
                if ($id == 0 || $value InstanceOf PHP) {
                    continue;
                }

                $def[$id] = PHP::convertNative($value);
            }
        }
        parent::__construct(array(),  $def, $line);
    }

    public function addIndex($index) 
    {
        $this->attrs[] = PHP::convertNative($index);
        return $this;
    }
}

final class PHP_Assign extends PHP
{
    public function __construct(PHP_Variable $a, $b, $line=0)
    {
        if (!$b instanceof PHP) {
            $b = self::convertNative($b);
        }
        parent::__construct(array(), array($a, $b), $line);
    }
}

final class PHP_Print extends PHP_Simple
{
}

final class PHP_Expr extends PHP
{
    function __construct($expr = array(), $line=0)
    { 
        if (!is_array($expr)) {
            $expr = array($expr);
        }
        foreach ($expr as &$value) {
            if (!$value instanceof PHP) {
                $value = PHP::convertNative($value);
            }
        }
        parent::__construct(array(), $expr, $line);
    }
}

final class PHP_Function extends PHP_Blocks
{
    function __construct($name, $args=null, $stmts=array(), $line = 0)
    {
        if (is_array($args)) {
            $args = new PHP_StmtList($args);
        }
        parent::__construct(array($name, $args), $stmts, $line);
    }
}

final class PHP_If extends PHP_Blocks 
{
}

final class PHP_Else extends PHP_Blocks 
{
}

final class PHP_Exec extends PHP
{
    function __construct($name, $args=null, $line = 0)
    {
        if (is_array($args)) {
            $args = new PHP_StmtList($args);
        } else if (!is_object($args) || !$args InstanceOf PHP_StmtList) {
            throw new Exception("\$args should be array or PHP_StmtList");
        }

        parent::__construct(array(), array($name, $args), $line);
    }

    function addParameter(PHP $param)
    {
        if (is_null($this->attrs[1])) {
            $this->attrs[1] = new PHP_StmtList(array());
        }
        $this->attrs[1]->push($param);
    }
}

final class PHP_Case extends PHP_Blocks
{
    function __construct($pattern, $code = array(), $line=0)
    {
        parent::__construct(self::convertNative($pattern), $code, $line);
    }
}

final class PHP_Switch extends PHP
{
    function __construct(PHP $expr, $line=0)
    {
        parent::__construct(array(), array($expr), $line);
    }

    function addCase(PHP_Case $case)
    {
        $this->nodes[] = $case;
    }
}

final class PHP_Array extends PHP
{
    function __construct($args=null, $line = 0)
    {
        if (!is_array($args)) {
            $args = array();
        } 

        parent::__construct(array(), $args, $line);
    }

    function addMember($key, $value)
    {
        $this->attrs[] = array(self::convertNative($key), self::convertNative($value));
    }
}

final class PHP_BuiltIn extends PHP 
{
    function __construct($name, PHP_StmtList $args=null, $line = 0)
    {
        parent::__construct(array(), array($name, $args), $line);
    }
}


final class PHP_Foreach extends PHP_Blocks {
    function __construct(PHP_Variable $array, $key, PHP_Variable $value, array $body = array(), $line = 0)
    {
        if (!is_null($key) && !$key instanceof PHP_Variable) {
            throw new Exception("\$key must be an instace of PHP_Variable");
        }
        parent::__construct(array($array, $key, $value), $body, $line);
    }
}

final class PHP_Class extends PHP_Blocks
{
    public function __construct($name, $stmts, $line=0)
    {
        parent::__construct(array($name), $stmts, $line);
    }
}

