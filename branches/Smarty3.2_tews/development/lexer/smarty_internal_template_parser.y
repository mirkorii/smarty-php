/**
* Smarty Internal Plugin Template Parser
*
* This is the template parser
* 
* 
* @package Smarty
* @subpackage Compiler
* @author Uwe Tews
*/
%name TP_
%declare_class {class Smarty_Internal_Template_Parser extends Smarty_Internal_Magic_Error}
%include_class
{
    const Err1 = "Security error: Call to private object member not allowed";
    const Err2 = "Security error: Call to dynamic object member not allowed";
    const Err3 = "PHP in template not allowed. Use SmartyBC to enable it";
    // states whether the parse was successful or not
    public $successful = true;
    public $retvalue = 0;
    public  $lex = null;
    public  $compiler = null;
    public  $prefix_number = 0;
    public  $block_nesting_level = 0;
    private $internalError = false;
    private $last_taglineno = 0;
    private $last_taglineno_nocache = 0;
    private $strip = false;
    private $text_is_php = false;
    private $is_xml = false;
    private $db_quote_code_buffer = '';
    private $asp_tags = null;
    private $php_handling = null;
    private $security = null;
    private $last_variable = null;
    private $last_index = null;

    function __construct($lex, $compiler) {
        $this->lex = $lex;
        $this->compiler = $compiler;
        $this->compiler->prefix_code = array();
        if ($this->security = isset($this->compiler->tpl_obj->security_policy)) {
            $this->php_handling = $this->compiler->tpl_obj->security_policy->php_handling;
        } else {
            $this->php_handling = $this->compiler->tpl_obj->php_handling;
        }
       $this->asp_tags = (ini_get('asp_tags') != '0');
    }


    public function compileVariable($variable) {
    	 if (strpos($variable,'(') === false) {
    	 		// not a variable variable
    	 		$var = trim($variable,'\'"');
			    $this->compiler->tag_nocache=$this->compiler->tag_nocache|$this->compiler->tpl_obj->getVariable($var, null, true, false, 'nocache');
//			    $this->compiler->tpl_obj->properties['variables'][$var] = $this->compiler->tag_nocache|$this->compiler->nocache;
			 } else {
			    $var = '{'.$variable.'}';
			 }
			 return '$_scope->'. $var . '->value';
    }
    
    public function updateNocacheLineTrace($allways = false) {
       if ($this->compiler->tpl_obj->enable_traceback) {
           $line = $this->lex->taglineno;
           if ($this->compiler->caching && $this->last_taglineno_nocache != $this->lex->taglineno) {
               $this->compiler->has_code = true;
               $this->compiler->nocache_nolog = true;
               $this->compiler->nocacheCode('', true, $line);
               $this->last_taglineno_nocache = $this->lex->taglineno;
           } elseif ($allways && $this->last_taglineno != $this->lex->taglineno) {
               $this->compiler->has_code = true;
               $this->compiler->nocacheCode('', true, $line);
               $this->last_taglineno = $this->lex->taglineno;
           }
       }
    }
} 


%token_prefix TP_

%parse_accept
{
    $this->successful = !$this->internalError;
    $this->internalError = false;
    $this->retvalue = $this->_retvalue;
    //echo $this->retvalue."\n\n";
}

%syntax_error
{
    $this->internalError = true;
    $this->yymajor = $yymajor;
    $this->compiler->trigger_template_error();
}

%stack_overflow
{
    $this->internalError = true;
    $this->compiler->trigger_template_error("Stack overflow in template parser");
}

%left VERT.
%left COLON.

//
// complete template
//
start ::= template. {
   // execute end of template
   $this->compiler->template_code->newline()->php("array_shift(\$_smarty_tpl->trace_call_stack);\n");
   if ($this->compiler->caching) {
       $this->compiler->has_code = true;
       $this->compiler->nocache_nolog = true;
       $this->compiler->nocacheCode("array_shift(\$_smarty_tpl->trace_call_stack);", true);
   }
}

//
// loop over template elements
//
                      // single template element
template       ::= template_element. {
}

                      // loop of elements
template       ::= template template_element. {
}

                      // empty template
template       ::= . 

//
// template elements
//
                      // Template init
template_element ::= TEMPLATEINIT(i). {
    if ($this->compiler->source->type == 'eval' || $this->compiler->source->type == 'string') {
        $resource = $this->compiler->source->type;
    } else {
        $resource = $this->compiler->source->filepath;
    }
    if (strpos($this->compiler->tpl_obj->template_resource,'extends:') ===false) {
        $code = "array_unshift(\$_smarty_tpl->trace_call_stack, array('{$resource}',{$this->lex->taglineno} , '{$this->compiler->source->type}'));";
        if ($this->compiler->caching) {
            $this->compiler->has_code = true;
            $this->compiler->nocache_nolog = true;
            $this->compiler->nocacheCode($code, true);
        }
        $this->compiler->template_code->php($code)->newline();
    }
}


                      // Smarty tag
template_element ::= smartytag(st). {
    if ($this->compiler->has_code) {
        $line = 0;
        if ($this->compiler->tpl_obj->enable_traceback) {
            if ($this->compiler->caching && $this->last_taglineno_nocache != $this->lex->taglineno && ($this->compiler->nocache || $this->compiler->tag_nocache)) { 
                $line = $this->last_taglineno_nocache = $this->lex->taglineno;
            }
            if ($this->last_taglineno != $this->lex->taglineno && (!$this->compiler->caching || (!$this->compiler->nocache && !$this->compiler->tag_nocache))) { 
                $line = $this->last_taglineno = $this->lex->taglineno;
            }
        }
        $this->compiler->nocacheCode(st,true,$line);
    } else { 
        $this->compiler->template_code->raw(st);
    }  
    $this->block_nesting_level = count($this->compiler->_tag_stack);
} 

                      // comments
