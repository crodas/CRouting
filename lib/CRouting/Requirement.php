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

/**
 *  Simple Requirement Class
 *
 *  @todo Support for complex Regular expressions
 *  @todo Callback support
 *
 */
class CRouting_Requirement
{   
    protected $content;
    protected $type;
    protected $options;

    public function __construct($requirement)
    {
        $this->parse($requirement);
    }

    protected function parse($requirement)
    {
        $this->content = $requirement;
        if ($requirement == '\d+') {
            $this->options = '/^\d+$/';
            $this->type = 'regex';
        } else if (is_string($requirement)) {
            if ($requirement[0] == substr($requirement, -1) && $requirement[0] == '/') {
                $this->parse(array('regex' => $requirement));
                return;
            }
            $this->type    = 'string';
            $this->options = explode("|", $requirement);
        } else if (is_array($requirement) && isset($requirement['callback'])) {
            if (!is_callable($requirement['callback'])) {
                throw new CRouting_Exception('Invalid callback  ' . print_r($requirement['callback'], true)) ;
            }
            $this->type    = 'callback';
            $this->options = $requirement['callback'];
        } else if (is_array($requirement)) {
            if (isset($requirement['regex'])) {
                $this->type    = 'regex';
                $this->options = $requirement['regex'];
            }
            if (isset($requirement['type'])) {
                switch  ($requirement['type']) {
                case 'int':
                case 'integer':
                    $this->type = $requirement['type'];
                    break;
                }
            }
        }
        
        if (empty($this->type)) {
            throw new CRouting_Exception('Dont know how to parse requirement ' . print_r($requirement, true));
        }
    }

    public function isString()
    {
        return $this->type == 'string';
    }

    public function getOptions()
    {
        return $this->options;
    }

    protected function validateAsInt($variable, $strict=true) {
        $useFilter = false;
        if ($strict && is_callable('filter_list')) {
            $useFilter = in_array('int', filter_list());
        }
        if ($useFilter) {
            $expr = PHP::Expr('!==', false, PHP::Exec('filter_var', $variable, new PHP_Constant('FILTER_VALIDATE_INT')));
        } else {
            /* use is_numeric, but it will also validate float */
            $expr = PHP::Expr('===', false, PHP::Exec('strpos', $variable, '.'), 'AND', PHP::Exec('is_numeric', $variable));
        }
        return $expr;
    }

    public function getExpr($variable)
    {
        $expr = null;
        switch ($this->type) {
        case 'callback':
            $expr = PHP::Exec($this->options, $variable);
            break;
        case 'regex':
            switch ($this->options) {
            case '/^[a-zA-Z][a-zA-Z0-9]+$/':
                if (is_callable('ctype_alpha') && is_callable('ctype_alnum')) {
                    $first = clone $variable;
                    $first->addIndex(0);
                    $expr = PHP::Expr('AND', PHP::Exec('ctype_alnum', $variable), PHP::Exec('ctype_alpha', $first));
                } 
                break;
            case '/^[a-zA-Z]+$/':
            case '/^[A-Za-z]+$/':
                if (is_callable('ctype_alpha')) {
                    $expr = PHP::Exec('ctype_alpha', $variable);
                } 
                break;
            case '/^\d+$/':
            case '/^[0-9]+$/':
                $expr = $this->validateAsInt($variable, false);
                break;
            }
            if ($expr === null) {
                $expr = PHP::Exec('preg_match', $this->options, $variable);
            }
            break;
        case 'integer':
        case 'int':
            $expr = $this->validateAsInt($variable);
            break;
        case 'string':
            $tmp = array();
            foreach($this->options as $value) {
                $tmp[] = PHP::Expr('==', $value, $variable);
            }
            $expr = PHP::ExprArray($tmp, 'OR');
            break;
        }
        return $expr;
    }

}
