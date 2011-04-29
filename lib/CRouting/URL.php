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
    const E_VARIABLE = 1;

    protected $cUrl;
    protected $url;
    protected $default;
    protected $requirements;

    protected $match;
    protected $generator;

    public function __construct(Array $definition)
    {
        foreach (array('pattern', 'name') as $check) {
            if (empty($definition[$check])) {
                throw new CRouting_Exception('Missing ' . $check . ' or it is empty');
            }
        }

        foreach (array('requirements', 'defaults', 'optional') as $optional) {
            if (empty($definition[$optional])) {
                $definition[$optional] = array();
            }
        }

        $this->name         = $definition['name'];
        $this->url          = $definition['pattern'];
        $this->default      = $definition['defaults'];
        $this->requirements = $definition['requirements'];
        $this->optional     = $definition['optional'];
        $this->cUrl         = $this->compilePattern();
        $this->compileMatch();
        $this->compileGenerator();
    }
    
    // toString {{{
    public function __toString()
    {
        return $this->url;
    }
    // }}}

    // compilePattern {{{
    /**
     *  Compile a given URL, returning an array of CRouting_Segement object.
     *
     *
     *  @return Array
     */
    protected function compilePattern()
    {
        $parts = array();
        foreach ($this->getURIPath($this->url) as $id => $part) {
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
                        throw new CRouting_Exception("Malformed URL part {$part}, unexpected { at position {$i}");
                    }
                    if (empty($buffer) && $i > 0) {
                        throw new CRouting_Exception("Variables cannot be together, they need to be separated by a constant. Position $i");
                    }
                    if (!empty($buffer)) {
                        $optional = in_array($buffer, $this->optional);
                        $parts[$id]->addToken('constant', $buffer, null, null, $optional);
                        $buffer  = '';
                    }
                    $state = self::E_VARIABLE;
                    break;
                case '}':
                    if ($state != self::E_VARIABLE) {
                        throw new CRouting_Exception("Malformed URL part {$part}, unexpected } at position {$i}");
                    }
                    if (empty($buffer)) {
                        throw new CRouting_Exception("Empty variables not allowed at position $i");
                    }

                    $default  = null;
                    $rule     = null;
                    if (isset($this->default[$buffer])) {
                        $default = $this->default[$buffer];
                        unset($this->default[$buffer]);
                    }
                    if (isset($this->requirements[$buffer])) {
                        $rule = $this->requirements[$buffer];
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

            if ($state != self::E_STRING) {
                throw new CRouting_Exception("Unexpected end {$part}");
            }

            if (!empty($buffer)) {
                $optional = in_array($buffer, $this->optional);
                $parts[$id]->addToken('constant', $buffer, null, null, $optional);
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

    // getSize() {{{
    /**
     *  Get the size of the current URL. This
     *  return the minimun and maximun number of 
     *  segments that are evaluated for this URL.
     *
     *  @return array
     */
    public function getSize()
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
    // }}}

    // getMatchCode {{{
    /**
     *  Return the PHP object representing the current URL
     *  checking of a given segment checking.
     *
     *  @param int $length Current length
     *
     *  @return PHP
     */
    public function getMatchCode()
    {
        return $this->match;
    }
    // }}}

    // requireMethodChecking {{{
    /**
     *  Check if the current URL relies on request
     *  method checking.
     *
     *  @return bool
     */
    public function requireMethodChecking()
    {
        return isset($this->requirements['$method']);
    }
    // }}}

    // compileMatch() {{{
    /**
     *  Compile matching rules to match or not the current URL pattern
     *
     *  @return void
     */
    protected function compileMatch()
    {
        $expr   = array();
        $return = array(); 
        $size   = $this->getSize();

        if ($this->requireMethodChecking()) {
            $validator = PHP::Expr('==', PHP::Variable('hasMethod'), true);
            if (strpos($this->requirements['$method'], 'ALL')===false) {
                // check if the rule doesn't contain the word ALL
                $expr[] = PHP::Expr('==', PHP::Variable('hasMethod'), true);
                $expr[] = PHP::Exec('preg_match', '~' . $this->requirements['$method'] . '~', PHP::Variable('_SERVER', 'REQUEST_METHOD'));
            }
        }

        foreach ($this->default as $name => $value) {
            $return[$name] = $value;
        }


        $regex = "~^";
        foreach ($this->cUrl as $id => $segment) {
            if ($segment->isOptional()) {
                $regex .= '(';
            }
            $regex .= "/";
            $regex .= $segment->getValidationExpr();
            if ($segment->isOptional()) {
                $regex .= ')?';
            }
            foreach($segment->getAll() as $token) {
                if ($token->isVariable()) {
                    $id  = $token->getValue();
                    $ret = PHP::Variable('match', $id);
                    if ($token->isOptional()) {
                        $return[$id] = PHP::Expr('?', PHP::Exec('empty', $ret), PHP::String($token->getDefault()), $ret);
                    } else {
                        $return[$id] = $ret;
                    }
                }
            }
        }
        $regex .= "/?$~";
        $expr[] = PHP::Exec('preg_match', $regex, PHP::Variable('curl'), PHP::Variable('match'));

        $match  = new PHP_IF(PHP::ExprArray($expr));
        $match->addStmt(PHP::Exec('return', PHP::zArray($return)));

        $this->match = $match;
    }
    // }}}


    protected function compileGenerator()
    {
        $check = array();
        $code  = array();

        foreach ($this->cUrl as $segment) {
            foreach ($segment->getVariables() as $var) {
                $varName = PHP::Variable('parts', $var->getValue());
                if (!$var->isOptional()) {
                    $check[] = PHP::Exec('empty', $varName);
                } else {
                    $defIf = new PHP_If(PHP::Exec('empty', $varName));
                    $defIf->addStmt(PHP::Assign($varName, PHP::String($var->getDefault())));
                    $code[] = $defIf;
                }
            }
        }

        if (count($check) > 0) {
            $if = new PHP_If(PHP::ExprArray($check, 'OR'));
            $if->addStmt(PHP::Exec('return', false));
            array_unshift($code, $if);
        }

        $url = new PHP_String('');
        if (count($this->cUrl) == 0) {
            $url->append('/');
        }
        foreach ($this->cUrl as $segment) {
            $url->append('/');
            foreach ($segment->getAll() as $token) {
                if ($token->isVariable()) {
                    $url->append(PHP::Variable('parts', $token->getValue()));
                } else {
                    $url->append($token->getValue());
                }
            }
        }

        $code[] = PHP::Exec('return', $url);
        $this->generator = $code;
    }

}