template_element ::= LDEL COMMENT RDEL. {
}

                      // Literal
template_element ::= literal(l). {
    $this->compiler->template_code->php('echo ')->string(l)->raw(";\n");
}

                      // '<?php' tag
template_element ::= PHPSTARTTAG(st). {
    if ($this->php_handling == Smarty::PHP_PASSTHRU) {
        $this->compiler->template_code->php("echo '<?php';\n");
    } elseif ($this->php_handling == Smarty::PHP_QUOTE) {
        $this->compiler->template_code->php("echo '&lt;?php';\n");
    } elseif ($this->php_handling == Smarty::PHP_ALLOW) {
        if (!($this->compiler->template instanceof SmartyBC)) {
            $this->compiler->trigger_template_error (self::Err3);
        }
        $this->text_is_php = true;
    }
}

                      // '?>' tag
template_element ::= PHPENDTAG. {
    if ($this->is_xml) {
        $this->is_xml = false;
        $this->compiler->template_code->php("echo '?>';\n");
    } elseif ($this->php_handling == Smarty::PHP_PASSTHRU) {
        $this->compiler->template_code->php("echo '?>';\n");
    } elseif ($this->php_handling == Smarty::PHP_QUOTE) {
        $this->compiler->template_code->php("echo '?&gt;';\n");
    } elseif ($this->php_handling == Smarty::PHP_ALLOW) {
        $this->text_is_php = false;
    }
}

                      // '<%' tag
template_element ::= ASPSTARTTAG(st). {
    if ($this->php_handling == Smarty::PHP_PASSTHRU) {
        $this->compiler->template_code->php("echo '<%';\n");
    } elseif ($this->php_handling == Smarty::PHP_QUOTE) {
        $this->compiler->template_code->php("echo '&lt;%';\n");
    } elseif ($this->php_handling == Smarty::PHP_ALLOW) {
        if ($this->asp_tags) {
            if (!($this->compiler->template instanceof SmartyBC)) {
                $this->compiler->trigger_template_error (self::Err3);
            }
            $this->text_is_php = true;
        } else {
            $this->compiler->template_code->php("echo '<%';\n");
        }
    } elseif ($this->php_handling == Smarty::PHP_REMOVE) {
        if (!$this->asp_tags) {
            $this->compiler->template_code->php("echo '<%';\n");
        }
    }
}
  
                      // '%>' tag
template_element ::= ASPENDTAG(et). {
    if ($this->php_handling == Smarty::PHP_PASSTHRU) {
        $this->compiler->template_code->php("echo '%>';\n");
    } elseif ($this->php_handling == Smarty::PHP_QUOTE) {
        $this->compiler->template_code->php("echo '%&gt;';\n");
    } elseif ($this->php_handling == Smarty::PHP_ALLOW) {
        if ($this->asp_tags) {
            $this->text_is_php = false;
        } else {
            $this->compiler->template_code->php("echo '%>';\n");
        }
    } elseif ($this->php_handling == Smarty::PHP_REMOVE) {
        if (!$this->asp_tags) {
            $this->compiler->template_code->php("echo '%>';\n");
        }
    }
}

template_element ::= FAKEPHPSTARTTAG(o). {
    if ($this->strip) {
        $this->compiler->template_code->php('echo ')->string(preg_replace('![\t ]*[\r\n]+[\t ]*!', '', o))->raw(";\n");
    } else {
        $this->compiler->template_code->php('echo ')->string(o)->raw(";\n");
    }
}

                      // XML tag
template_element ::= XMLTAG. {
    $this->is_xml = true; 
    $this->compiler->template_code->php("echo '<?xml';\n");
}

                      // template text
template_element ::= TEXT(o). {
    if ($this->text_is_php) {
        $line = 0;
        if ($this->compiler->tpl_obj->enable_traceback) {
            if ($this->compiler->caching && $this->last_taglineno_nocache != $this->lex->taglineno && ($this->compiler->nocache || $this->compiler->tag_nocache)) { 
                $line = $this->last_taglineno_nocache = $this->lex->taglineno;
            }
            if ($this->last_taglineno != $this->lex->taglineno && (!$this->compiler->caching || (!$this->compiler->nocache && !$this->compiler->tag_nocache))) { 
                $line = $this->last_taglineno = $this->lex->taglineno;
            }
        }
        $this->compiler->prefix_code[] = o;
        $this->compiler->nocacheCode('', true, $line);
    } else {
        // inheritance child templates shall not output text
        if (!$this->compiler->isInheritanceChild || $this->compiler->block_nesting_level > 0) {
            if ($this->strip) {
                $this->compiler->template_code->php('echo ')->string(preg_replace('![\t ]*[\r\n]+[\t ]*!', '', o))->raw(";\n");
            } else {
                $this->compiler->template_code->php('echo ')->string(o)->raw(";\n");
            }
        }
    }
}

                      // strip on
template_element ::= STRIPON(d). {
    $this->strip = true;
}
                      // strip off
template_element ::= STRIPOFF(d). {
    $this->strip = false;
}

                    // Litteral
literal(res) ::= LITERALSTART LITERALEND. {
    res = '';
}

literal(res) ::= LITERALSTART literal_elements(l) LITERALEND. {
    res = l;
}
 
