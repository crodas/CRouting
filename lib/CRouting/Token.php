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
    protected $optional;
    protected $requirement = '[a-zA-Z0-9-_]+';

    public function __construct($value, $type, $optional)
    {
        if ($type != 'variable' && $type != 'constant') {
            throw new Exception('Token type must be variable or constant');
        }
        $this->value    = $value;
        $this->optional = $optional;
        $this->type     = $type == 'constant' ? self::CONSTANT : self::VARIABLE;
    }

    public function setDefault($default)
    {
        $this->default = $default;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function setRequirement($requirement)
    {
        if (is_string($requirement)) {
            $this->requirement = $requirement;
        }
    }

    public function getValidation($variable) 
    {
        if (empty($this->requirement)) {
            return false;
        }
        return $this->requirement->getExpr($variable);
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

    public function hasRequirement() 
    {
        return !empty($this->requirement);
    }

    public function isVariable()
    {
        return $this->type == self::VARIABLE;
    }

    public function isOptional()
    {
        return  $this->optional || ($this->isVariable() && !is_null($this->default)); 
    }

    public function __toString()
    {
        $regex = '(';
        if ($this->isVariable()) {
            $regex .= '?P<' . $this->getValue() . '>' . $this->requirement;
        } else {
            $regex .= ':?' . preg_quote($this->value, '|');
        }
        $regex .= ')';
        if ($this->isOptional()) {
            $regex .= '?';
        }
        return $regex;
    }
}
