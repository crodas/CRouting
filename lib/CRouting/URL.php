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

class CRouting_URL
{
    const E_STRING   = 0;
    const E_VARIABLE = 0;

    protected $cUrl;
    protected $url;
    protected $rules;
    protected $default;
    protected $requirements;

    public function __construct($url, Array $requirements, Array $defaults)
    {
        $this->url           = $url;
        $this->cUrl          = $this->compileURL($url, $requirements, $defaults);
        $this->default       = $defaults;
        $this->requirements  = $requirements;
        $this->compile();
    }
    
    // toString {{{
    public function __toString()
    {
        return $this->url;
    }
    // }}}

    // compileURL {{{
    /**
     *  Compile a givne URL, returning an array of CRouting_Segement object.
     *
     *  @param string $url
     *  @param array  $rules
     *  @param array  $defaults
     *
     *  @return Array
     */
    protected function compileURL($url, $rules, &$defaults)
    {
        $parts = array();
        foreach ($this->getURIPath($url) as $id => $part) {
            $state  = self::E_STRING;
            $buffer = ""; 
            $length = strlen($part);

            $parts[$id] = new CRouting_Segment($id);

            for($i=0; $i < $length; $i++) {
                switch ($part[$i]) {
                case '\\';
                    $buffer .= $part[++$i];
                    break;
                case '{':
                    if ($state != self::E_STRING) {
                        throw new Exception("Malformed URL part {$part}, unexpected { at position {$i}");
                    }
                    if (empty($buffer) && $i > 0) {
                        throw new Exception("Variables cannot be together, they need to be separated by a constant. Position $i");
                    }
                    if (!empty($buffer)) {
                        $parts[$id]->addToken('constant', $buffer);
                        $buffer  = '';
                    }
                    $state = self::E_VARIABLE;
                    break;
                case '}':
                    if ($state != self::E_VARIABLE) {
                        throw new Exception("Malformed URL part {$part}, unexpected } at position {$i}");
                    }
                    if (empty($buffer)) {
                        throw new Exception("Empty variables not allowed at position $i");
                    }

                    $default = null;
                    $rule    = null;
                    if (isset($defaults[$buffer])) {
                        $default = $defaults[$buffer];
                        unset($defaults[$buffer]);
                    }
                    if (isset($rules[$buffer])) {
                        $rule = $rules[$buffer];
                    }
                    $parts[$id]->addToken('variable', $buffer, $default, $rule);
                    $buffer  = '';
                    $state   = self::E_STRING;
                    break;
                default:
                    $buffer .= $part[$i];
                    break;
                }
            }
            if (!empty($buffer)) {
                if ($state != self::E_STRING) {
                    throw new Exception("Unexpected end {$part}");
                }
                $parts[$id]->addToken('constant', $buffer);
            }
        }

        return $parts;
    }
    // }}}

    // get URIPath {{{
    /**
     *  Split a given URI path into arrays.
     *
     *  @param string $url
     *
     *  @return array
     */
    protected function getURIpath($url) 
    {
        $req   = preg_replace("/^\/+|(\/)+|\?.*/", '$1', $url);
        $parts = explode('/', $req);
        $lexpr  = count($parts) - 1;

        if (empty($parts[$lexpr])) {
            unset($parts[$lexpr]);
        }

        return $parts;
    }
    // }}}

    public function getSize($debug=true)
    {
        $min = $max = 0;
        foreach ($this->cUrl as $token) {
            if (!$token->isOptional()) {
                $min++;
            }
            $max++;
        }
        return compact('min', 'max');
    }

    public function getRule($id)
    {
        return isset($this->rules[$id]) ? $this->rules[$id] : false;
    }

    protected function compile()
    {
        $size = $this->getSize();
        $expr = array();
        for ($i=$size['min']; $i <= $size['max']; $i++) {
            $expr[$i] = $this->compileRule($size['max'] - $i);
        }
        $this->rules = $expr;
    }

    protected function compileRule($skip=0)
    {
        $cur  = 0;
        $expr = array();
        $i    = 0;
        $ret  = array();
        foreach ($this->default as $key => $value) {
            $ret[] = array(PHP::String($key), PHP::String($value));
        }

        if (isset($this->requirements['$method'])) {
            $method    = new CRouting_Requirement($this->requirements['$method']);
            $validator =  PHP::Expr('==', PHP::Variable('hasMethod'), true);
            if (!$method->isString() || !in_array('ALL', $method->getOptions())) {
                // check if the rule doesn't contain the word ALL
                $expr[] = PHP::Expr('==', PHP::Variable('hasMethod'), true);
                $expr[] = $method->getExpr(PHP::Variable('_SERVER', 'REQUEST_METHOD'));
            }
        }

        foreach ($this->cUrl as $id => $segment) {
            if ($cur < $skip && $segment->isOptional()) {
                $token = $segment->getToken(0);
                $ret[] = array(PHP::String($token->getValue()), PHP::String($token->getDefault()));
                $cur++;
            } else {
                $variable = PHP::Variable('parts', $i++);
                $tempExpr = $segment->getValidationExpr($variable, &$ret);
                if ($tempExpr instanceof PHP) {
                    $expr[] = $tempExpr;
                }
            }
        }
        $return = PHP::Exec('return', new PHP_Array($ret));
        if (count($expr)) {
            $base = new PHP_IF(PHP::ExprArray($expr));
            $base->addStmt(new PHP_Comment($this->url));
            $base->addStmt($return);
        } else {
            $base = $return;
        }
        return $base;
    }
}