literal_elements(res) ::= literal_elements(l1) literal_element(l2). {
    res = l1.l2;
}

literal_elements(res) ::= . {
    res = '';
}

literal_element(res) ::= literal(l). {
    res = l;
}

literal_element(res) ::= LITERAL(l). {
    res = l;
}

literal_element(res) ::= PHPSTARTTAG(st). {
    res = st;
}

literal_element(res) ::= FAKEPHPSTARTTAG(st). {
    res = st;
}

literal_element(res) ::= PHPENDTAG(et). {
    res = et;
}

literal_element(res) ::= ASPSTARTTAG(st). {
    res = st;
}

literal_element(res) ::= ASPENDTAG(et). {
    res = et;
}

//
// output tags start here
//

                  // output with optional attributes
smartytag(res)   ::= LDEL value(e) RDEL. {
    res = $this->compiler->compileTag('private_print_expression',array(),array('value'=>e));
}

smartytag(res)   ::= LDEL value(e) modifierlist(l) attributes(a) RDEL. {
    res = $this->compiler->compileTag('private_print_expression',a,array('value'=>e, 'modifierlist'=>l));
}

smartytag(res)   ::= LDEL value(e) attributes(a) RDEL. {
    res = $this->compiler->compileTag('private_print_expression',a,array('value'=>e));
}

smartytag(res)   ::= LDEL expr(e) modifierlist(l) attributes(a) RDEL. {
    res = $this->compiler->compileTag('private_print_expression',a,array('value'=>e,'modifierlist'=>l));
}

smartytag(res)   ::= LDEL expr(e) attributes(a) RDEL. {
    res = $this->compiler->compileTag('private_print_expression',a,array('value'=>e));
}

//
// Smarty tags start here
//

                  // assign new style
smartytag(res)   ::= LDEL DOLLAR ID(i) EQUAL value(e) RDEL. {
    res = $this->compiler->compileTag('assign',array(array('value'=>e),array('var'=>"'".i."'")));
}
                  
smartytag(res)   ::= LDEL DOLLAR ID(i) EQUAL expr(e) RDEL. {
    res = $this->compiler->compileTag('assign',array(array('value'=>e),array('var'=>"'".i."'")));
}
                 
smartytag(res)   ::= LDEL DOLLAR ID(i) EQUAL expr(e) attributes(a) RDEL. {
    res = $this->compiler->compileTag('assign',array_merge(array(array('value'=>e),array('var'=>"'".i."'")),a));
}                  

smartytag(res)   ::= LDEL varindexed(vi) EQUAL expr(e) attributes(a) RDEL. {
    res = $this->compiler->compileTag('assign',array_merge(array(array('value'=>e),array('var'=>vi['var'])),a),array('smarty_internal_index'=>vi['smarty_internal_index']));
} 
                 
                  // tag with optional Smarty2 style attributes
smartytag(res)   ::= LDEL ID(i) attributes(a) RDEL. {
    res = $this->compiler->compileTag(i,a);
}

smartytag(res)   ::= LDEL ID(i) RDEL. {
    res = $this->compiler->compileTag(i,array());
}

                  // registered object tag
smartytag(res)   ::= LDEL ID(i) PTR ID(m) attributes(a) RDEL. {
    res = $this->compiler->compileTag(i,a,array('object_method'=>m));
}

                  // tag with modifier and optional Smarty2 style attributes
smartytag(res)   ::= LDEL ID(i) modifierlist(l)attributes(a) RDEL. {
    res = 'ob_start(); '.$this->compiler->compileTag(i,a).' echo ';
    res .= $this->compiler->compileTag('private_modifier',array(),array('modifierlist'=>l,'value'=>'ob_get_clean()')).';';
}

                  // registered object tag with modifiers
smartytag(res)   ::= LDEL ID(i) PTR ID(me) modifierlist(l) attributes(a) RDEL. {
    res = 'ob_start(); '.$this->compiler->compileTag(i,a,array('object_method'=>me)).' echo ';
    res .= $this->compiler->compileTag('private_modifier',array(),array('modifierlist'=>l,'value'=>'ob_get_clean()')).';';
}

                  // {if}, {elseif} and {while} tag
smartytag(res)   ::= LDELIF(i) expr(ie) RDEL. {
    $tag = trim(substr(i,$this->lex->ldel_length)); 
    res = $this->compiler->compileTag(($tag == 'else if')? 'elseif' : $tag,array(),array('if condition'=>ie));
}

smartytag(res)   ::= LDELIF(i) expr(ie) attributes(a) RDEL. {
    $tag = trim(substr(i,$this->lex->ldel_length));
    res = $this->compiler->compileTag(($tag == 'else if')? 'elseif' : $tag,a,array('if condition'=>ie));
}

smartytag(res)   ::= LDELIF(i) statement(ie) RDEL. {
    $tag = trim(substr(i,$this->lex->ldel_length));
    res = $this->compiler->compileTag(($tag == 'else if')? 'elseif' : $tag,array(),array('if condition'=>ie));
}

smartytag(res)   ::= LDELIF(i) statement(ie)  attributes(a) RDEL. {
    $tag = trim(substr(i,$this->lex->ldel_length));
    res = $this->compiler->compileTag(($tag == 'else if')? 'elseif' : $tag,a,array('if condition'=>ie));
}

                  // {for} tag
