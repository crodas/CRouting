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
class CRouting_Segment
{
    protected $id;
    protected $tokens;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getToken($id)
    {
        return isset($this->tokens[$id]) ? $this->tokens[$id] : null;
    }

    // getValidationExpr {{{
    /**
     *  Generate the Expression to validate the current segment. Also,
     *  append to the $return array the variables to return.
     *
     *  @param PHP_Variable $variable
     *  @param array &$return       
     *
     *  return PHP_Expr
     */
    public function getValidationExpr()
    {   
        $regex = "";
        foreach ($this->tokens as $id => $token) {
            $regex .= $token;
        }
        return $regex;
    }
    // }}}

    // addToken {{{
    /**
     *  Create a new token and append it to the segment
     *
     *  @param string $type
     *  @param string $value
     *  @param string $default
     *  @param string $rule
     *
     *  @return void
     */
    public function addToken($type, $value, $default=null, $requirement=null, $optional=false)
    {
        $token = new CRounting_Token($value, $type, $optional);
        if (!is_null($default)) {
            $token->setDefault($default);
        }
        if ($requirement) {
            $token->setRequirement($requirement);
        }
        $this->tokens[] = $token;
    }
    // }}}

    // isOptional {{{
    /**
     *  Check if the current Segment and its token is optional
     *
     *  @return bool
     */
    public function isOptional()
    {
        $optional = true;
        foreach ($this->tokens as $token) {
            $optional &= $token->isOptional();
        }
        return $optional;
    }
    // }}}

    function getVariables()
    {
        $tokens = array();
        foreach ($this->tokens as $id => $token) {
            if ($token->isVariable()) {
                $tokens[] = $token;
            }
        }
        return $tokens;
    }

    function getAll()
    {
        $tokens = array();
        foreach ($this->tokens as $id => $token) {
            $tokens[] = $token;
        }
        return $tokens;
    }
}
