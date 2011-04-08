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

class PHP_Generator {
    protected $ident = 0;
    protected $safe  = false;
    
    protected $exec = array(
        'lower' => 'strtolower',
        'upper' => 'strtoupper',
    );

    public function generateString($args) {
        return  "'" . addslashes($args[0]) . "'";
    }

    public function generateOperator($args) {
        return $args[0];
    }

    public function generateProperty($args) {
        return "->" . $args[0];
    }

    public function generateAssign ($args) {
        return $args[0] . ' = ' . $args[1];
    }

    public function generateStmtList($args) {
        return implode(",", $args);
    }

    public function generateExec($args) {
        if (is_array($args[0])) {
            $args[0] = implode("::", $args[0]);
        }
        return "{$args[0]}({$args[1]})";
    }

    public function generateBool($args) {
        return $args[0] ? 'true' : 'false';
    }

    public function generateExpr($args) {
        $prev  = null;
        $code  = '';
        if ($args[0]->getType() != 'Operator') {
            switch ($args[0]->getType()) {
            case 'Exec':
            case 'Expr':
            case 'Assign':
                if (count($args) > 1 && $args[1] InstanceOf PHP_Operator) {
                    $code .= ' ' . $args[1]  . ' ' . $this->generateExpr(array_slice($args, 2));
                }
                if ($args[0]->getType() == 'Exec') {
                    $expr = $args[0];
                } else {
                    $expr = '(' . $args[0] . ')';
                }
                return $expr . $code;
            default:
                throw new Exception("Malformed Expr. Unexpected: " . print_r($args, true));
            }
        }
        switch ((string)$args[0]) {
        case 'in':
            $code = "(strpos({$args[2]}, {$args[1]}) !== false)";
            break;
        case '?':
            $code = "{$args[1]} ? {$args[2]} : {$args[3]}";
            break;
        default:
            $code = $args[1] . ' ' . $args[0] . ' ' . $args[2];
        }
        if (count($args) > 3 && $args[3] InstanceOf PHP_Operator) {
            $code .= ' ' . $args[3]  . ' ' . $this->generateExpr(array_slice($args, 4));
        }
        return $code;
    }

    public function generatePrint($args) {
        if ($args[0] InstanceOf PHP_String) {
            list($value) = $args[0]->getAttributes();
            $value = trim($value);
            if (empty($value)) {
                return '';
            }
        }
        return "echo {$args[0]}";
    }

    public function generateElse($args, $body) {
        return "else {$body}";
    }

    public function generateIf($args, $body) {
        return "if ({$args[0]}) {$body}";
    }


    public function generateFunction($args, $body) {
        $code = "function {$args[0]}({$args[1]}) {$body}";
        return $code;
    }

    public function generateClass($args, $body) {
        return "class {$args[0]} {$body}";
    }


    public function blockClass($args, $body) {
        $code = "class {$args[0]} {$body}";
        return $code;
    }

    public function generateForEach($args, $body) {
        $code = "foreach ({$args[0]} as ";
        if (!empty($args[1])) {
            $code .= "{$args[1]} => ";
        }
        $code .= " {$args[2]}) {$body}";
        return $code;
    }

    public function generateComment($string)
    {
        return "/* $string[0] */\n";
    }

    public function doReturn($args)
    {
        return "return " .$args[0];
    }

    public function nodes($array, $newBlock=true) {
        if (empty($array)) {
            return "";
        }
        if ($newBlock) {
            $this->ident++;
            $code  = "{\n";
        } else {
            $code = '';
        }
            
        $pident = str_repeat("\t", $this->ident-1);
        $ident  = str_repeat("\t", $this->ident);
        foreach ($array as $stmt) {
            if (empty($stmt)) {
                continue;
            }
            $current = "$stmt";
            if (empty($current)) {
                continue;
            }
            $code .= "{$ident}{$current}";
            if (!$stmt instanceof PHP_Blocks && substr($code, -1) != "\n") {
                $code .= ";\n";
            }
        }
        if ($newBlock) {
            $code .=  "{$pident}}\n";
            $this->ident--;
        }
        return $code;
    }

    public function generateNumber($args) {
        return (string)$args[0];
    }

    public function generateArray($args) {
        $code =  "";
        foreach ($args as $arg) {
            if ($arg[0] === null) {
                $code .=$arg[1];
            } else {
                $code .= $arg[0] . ' => ' . $arg[1];
            }
            $code .= ", ";
        }
        $code = "array(" . substr($code, 0, -2) . ")";
        return $code;
    }

    public function generateVariable($args) {
        foreach ($args as $value) {
            if (empty($var)) {
                $var = '$' . $value;
            } else {
                if ($value InstanceOf PHP_Property) {
                    $var .= $value;
                } else {
                    $var .= "[" .  $value  . "]";
                }
            }
        }
        return $var;
    }


}