smartytag(res)   ::= LDELFOR statements(st) SEMICOLON optspace expr(ie) SEMICOLON optspace DOLLAR varvar(v2) EQUAL expr(e) attributes(a) RDEL. {
    res = $this->compiler->compileTag('for',array_merge(a,array(array('start'=>st),array('ifexp'=>ie),array('var'=>v2),array('step'=>'='.e))),1);
}
smartytag(res)   ::= LDELFOR statements(st) SEMICOLON optspace expr(ie) SEMICOLON optspace IDINCDEC(v2) attributes(a) RDEL. {
    $len =strlen(v2);
    res = $this->compiler->compileTag('for',array_merge(a,array(array('start'=>st),array('ifexp'=>ie),array('var'=>substr(v2,1,$len-3)),array('step'=>substr(v2,$len-2)))),1);
}

smartytag(res)   ::= LDELFOR statement(st) TO expr(v) attributes(a) RDEL. {
    res = $this->compiler->compileTag('for',array_merge(a,array(array('start'=>st),array('to'=>v))),0);
}

smartytag(res)   ::= LDELFOR statement(st) TO expr(v) STEP expr(v2) attributes(a) RDEL. {
    res = $this->compiler->compileTag('for',array_merge(a,array(array('start'=>st),array('to'=>v),array('step'=>v2))),0);
}

                  // {foreach} tag
smartytag(res)   ::= LDELFOREACH attributes(a) RDEL. {
    res = $this->compiler->compileTag('foreach',a);
}

                  // {foreach $array as $var} tag
smartytag(res)   ::= LDELFOREACH SPACE value(v1) AS DOLLAR varvar(v0) attributes(a) RDEL. {
    res = $this->compiler->compileTag('foreach',array_merge(a,array(array('from'=>v1),array('item'=>v0))));
}

smartytag(res)   ::= LDELFOREACH SPACE value(v1) AS DOLLAR varvar(v2) APTR DOLLAR varvar(v0) attributes(a) RDEL. {
    res = $this->compiler->compileTag('foreach',array_merge(a,array(array('from'=>v1),array('item'=>v0),array('key'=>v2))));
}

smartytag(res)   ::= LDELFOREACH SPACE expr(e) AS DOLLAR varvar(v0) attributes(a) RDEL. { 
    res = $this->compiler->compileTag('foreach',array_merge(a,array(array('from'=>e),array('item'=>v0))));
}

smartytag(res)   ::= LDELFOREACH SPACE expr(e) AS DOLLAR varvar(v1) APTR DOLLAR varvar(v0) attributes(a) RDEL. { 
    res = $this->compiler->compileTag('foreach',array_merge(a,array(array('from'=>e),array('item'=>v0),array('key'=>v1))));
}

                  // {setfilter}
smartytag(res)   ::= LDELSETFILTER ID(m) modparameters(p) RDEL. { 
    res = $this->compiler->compileTag('setfilter',array(),array('modifier_list'=>array(array_merge(array(m),p))));
}

smartytag(res)   ::= LDELSETFILTER ID(m) modparameters(p) modifierlist(l) RDEL. { 
    res = $this->compiler->compileTag('setfilter',array(),array('modifier_list'=>array_merge(array(array_merge(array(m),p)),l)));
}

                  
                  
                  // end of block tag  {/....}                  
smartytag(res)   ::= LDELSLASH ID(i) RDEL. {
    res = $this->compiler->compileTag(i.'close',array());
}

smartytag(res)   ::= LDELSLASH ID(i) modifierlist(l) RDEL. {
    res = $this->compiler->compileTag(i.'close',array(),array('modifier_list'=>l));
}

                  // end of block object tag  {/....}                 
smartytag(res)   ::= LDELSLASH ID(i) PTR ID(m) RDEL. {
    res = $this->compiler->compileTag(i.'close',array(),array('object_method'=>m));
}

smartytag(res)   ::= LDELSLASH ID(i) PTR ID(m) modifierlist(l) RDEL. {
    res = $this->compiler->compileTag(i.'close',array(),array('object_method'=>m, 'modifier_list'=>l));
}

//
//Attributes of Smarty tags 
//
                  // list of attributes
attributes(res)  ::= attributes(a1) attribute(a2). {
    res = a1;
    res[] = a2;
}

                  // single attribute
attributes(res)  ::= attribute(a). {
    res = array(a);
}

                  // no attributes
attributes(res)  ::= . {
    res = array();
}
                  
                  // attribute
attribute(res)   ::= SPACE ID(v) EQUAL ID(id). {
    if (preg_match('~^true$~i', id)) {
        res = array(v=>'true');
    } elseif (preg_match('~^false$~i', id)) {
        res = array(v=>'false');
    } elseif (preg_match('~^null$~i', id)) {
        res = array(v=>'null');
    } else {
        res = array(v=>"'".id."'");
    }
}

attribute(res)   ::= ATTR(v) expr(e). {
    res = array(trim(v," =\n\r\t")=>e);
}

attribute(res)   ::= ATTR(v) value(e). {
    res = array(trim(v," =\n\r\t")=>e);
}

attribute(res)   ::= SPACE ID(v). {
    res = "'".v."'";
}

attribute(res)   ::= SPACE expr(e). {
    res = e;
}

attribute(res)   ::= SPACE value(v). {
    res = v;
}

attribute(res)   ::= SPACE INTEGER(i) EQUAL expr(e). {
    res = array(i=>e);
}

                  

//
// statement
//
statements(res)   ::= statement(s). {
    res = array(s);
}

statements(res)   ::= statements(s1) COMMA statement(s). {
    s1[]=s;
    res = s1;
}

statement(res)    ::= DOLLAR varvar(v) EQUAL expr(e). {
    res = array('var' => v, 'value'=>e);
}

statement(res)    ::= varindexed(vi) EQUAL expr(e). {
    res = array('var' => vi, 'value'=>e);
}

