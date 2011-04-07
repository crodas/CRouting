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
 *
 */
class CRounting_Token
{
    const CONSTANT = 1;
    const VARIABLE = 2;
    const INT = 3;

    protected $value;
    protected $type;
    protected $default;
    protected $rule;

    public function __construct($value, $type)
    {
        if ($type != 'variable' && $type != 'constant') {
            throw new Exception('Token type must be variable or constant');
        }
        $this->value = $value;
        $this->type  = $type == 'constant' ? self::CONSTANT : self::VARIABLE;
    }

    public function setDefault($default)
    {
        $this->default = $default;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function setRule($rule)
    {
        /* improve this */
        if ($rule == '\d+') {
            $value = self::INT;
        } else {
            $value = explode('|', $rule);
        }
        $this->rule = $value;
    }

    public function getValidation($variable) 
    {
        if (empty($this->rule)) {
            return false;
        }
        $logic = array();
        if (is_array($this->rule)) {
            foreach ($this->rule as $val) {
                $logic = array_merge($logic, array(PHP::Operator('=='), $variable, PHP::String($val), PHP::Operator('OR')));
            }
            array_pop($logic);
        } else if ($this->rule == self::INT) {
            $logic = PHP::Exec('is_numeric', $variable);
        } else {
            throw new Exception("Unkown validation");
        }
        return new PHP_Expr($logic);
    }

    public function getType()
    {
        return $this->type;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function isConstant()
    {
        return $this->type == self::CONSTANT;
    }

    public function hasRule() 
    {
        return !empty($this->rule);
    }

    public function isVariable()
    {
        return $this->type == self::VARIABLE;
    }

    public function isOptional()
    {
        return $this->isVariable() && !is_null($this->default);
    }

}
