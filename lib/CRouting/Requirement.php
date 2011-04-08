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
            $this->type = 'number';
        } else if (is_string($requirement)) {
            $this->type    = 'string';
            $this->options = explode("|", $requirement);
        } else {
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

    public function getExpr($variable)
    {
        $expr = null;
        switch ($this->type) {
        case 'number':
            $expr = PHP::Exec('is_numeric', $variable);
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