statement(res)    ::= OPENP statement(st) CLOSEP. {
    res = st;
}


//
// expressions
//

                  // single value
expr(res)        ::= value(v). {
    res = v;
}

                 // ternary
expr(res)        ::= ternary(v). {
    res = v;
}

                 // resources/streams
expr(res)        ::= DOLLAR ID(i) COLON ID(i2). {
    res = '$_smarty_tpl->getStreamVariable(\''. i .'://'. i2 . '\')';
}

                  // arithmetic expression
expr(res)        ::= expr(e) MATH(m) value(v). {
    res = e . trim(m) . v;
}

expr(res)        ::= expr(e) UNIMATH(m) value(v). {
    res = e . trim(m) . v;
}
 
                  // bit operation 
expr(res)        ::= expr(e) ANDSYM(m) value(v). {
    res = e . trim(m) . v;
} 

                  // array
expr(res)       ::= array(a). {
    res = a;
}

                  // modifier
expr(res)        ::= expr(e) modifierlist(l). {
    res = $this->compiler->compileTag('private_modifier',array(),array('value'=>e,'modifierlist'=>l));
}

// if expression
                    // simple expression
expr(res)        ::= expr(e1) ifcond(c) expr(e2). {
    res = e1.c.e2;
}

expr(res)        ::= expr(e1) ISIN array(a).  {
    res = 'in_array('.e1.','.a.')';
}

expr(res)        ::= expr(e1) ISIN value(v).  {
    res = 'in_array('.e1.',(array)'.v.')';
}

expr(res)        ::= expr(e1) lop(o) expr(e2).  {
    res = e1.o.e2;
}

expr(res)        ::= expr(e1) ISDIVBY expr(e2). {
    res = '!('.e1.' % '.e2.')';
}

expr(res)        ::= expr(e1) ISNOTDIVBY expr(e2).  {
    res = '('.e1.' % '.e2.')';
}

expr(res)        ::= expr(e1) ISEVEN. {
    res = '!(1 & '.e1.')';
}

expr(res)        ::= expr(e1) ISNOTEVEN.  {
    res = '(1 & '.e1.')';
}

expr(res)        ::= expr(e1) ISEVENBY expr(e2).  {
    res = '!(1 & '.e1.' / '.e2.')';
}

expr(res)        ::= expr(e1) ISNOTEVENBY expr(e2). {
    res = '(1 & '.e1.' / '.e2.')';
}

expr(res)        ::= expr(e1) ISODD.  {
    res = '(1 & '.e1.')';
}

expr(res)        ::= expr(e1) ISNOTODD. {
    res = '!(1 & '.e1.')';
}

expr(res)        ::= expr(e1) ISODDBY expr(e2). {
    res = '(1 & '.e1.' / '.e2.')';
}

expr(res)        ::= expr(e1) ISNOTODDBY expr(e2).  {
    res = '!(1 & '.e1.' / '.e2.')';
}

expr(res)        ::= value(v1) INSTANCEOF(i) ID(id). {
    res = v1.i.id;
}
expr(res)        ::= value(v1) INSTANCEOF(i) NAMESPACE(id). {
    res = v1.i.id;
}


expr(res)        ::= value(v1) INSTANCEOF(i) value(v2). {
    $this->prefix_number++;
    $this->compiler->prefix_code[] = '$_tmp'.$this->prefix_number.'='.v2.';';
    res = v1.i.'$_tmp'.$this->prefix_number;
}

//
// ternary
//
ternary(res)        ::= OPENP expr(v) CLOSEP  QMARK DOLLAR ID(e1) COLON  expr(e2). {
    res = v.' ? '. $this->compileVariable("'".e1."'") . ' : '.e2;
}

ternary(res)        ::= OPENP expr(v) CLOSEP  QMARK  expr(e1) COLON  expr(e2). {
    res = v.' ? '.e1.' : '.e2;
}


                 // value
value(res)       ::= variable(v). {
    res = v;
}

                  // +/- value
value(res)        ::= UNIMATH(m) value(v). {
    res = m.v;
}

                  // logical negation
value(res)       ::= NOT value(v). {
    res = '!'.v;
}

value(res)       ::= TYPECAST(t) value(v). {
    res = t.v;
}

                 // numeric
value(res)       ::= HEX(n). {
    res = n;
}

value(res)       ::= INTEGER(n). {
    res = n;
}

value(res)       ::= INTEGER(n1) DOT INTEGER(n2). {
    res = n1.'.'.n2;
}

value(res)       ::= INTEGER(n1) DOT. {
    res = n1.'.';
}

value(res)       ::= DOT INTEGER(n1). {
    res = '.'.n1;
}

                 // ID, true, false, null
value(res)       ::= ID(id). {
    if (preg_match('~^true$~i', id)) {
        res = 'true';
    } elseif (preg_match('~^false$~i', id)) {
        res = 'false';
    } elseif (preg_match('~^null$~i', id)) {
        res = 'null';
    } else {
        res = "'".id."'";
    }
}

                  // function call
value(res)       ::= function(f). {
    res = f;
}

                  // expression
value(res)       ::= OPENP expr(e) CLOSEP. {
    res = "(". e .")";
}

                  // singele quoted string
value(res)       ::= SINGLEQUOTESTRING(t). {
    res = t;
}

                  // double quoted string
value(res)       ::= doublequoted_with_quotes(s). {
    res = s;
}

value(res)    ::= IDINCDEC(v). {
    $len = strlen(v);
    res = '$_scope->' . substr(v,1,$len-3) . '->value' . substr(v,$len-2);
}

                  // static class access
