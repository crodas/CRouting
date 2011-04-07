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
    public function getValidationExpr($variable, &$return)
    {   
        /**
         *  detect each constant pattern in the URL,
         *  generate code to fail as soon as possible
         */
        $rules = array();
        foreach ($this->tokens as $id => $token) {
            if ($token->isConstant()) {
                $newvar = PHP::Variable('offset_' . $this->id . '_' . $id);
                $text   = PHP::String($token->getValue());
                $length = strlen($token->getValue());
                if ($id == 0 && count($this->tokens) == 1) {
                    $rules[] = PHP::Expr('==', $variable, $text);
                } else {
                    if ($id == 0) {
                        $rules[]  = PHP::Expr('===', PHP::Expr(PHP::Assign($newvar, PHP::Exec('strpos', $variable, $text))), 0);
                    } else {
                        $rules[]  = PHP::Expr('!==', PHP::Expr(PHP::Assign($newvar, PHP::Exec('strpos', $variable, $text, (isset($offset) ? PHP::Expr('+', $offset, 1) : 0)))), false);
                    }
                }
                $offset = $newvar;
            }
        }


        /**
         *  generate code to extract each variable within the segment,
         *  and validate it, if there is something to validate
         */
        $ntokens = count($this->tokens);
        foreach ($this->tokens as $id => $token) {
            if ($token->isConstant()) {
                continue;
            }
            $tokenVar = $variable;
            if ($ntokens > 1) {
                /* segment has constants and variables */
                $newvar   = PHP::Variable('value_'  . $this->id . '_' . $id);
                $varStart = PHP::Variable('offset_' . $this->id . '_' . ($id-1));
                $offStart = PHP::Expr('+', 1, $varStart);
                $offEnd   = PHP::Variable('offset_' . $this->id . '_' . ($id+1));
                if ($id == 0) {
                    $varStart = $offEnd;
                    $exec = PHP::Exec('substr', $variable, 0, $offEnd);
                } else if ($ntokens == $id+1) {
                    $exec = PHP::Exec('substr', $variable, $offStart);
                } else {
                    $exec = PHP::Exec('substr', $variable, $offStart, PHP::Expr('-', $offEnd, PHP::Expr($offStart)));
                }

                if ($token->isOptional()) {
                    $default = PHP::Expr('!==', PHP::Expr(PHP::Assign($newvar, PHP::String($token->getDefault()))), false);
                    $rules[] = PHP::Expr('OR', PHP::Assign($newvar, $exec), $default);
                } else {
                    $rules[] = PHP::Expr(PHP::Assign($newvar, $exec));
                }

                $tokenVar = $newvar;
            }

            if ($token->hasRule()) {
                $rule = $token->getValidation($tokenVar);
                if (!$rule instanceof PHP) {
                    continue;
                }
                if ($token->isOptional() && ($id > 0 || count($this->tokens) > 1)) {
                    // append an OR if it is optional, and it is the first
                    // or the only token in the segment
                    $rule = PHP::Expr('OR', PHP::Expr('==', $varStart, false), $rule);
                }
                $rules[]  = $rule;
                $return[] = array(PHP::String($token->getValue()), $tokenVar);
            } else {
                // 
                $return[] = array(PHP::String($token->getValue()), $tokenVar);
            }
        }

        return count($rules) > 0? PHP::ExprArray($rules) : false;
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
    public function addToken($type, $value, $default=null, $rule=null)
    {
        $token = new CRounting_Token($value, $type);
        if (!is_null($default)) {
            $token->setDefault($default);
        }
        if ($rule) {
            $token->setRule($rule);
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

}