value(res)       ::= ID(c) DOUBLECOLON static_class_access(r). {
    if (!$this->security || isset($this->compiler->tpl_obj->registered_classes[c]) || $this->compiler->tpl_obj->security_policy->isTrustedStaticClass(c, $this->compiler)) {
        if (isset($this->compiler->tpl_obj->registered_classes[c])) {
            res = $this->compiler->tpl_obj->registered_classes[c].'::'.r;
        } else {
            res = c.'::'.r;
        } 
    } else {
        $this->compiler->trigger_template_error ("static class '".c."' is undefined or not allowed by security setting");
    }
}

                  // namespace class access
value(res)       ::= NAMESPACE(c) DOUBLECOLON static_class_access(r). {
        res = c.'::'.r;
}

                  // name space constant
value(res)       ::= NAMESPACE(c). {
    res = c;
}

value(res)    ::= varindexed(vi) DOUBLECOLON static_class_access(r). {
    if (vi['var'] == '\'smarty\'') {
        res =  $this->compiler->compileTag('private_special_variable',array(),vi['smarty_internal_index']).'::'.r;
    } else {
        res = $this->compileVariable(vi['var']).vi['smarty_internal_index'].'::'.r;
    }
}

                  // Smarty tag
value(res)       ::= smartytag(st). {
    $this->prefix_number++;
    $this->compiler->prefix_code[] = 'ob_start(); '.st.' $_tmp'.$this->prefix_number.'=ob_get_clean();';
    res = '$_tmp'.$this->prefix_number;
}

value(res)       ::= value(v) modifierlist(l). {
    res = $this->compiler->compileTag('private_modifier',array(),array('value'=>v,'modifierlist'=>l));
}


//
// variables 
//
                  // Smarty variable (optional array)
variable(res)    ::= varindexed(vi). {
    if (vi['var'] == '\'smarty\'') {
        $smarty_var = $this->compiler->compileTag('private_special_variable',array(),vi['smarty_internal_index']);
        res = $smarty_var;
    } else {
        // used for array reset,next,prev,end,current 
        $this->last_variable = vi['var'];
        $this->last_index = vi['smarty_internal_index'];
        res = $this->compileVariable(vi['var']).vi['smarty_internal_index'];
    }
}

                  // variable with property
variable(res)    ::= DOLLAR varvar(v) AT ID(p). {
    res = '$_scope->' . trim(v,"'") . '->' . p;
}

                  // object
variable(res)    ::= object(o). {
    res = o;
}

                  // config variable
variable(res)    ::= HATCH ID(i) HATCH. {
    $var = trim(i,'\'');
    res = "\$_scope->___config_var_{$var}";
}

variable(res)    ::= HATCH ID(i) HATCH arrayindex(a). {
    $var = trim(i,'\'');
    res = "\$_scope->___config_var_{$var}".a;
}

variable(res)    ::= HATCH variable(v) HATCH. {
    res = "\$_scope->___config_var_{{v}}";
}

variable(res)    ::= HATCH variable(v) HATCH arrayindex(a). {
    res = "\$_scope->___config_var_{{v}}".a;
}

varindexed(res)  ::= DOLLAR varvar(v) arrayindex(a). {
    res = array('var'=>v, 'smarty_internal_index'=>a);
}

//
// array index
//
                    // multiple array index
arrayindex(res)  ::= arrayindex(a1) indexdef(a2). {
    res = a1.a2;
}

                    // no array index
arrayindex        ::= . {
    return;
}

// single index definition
                    // Smarty2 style index 
indexdef(res)    ::= DOT DOLLAR varvar(v).  {
    res = '['.$this->compileVariable(v).']';
}

indexdef(res)    ::= DOT DOLLAR varvar(v) AT ID(p). {
    res = '['.$this->compileVariable(v).'->'.p.']';
}

indexdef(res)   ::= DOT ID(i). {
    res = "['". i ."']";
}

indexdef(res)   ::= DOT INTEGER(n). {
    res = "[". n ."]";
}

indexdef(res)   ::= DOT LDEL expr(e) RDEL. {
    res = "[". e ."]";
}

                    // section tag index
indexdef(res)   ::= OPENB ID(i)CLOSEB. {
    res = '['.$this->compiler->compileTag('private_special_variable',array(),'[\'section\'][\''.i.'\'][\'index\']').']';
}

indexdef(res)   ::= OPENB ID(i) DOT ID(i2) CLOSEB. {
    res = '['.$this->compiler->compileTag('private_special_variable',array(),'[\'section\'][\''.i.'\'][\''.i2.'\']').']';
}

                    // PHP style index
indexdef(res)   ::= OPENB expr(e) CLOSEB. {
    res = "[". e ."]";
}

                    // für assign append array
indexdef(res)  ::= OPENB CLOSEB. {
    res = '[]';
}

//
// variable variable names
//
                    // singel identifier element
varvar(res)      ::= varvarele(v). {
    res = v;
}

                    // sequence of identifier elements
varvar(res)      ::= varvar(v1) varvarele(v2). {
    res = v1.'.'.v2;
}

                    // fix sections of element
varvarele(res)   ::= ID(s). {
    res = '\''.s.'\'';
}

                    // variable sections of element
varvarele(res)   ::= LDEL expr(e) RDEL. {
    res = '('.e.')';
}

//
// objects
//
object(res)    ::= varindexed(vi) objectchain(oc). {
    if (vi['var'] == '\'smarty\'') {
        res =  $this->compiler->compileTag('private_special_variable',array(),vi['smarty_internal_index']).oc;
    } else {
        res = $this->compileVariable(vi['var']).vi['smarty_internal_index'].oc;
    }
}

                    // single element
objectchain(res) ::= objectelement(oe). {
    res  = oe;
}

                    // chain of elements 
objectchain(res) ::= objectchain(oc) objectelement(oe). {
    res  = oc.oe;
}

                    // variable
objectelement(res)::= PTR ID(i) arrayindex(a). {
    if ($this->security && substr(i,0,1) == '_') {
        $this->compiler->trigger_template_error (self::Err1);
    }
    res = '->'.i.a;
}

objectelement(res)::= PTR DOLLAR varvar(v) arrayindex(a). {
    if ($this->security) {
        $this->compiler->trigger_template_error (self::Err2);
    }
    res = '->{'.$this->compileVariable(v).a.'}';
}

objectelement(res)::= PTR LDEL expr(e) RDEL arrayindex(a). {
    if ($this->security) {
        $this->compiler->trigger_template_error (self::Err2);
    }
    res = '->{'.e.a.'}';
}

objectelement(res)::= PTR ID(ii) LDEL expr(e) RDEL arrayindex(a). {
    if ($this->security) {
        $this->compiler->trigger_template_error (self::Err2);
    }
    res = '->{\''.ii.'\'.'.e.a.'}';
}

                    // method
objectelement(res)::= PTR method(f).  {
    res = '->'.f;
}


//
// function
//
function(res)     ::= ID(f) OPENP params(p) CLOSEP. {
    if (!$this->security || $this->compiler->tpl_obj->security_policy->isTrustedPhpFunction(f, $this->compiler)) {
        if (strcasecmp(f,'isset') === 0 || strcasecmp(f,'empty') === 0 || strcasecmp(f,'array') === 0 || is_callable(f)) {
            $func_name = strtolower(f);
            if ($func_name == 'isset') {
                if (count(p) == 0) {
                    $this->compiler->trigger_template_error ('Illegal number of paramer in "isset()"');
                }
                $par = implode(',',p);
                preg_match('/\$_scope->([0-9]*[a-zA-Z_]\w*)(.*)/',$par,$match);
                if (isset($match[1])) {
                    $search = array('/\$_scope->([0-9]*[a-zA-Z_]\w*)/','/->value.*/');
                    $replace = array('$_smarty_tpl->getVariable(\'\1\', null, true, false)','');
                    $this->prefix_number++;
                    $this->compiler->prefix_code[] = '$_tmp'.$this->prefix_number.'='.preg_replace($search, $replace, $par).';';
                    $isset_par = '$_tmp'.$this->prefix_number.$match[2];
                } else {
                    $this->prefix_number++;
                    $this->compiler->prefix_code[] = '$_tmp'.$this->prefix_number.'='. $par .';';
                    $isset_par = '$_tmp'.$this->prefix_number;
                }
                res = f . "(". $isset_par .")";
            } elseif (in_array($func_name,array('empty','reset','current','end','prev','next'))){
                if (count(p) != 1) {
                    $this->compiler->trigger_template_error ("Illegal number of paramer in \"{$func_name}\"");
                }
                res = $func_name.'('.p[0].')';
            } else {
                res = f . "(". implode(',',p) .")";
            }
        } else {
            $this->compiler->trigger_template_error ("unknown function \"" . f . "\"");
        }
    }
}

//
// namespace function
//
function(res)     ::= NAMESPACE(f) OPENP params(p) CLOSEP. {
    if (!$this->security || $this->compiler->tpl_obj->security_policy->isTrustedPhpFunction(f, $this->compiler)) {
        if (is_callable(f)) {
            res = f . "(". implode(',',p) .")";
        } else {
            $this->compiler->trigger_template_error ("unknown function \"" . f . "\"");
        }
    }
}

//
// method
//
method(res)     ::= ID(f) OPENP params(p) CLOSEP. {
    if ($this->security && substr(f,0,1) == '_') {
        $this->compiler->trigger_template_error (self::Err1);
    }
    res = f . "(". implode(',',p) .")";
}

method(res)     ::= DOLLAR ID(f) OPENP params(p) CLOSEP.  {
    if ($this->security) {
        $this->compiler->trigger_template_error (self::Err2);
    }
    $this->prefix_number++;
    $this->compiler->prefix_code[] = '$_tmp'.$this->prefix_number.'='.$this->compileVariable("'".f."'").';';
    res = '$_tmp'.$this->prefix_number.'('. implode(',',p) .')';
}

// function/method parameter
                    // multiple parameters
params(res)       ::= params(p) COMMA expr(e). {
    res = array_merge(p,array(e));
}

                    // single parameter
params(res)       ::= expr(e). {
    res = array(e);
}

                    // kein parameter
params(res)       ::= . {
    res = array();
}

//
// modifier
// 
modifierlist(res) ::= modifierlist(l) modifier(m) modparameters(p). {
    res = array_merge(l,array(array_merge(m,p)));
}

modifierlist(res) ::= modifier(m) modparameters(p). {
    res = array(array_merge(m,p));
}
 
modifier(res)    ::= VERT AT ID(m). {
    res = array(m);
}

modifier(res)    ::= VERT ID(m). {
    res =  array(m);
}

//
// modifier parameter
//
                    // multiple parameter
modparameters(res) ::= modparameters(mps) modparameter(mp). {
    res = array_merge(mps,mp);
}

                    // no parameter
modparameters(res)      ::= . {
    res = array();
}

                    // parameter expression
modparameter(res) ::= COLON value(mp). {
    res = array(mp);
}

modparameter(res) ::= COLON array(mp). {
    res = array(mp);
}

                  // static class method call
static_class_access(res)       ::= method(m). {
    res = m;
}

                  // static class method call with object chainig
static_class_access(res)       ::= method(m) objectchain(oc). {
    res = m.oc;
}

                  // static class constant
static_class_access(res)       ::= ID(v). {
    res = v;
}

                  // static class variables
static_class_access(res)       ::=  DOLLAR ID(v) arrayindex(a). {
    res = '$'.v.a;
}

                  // static class variables with object chain
static_class_access(res)       ::= DOLLAR ID(v) arrayindex(a) objectchain(oc). {
    res = '$'.v.a.oc;
}


// if conditions and operators
ifcond(res)        ::= EQUALS. {
    res = '==';
}

ifcond(res)        ::= NOTEQUALS. {
    res = '!=';
}

ifcond(res)        ::= GREATERTHAN. {
    res = '>';
}

ifcond(res)        ::= LESSTHAN. {
    res = '<';
}

ifcond(res)        ::= GREATEREQUAL. {
    res = '>=';
}

ifcond(res)        ::= LESSEQUAL. {
    res = '<=';
}

ifcond(res)        ::= IDENTITY. {
    res = '===';
}

ifcond(res)        ::= NONEIDENTITY. {
    res = '!==';
}

ifcond(res)        ::= MOD. {
    res = '%';
}

lop(res)        ::= LAND. {
    res = '&&';
}

lop(res)        ::= LOR. {
    res = '||';
}

lop(res)        ::= LXOR. {
    res = ' XOR ';
}

//
// ARRAY element assignment
//
array(res)           ::=  OPENB arrayelements(a) CLOSEB.  {
    res = 'array('.a.')';
}

arrayelements(res)   ::=  arrayelement(a).  {
    res = a;
}

arrayelements(res)   ::=  arrayelements(a1) COMMA arrayelement(a).  {
    res = a1.','.a;
}

arrayelements        ::=  .  {
    return;
}

arrayelement(res)    ::=  value(e1) APTR expr(e2). {
    res = e1.'=>'.e2;
}

arrayelement(res)    ::=  ID(i) APTR expr(e2). { 
    res = '\''.i.'\'=>'.e2;
}

arrayelement(res)    ::=  expr(e). {
    res = e;
}


//
// double quoted strings
//
doublequoted_with_quotes(res) ::= QUOTE QUOTE. {
    res = "''";
}

doublequoted_with_quotes(res) ::= QUOTE doublequoted(s) QUOTE. {
    res = s;
}


doublequoted(res)          ::= doublequoted(o1) doublequotedcontent(o2). {
    if (o2 === false) {
       res = o1;
    } else {
       res = o1. '.' . o2;
    }
}

doublequoted(res)          ::= doublequotedcontent(o). {
    if (o === false) {
       res = "''";
    } else {
       res = o;
    }
}

doublequotedcontent(res)           ::=  BACKTICK variable(v) BACKTICK. {
    if (empty($this->db_quote_code_buffer)) {
        res = '(string)'.v;
    } else {
        $this->db_quote_code_buffer .= 'echo (string)'.v.';';
        res = false;
    }
}

doublequotedcontent(res)           ::=  BACKTICK expr(e) BACKTICK. {
    if (empty($this->db_quote_code_buffer)) {
        res = '(string)('.e.')';
    } else {
        $this->db_quote_code_buffer .= 'echo (string)('.e.');';
        res = false;
    }
}

doublequotedcontent(res)           ::=  DOLLARID(i). {
    if (empty($this->db_quote_code_buffer)) {
        res = '(string)$_scope->'. substr(i,1) . '->value';
    } else {
        $this->db_quote_code_buffer .= 'echo (string)$_scope->'. substr(i,1) . '->value;';
        res = false;
    }
}

doublequotedcontent(res)           ::=  LDEL variable(v) RDEL. {
    if (empty($this->db_quote_code_buffer)) {
        res = '(string)'.v;
    } else {
        $this->db_quote_code_buffer .= 'echo (string)'.v.';';
        res = false;
    }
}

doublequotedcontent(res)           ::=  LDEL expr(e) RDEL. {
    if (empty($this->db_quote_code_buffer)) {
        res = '(string)('.e.')';
    } else {
        $this->db_quote_code_buffer .= 'echo (string)('.e.');';
        res = false;
    }
}

doublequotedcontent(res)     ::=  smartytag(st). {
    if (empty($this->db_quote_code_buffer)) {
            $this->db_quote_code_buffer = 'ob_start();';
    }
    $this->db_quote_code_buffer .= st;
    if ($this->block_nesting_level == count($this->compiler->_tag_stack)) {
        $this->prefix_number++;
        $this->compiler->prefix_code[] = $this->db_quote_code_buffer . ' $_tmp'.$this->prefix_number.'=ob_get_clean();';
        $this->db_quote_code_buffer = '';
        res = '$_tmp'.$this->prefix_number;
    } else {
        res = false;
    }

}

doublequotedcontent(res)           ::=  TEXT(o). {
    if (empty($this->db_quote_code_buffer)) {
        res = '"'.o.'"';
    } else {
        $this->db_quote_code_buffer .= 'echo ' . sprintf('"%s"', addcslashes(o, "\0\t\n\r\"\$\\")) . ';';
        res = false;
    }
}


//
// optional space
//
optspace(res)     ::= SPACE(s).  {
    res = s;
}

optspace(res)     ::= .          {
    res = '';
}