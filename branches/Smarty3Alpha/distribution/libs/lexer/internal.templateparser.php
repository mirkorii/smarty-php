<?php
/**
* Smarty Internal Plugin Templateparser
*
* This is the template parser.
* It is generated from the internal.templateparser.y file
* @package Smarty
* @subpackage Compiler
* @author Uwe Tews
*/

/**
 * This can be used to store both the string representation of
 * a token, and any useful meta-data associated with the token.
 *
 * meta-data should be stored as an array
 */
class TP_yyToken implements ArrayAccess
{
    public $string = '';
    public $metadata = array();

    function __construct($s, $m = array())
    {
        if ($s instanceof TP_yyToken) {
            $this->string = $s->string;
            $this->metadata = $s->metadata;
        } else {
            $this->string = (string) $s;
            if ($m instanceof TP_yyToken) {
                $this->metadata = $m->metadata;
            } elseif (is_array($m)) {
                $this->metadata = $m;
            }
        }
    }

    function __toString()
    {
        return $this->_string;
    }

    function offsetExists($offset)
    {
        return isset($this->metadata[$offset]);
    }

    function offsetGet($offset)
    {
        return $this->metadata[$offset];
    }

    function offsetSet($offset, $value)
    {
        if ($offset === null) {
            if (isset($value[0])) {
                $x = ($value instanceof TP_yyToken) ?
                    $value->metadata : $value;
                $this->metadata = array_merge($this->metadata, $x);
                return;
            }
            $offset = count($this->metadata);
        }
        if ($value === null) {
            return;
        }
        if ($value instanceof TP_yyToken) {
            if ($value->metadata) {
                $this->metadata[$offset] = $value->metadata;
            }
        } elseif ($value) {
            $this->metadata[$offset] = $value;
        }
    }

    function offsetUnset($offset)
    {
        unset($this->metadata[$offset]);
    }
}

/** The following structure represents a single element of the
 * parser's stack.  Information stored includes:
 *
 *   +  The state number for the parser at this level of the stack.
 *
 *   +  The value of the token stored at this level of the stack.
 *      (In other words, the "major" token.)
 *
 *   +  The semantic value stored at this level of the stack.  This is
 *      the information used by the action routines in the grammar.
 *      It is sometimes called the "minor" token.
 */
class TP_yyStackEntry
{
    public $stateno;       /* The state-number */
    public $major;         /* The major token value.  This is the code
                     ** number for the token at this stack level */
    public $minor; /* The user-supplied minor token value.  This
                     ** is the value of the token  */
};

// code external to the class is included here

// declare_class is output here
#line 12 "internal.templateparser.y"
class Smarty_Internal_Templateparser#line 109 "internal.templateparser.php"
{
/* First off, code is included which follows the "include_class" declaration
** in the input file. */
#line 14 "internal.templateparser.y"

    // states whether the parse was successful or not
    public $successful = true;
    public $retvalue = 0;
    private $lex;
    private $internalError = false;

    function __construct($lex, $compiler) {
        // set instance object
        self::instance($this); 
        $this->lex = $lex;
        $this->smarty = Smarty::instance(); 
        $this->compiler = $compiler;
        $this->template = $this->compiler->template;
        $this->cacher = $this->template->cacher_object; 
				$this->nocache = false;
				$this->prefix_code = array();
				$this->prefix_number = 0;
    }
    public static function &instance($new_instance = null)
    {
        static $instance = null;
        if (isset($new_instance) && is_object($new_instance))
            $instance = $new_instance;
        return $instance;
    }
    
#line 142 "internal.templateparser.php"

/* Next is all token values, as class constants
*/
/* 
** These constants (all generated automatically by the parser generator)
** specify the various kinds of tokens (terminals) that the parser
** understands. 
**
** Each symbol here is a terminal symbol in the grammar.
*/
    const TP_OTHER                          =  1;
    const TP_LDELSLASH                      =  2;
    const TP_LDEL                           =  3;
    const TP_RDEL                           =  4;
    const TP_XML                            =  5;
    const TP_PHP                            =  6;
    const TP_SHORTTAGSTART                  =  7;
    const TP_SHORTTAGEND                    =  8;
    const TP_COMMENTEND                     =  9;
    const TP_COMMENTSTART                   = 10;
    const TP_NUMBER                         = 11;
    const TP_MATH                           = 12;
    const TP_UNIMATH                        = 13;
    const TP_INCDEC                         = 14;
    const TP_OPENP                          = 15;
    const TP_CLOSEP                         = 16;
    const TP_OPENB                          = 17;
    const TP_CLOSEB                         = 18;
    const TP_DOLLAR                         = 19;
    const TP_DOT                            = 20;
    const TP_COMMA                          = 21;
    const TP_COLON                          = 22;
    const TP_DOUBLECOLON                    = 23;
    const TP_SEMICOLON                      = 24;
    const TP_VERT                           = 25;
    const TP_EQUAL                          = 26;
    const TP_SPACE                          = 27;
    const TP_PTR                            = 28;
    const TP_APTR                           = 29;
    const TP_ID                             = 30;
    const TP_EQUALS                         = 31;
    const TP_NOTEQUALS                      = 32;
    const TP_GREATERTHAN                    = 33;
    const TP_LESSTHAN                       = 34;
    const TP_GREATEREQUAL                   = 35;
    const TP_LESSEQUAL                      = 36;
    const TP_IDENTITY                       = 37;
    const TP_NONEIDENTITY                   = 38;
    const TP_NOT                            = 39;
    const TP_LAND                           = 40;
    const TP_LOR                            = 41;
    const TP_QUOTE                          = 42;
    const TP_SINGLEQUOTE                    = 43;
    const TP_BOOLEAN                        = 44;
    const TP_NULL                           = 45;
    const TP_IN                             = 46;
    const TP_ANDSYM                         = 47;
    const TP_BACKTICK                       = 48;
    const TP_HATCH                          = 49;
    const TP_AT                             = 50;
    const TP_ISODD                          = 51;
    const TP_ISNOTODD                       = 52;
    const TP_ISEVEN                         = 53;
    const TP_ISNOTEVEN                      = 54;
    const TP_ISODDBY                        = 55;
    const TP_ISNOTODDBY                     = 56;
    const TP_ISEVENBY                       = 57;
    const TP_ISNOTEVENBY                    = 58;
    const TP_ISDIVBY                        = 59;
    const TP_ISNOTDIVBY                     = 60;
    const TP_LITERALSTART                   = 61;
    const TP_LITERALEND                     = 62;
    const TP_LDELIMTAG                      = 63;
    const TP_RDELIMTAG                      = 64;
    const TP_PHPSTART                       = 65;
    const TP_PHPEND                         = 66;
    const YY_NO_ACTION = 373;
    const YY_ACCEPT_ACTION = 372;
    const YY_ERROR_ACTION = 371;

/* Next are that tables used to determine what action to take based on the
** current state and lookahead token.  These tables are used to implement
** functions that take a state number and lookahead value and return an
** action integer.  
**
** Suppose the action integer is N.  Then the action is determined as
** follows
**
**   0 <= N < self::YYNSTATE                              Shift N.  That is,
**                                                        push the lookahead
**                                                        token onto the stack
**                                                        and goto state N.
**
**   self::YYNSTATE <= N < self::YYNSTATE+self::YYNRULE   Reduce by rule N-YYNSTATE.
**
**   N == self::YYNSTATE+self::YYNRULE                    A syntax error has occurred.
**
**   N == self::YYNSTATE+self::YYNRULE+1                  The parser accepts its
**                                                        input. (and concludes parsing)
**
**   N == self::YYNSTATE+self::YYNRULE+2                  No such action.  Denotes unused
**                                                        slots in the yy_action[] table.
**
** The action table is constructed as a single large static array $yy_action.
** Given state S and lookahead X, the action is computed as
**
**      self::$yy_action[self::$yy_shift_ofst[S] + X ]
**
** If the index value self::$yy_shift_ofst[S]+X is out of range or if the value
** self::$yy_lookahead[self::$yy_shift_ofst[S]+X] is not equal to X or if
** self::$yy_shift_ofst[S] is equal to self::YY_SHIFT_USE_DFLT, it means that
** the action is not in the table and that self::$yy_default[S] should be used instead.  
**
** The formula above is for computing the action when the lookahead is
** a terminal symbol.  If the lookahead is a non-terminal (as occurs after
** a reduce action) then the static $yy_reduce_ofst array is used in place of
** the static $yy_shift_ofst array and self::YY_REDUCE_USE_DFLT is used in place of
** self::YY_SHIFT_USE_DFLT.
**
** The following are the tables generated in this section:
**
**  self::$yy_action        A single table containing all actions.
**  self::$yy_lookahead     A table containing the lookahead for each entry in
**                          yy_action.  Used to detect hash collisions.
**  self::$yy_shift_ofst    For each state, the offset into self::$yy_action for
**                          shifting terminals.
**  self::$yy_reduce_ofst   For each state, the offset into self::$yy_action for
**                          shifting non-terminals after a reduce.
**  self::$yy_default       Default action for each state.
*/
    const YY_SZ_ACTTAB = 794;
static public $yy_action = array(
 /*     0 */   129,  236,  195,  141,   18,  120,  196,  168,   61,   26,
 /*    10 */    16,   52,  120,  163,  120,  167,  145,  146,  225,  224,
 /*    20 */   216,  184,  214,  212,  213,  217,  186,  157,  160,  166,
 /*    30 */   153,    3,    4,    5,    2,   11,   10,  145,  146,   60,
 /*    40 */    99,  230,  228,  174,  149,  204,  131,  203,  157,  160,
 /*    50 */   166,  153,    3,    4,    5,    2,   11,   10,  372,   42,
 /*    60 */   155,  177,   56,   88,  170,  171,   58,  101,  145,  146,
 /*    70 */   174,  163,  204,  167,  100,  199,   32,    9,  165,  157,
 /*    80 */   160,  166,  153,    3,    4,    5,    2,   11,   10,  145,
 /*    90 */   146,  151,  109,   33,   23,    8,  163,   12,  167,   53,
 /*   100 */   157,  160,  166,  153,    3,    4,    5,    2,   11,   10,
 /*   110 */   117,  154,  151,  219,   33,  140,    8,   24,   12,    6,
 /*   120 */    55,  185,   35,   49,  159,  156,  139,  190,  151,  126,
 /*   130 */    33,  114,   21,  205,   12,   19,   53,   31,  221,  189,
 /*   140 */     6,  137,   15,   35,   49,  159,  156,  117,  120,   23,
 /*   150 */   126,   53,  163,   28,  167,  125,   30,  147,   44,   35,
 /*   160 */    49,  159,  156,   70,  110,   23,  126,  183,  175,  187,
 /*   170 */    80,  151,  152,   33,  201,   21,  185,   12,  218,   53,
 /*   180 */    59,   23,  205,  222,   19,   17,  147,  151,   24,   33,
 /*   190 */   118,   21,  185,   12,  192,   53,  183,  175,  200,   76,
 /*   200 */    53,  152,   35,   49,  159,  156,  115,  218,  185,  126,
 /*   210 */   234,   79,  137,  169,  231,   29,   23,  123,   35,   49,
 /*   220 */   159,  156,   41,  223,   23,  126,   37,  173,  137,   59,
 /*   230 */   151,  218,   33,  197,   21,  104,   12,  123,   53,   25,
 /*   240 */   204,  215,  120,  185,  176,  147,  151,   24,   33,  116,
 /*   250 */    21,  185,   12,  209,   53,  183,  175,   78,   83,   14,
 /*   260 */   152,   35,   49,  159,  156,  113,  218,   86,  126,  123,
 /*   270 */   134,  234,  227,   22,  120,   93,  147,   35,   49,  159,
 /*   280 */   156,  230,  228,   29,  126,  210,  183,  175,  123,   75,
 /*   290 */   176,  152,  218,  147,   44,  123,  179,  218,  226,   64,
 /*   300 */   122,  120,  233,  183,  175,  151,   80,   33,  152,   21,
 /*   310 */    14,   12,  237,   57,  218,   82,   32,  110,   86,  222,
 /*   320 */   120,  151,    1,  130,   36,   21,  132,   12,  229,   53,
 /*   330 */   191,   90,   94,  111,  112,   47,   35,   49,  159,  156,
 /*   340 */   121,  210,  210,  126,  112,   28,  150,  218,   30,   14,
 /*   350 */   191,  123,   35,   49,  159,  156,  197,   86,   14,  126,
 /*   360 */   163,  202,  167,  148,  177,  120,   86,   20,  162,  123,
 /*   370 */   108,  225,  224,  216,  184,  214,  212,  213,  217,  147,
 /*   380 */    44,  147,  120,  226,  123,   69,  163,   89,  167,  183,
 /*   390 */   175,  232,   80,   81,  152,   14,  152,  210,  147,   44,
 /*   400 */   218,   48,  218,   86,   68,  222,   27,   46,  183,  175,
 /*   410 */   147,   80,   38,  152,   45,  187,  191,  147,   44,  218,
 /*   420 */   135,  136,  191,   74,  222,  152,  176,  183,  175,  191,
 /*   430 */    80,  218,  152,  112,  128,   51,   63,  107,  218,  147,
 /*   440 */    44,  235,  204,  222,   77,   72,  147,   87,  161,  183,
 /*   450 */   175,  127,   80,    7,  152,  218,  183,  175,   92,   80,
 /*   460 */   218,  152,  133,  147,   43,  222,  194,  218,  210,   65,
 /*   470 */   147,  181,  208,  183,  175,  182,   80,  176,  152,   97,
 /*   480 */   158,  188,  147,   44,  218,  152,  151,  180,   71,  222,
 /*   490 */    21,  218,  183,  175,   53,   80,  144,  152,   27,  172,
 /*   500 */   211,  147,   85,  218,   50,  121,  178,   53,  222,   34,
 /*   510 */    62,  183,  175,  147,   80,  187,  152,   35,   49,  159,
 /*   520 */   156,   13,  218,  220,  126,  123,  147,   44,  152,  193,
 /*   530 */   119,  206,   73,  164,  218,   54,  183,  175,   39,   80,
 /*   540 */   241,  152,  174,  147,   44,  241,  241,  218,  241,   67,
 /*   550 */   241,  241,  222,  183,  175,  241,   80,  241,  152,  241,
 /*   560 */   147,   44,  241,  241,  218,  241,   66,  147,   91,  222,
 /*   570 */   183,  175,  241,   80,  241,  152,  241,  183,  175,  241,
 /*   580 */    80,  218,  152,  241,  241,  207,  222,  241,  218,  241,
 /*   590 */   147,   91,  241,  241,  241,  241,  241,  241,  241,  241,
 /*   600 */   183,  175,  241,   80,  241,  152,  147,   91,  142,  241,
 /*   610 */   241,  218,  241,  241,  241,  241,  183,  175,  241,   80,
 /*   620 */   241,  152,  241,  241,  138,  241,  241,  218,  147,   85,
 /*   630 */   241,  241,  241,  241,  241,  147,   91,  241,  183,  175,
 /*   640 */   241,   80,  241,  152,  241,  183,  175,  241,   80,  218,
 /*   650 */   152,  147,   40,  143,  124,  241,  218,  241,  198,  241,
 /*   660 */   241,  183,  175,  241,   80,  241,  152,  241,  241,  241,
 /*   670 */   241,  241,  218,  241,  147,   98,  241,  241,  241,  241,
 /*   680 */   241,  147,  106,  241,  183,  175,  241,   80,  241,  152,
 /*   690 */   241,  183,  175,  241,   80,  218,  152,  241,  147,   95,
 /*   700 */   241,  241,  218,  241,  241,  241,  241,  241,  183,  175,
 /*   710 */   241,   80,  241,  152,  241,  147,  105,  241,  241,  218,
 /*   720 */   241,  241,  147,   84,  241,  183,  175,  241,   80,  241,
 /*   730 */   152,  241,  183,  175,  241,   80,  218,  152,  147,   96,
 /*   740 */   241,  241,  241,  218,  241,  241,  241,  241,  183,  175,
 /*   750 */   241,   80,  241,  152,  241,  147,  103,  241,  241,  218,
 /*   760 */   241,  241,  241,  241,  241,  183,  175,  241,   80,  241,
 /*   770 */   152,  241,  147,  102,  241,  241,  218,  241,  241,  241,
 /*   780 */   241,  241,  183,  175,  241,   80,  241,  152,  241,  241,
 /*   790 */   241,  241,  241,  218,
    );
    static public $yy_lookahead = array(
 /*     0 */    24,    4,    1,    2,    3,   25,    5,    6,    7,   29,
 /*    10 */    21,   10,   25,    1,   25,    3,   40,   41,   31,   32,
 /*    20 */    33,   34,   35,   36,   37,   38,   30,   51,   52,   53,
 /*    30 */    54,   55,   56,   57,   58,   59,   60,   40,   41,   16,
 /*    40 */    92,   12,   13,   95,   16,   97,   50,   18,   51,   52,
 /*    50 */    53,   54,   55,   56,   57,   58,   59,   60,   68,   69,
 /*    60 */    70,   71,   61,   93,   63,   64,   65,   92,   40,   41,
 /*    70 */    95,    1,   97,    3,   21,   16,   47,   24,   66,   51,
 /*    80 */    52,   53,   54,   55,   56,   57,   58,   59,   60,   40,
 /*    90 */    41,   11,   30,   13,    3,   15,    1,   17,    3,   19,
 /*   100 */    51,   52,   53,   54,   55,   56,   57,   58,   59,   60,
 /*   110 */    30,    4,   11,   43,   13,   19,   15,   26,   17,   39,
 /*   120 */    19,   30,   42,   43,   44,   45,   30,    4,   11,   49,
 /*   130 */    13,   30,   15,    1,   17,    3,   19,   46,   43,   18,
 /*   140 */    39,   50,   21,   42,   43,   44,   45,   30,   25,    3,
 /*   150 */    49,   19,    1,   17,    3,   82,   20,   73,   74,   42,
 /*   160 */    43,   44,   45,   79,   28,    3,   49,   83,   84,   96,
 /*   170 */    86,   11,   88,   13,   42,   15,   30,   17,   94,   19,
 /*   180 */    48,    3,    1,   99,    3,   15,   73,   11,   26,   13,
 /*   190 */    30,   15,   30,   17,    4,   19,   83,   84,   90,   86,
 /*   200 */    19,   88,   42,   43,   44,   45,   30,   94,   30,   49,
 /*   210 */    73,   72,   50,   62,    4,   22,    3,   27,   42,   43,
 /*   220 */    44,   45,   78,   42,    3,   49,   89,   14,   50,   48,
 /*   230 */    11,   94,   13,   16,   15,   92,   17,   27,   19,   26,
 /*   240 */    97,  104,   25,   30,  105,   73,   11,   26,   13,   30,
 /*   250 */    15,   30,   17,    4,   19,   83,   84,   72,   86,   15,
 /*   260 */    88,   42,   43,   44,   45,   30,   94,   23,   49,   27,
 /*   270 */    28,   73,    4,   29,   25,   75,   73,   42,   43,   44,
 /*   280 */    45,   12,   13,   22,   49,   85,   83,   84,   27,   86,
 /*   290 */   105,   88,   94,   73,   74,   27,   76,   94,   98,   79,
 /*   300 */    80,   25,  104,   83,   84,   11,   86,   13,   88,   15,
 /*   310 */    15,   17,   97,   19,   94,   81,   47,   28,   23,   99,
 /*   320 */    25,   11,   27,   28,   30,   15,   73,   17,    4,   19,
 /*   330 */    96,   75,   75,   77,   77,   81,   42,   43,   44,   45,
 /*   340 */    30,   85,   85,   49,   77,   17,    4,   94,   20,   15,
 /*   350 */    96,   27,   42,   43,   44,   45,   16,   23,   15,   49,
 /*   360 */     1,   18,    3,   70,   71,   25,   23,  100,    9,   27,
 /*   370 */    78,   31,   32,   33,   34,   35,   36,   37,   38,   73,
 /*   380 */    74,   73,   25,   98,   27,   79,    1,   75,    3,   83,
 /*   390 */    84,   83,   86,   72,   88,   15,   88,   85,   73,   74,
 /*   400 */    94,   81,   94,   23,   79,   99,   26,   81,   83,   84,
 /*   410 */    73,   86,   93,   88,   81,   96,   96,   73,   74,   94,
 /*   420 */    83,   84,   96,   79,   99,   88,  105,   83,   84,   96,
 /*   430 */    86,   94,   88,   77,   73,   19,   30,   92,   94,   73,
 /*   440 */    74,   48,   97,   99,   72,   79,   73,   74,   49,   83,
 /*   450 */    84,   30,   86,  101,   88,   94,   83,   84,   75,   86,
 /*   460 */    94,   88,   30,   73,   74,   99,   30,   94,   85,   79,
 /*   470 */    73,    8,   16,   83,   84,    4,   86,  105,   88,   30,
 /*   480 */    83,   30,   73,   74,   94,   88,   11,    4,   79,   99,
 /*   490 */    15,   94,   83,   84,   19,   86,   30,   88,   26,    4,
 /*   500 */     4,   73,   74,   94,   30,   30,  105,   19,   99,   87,
 /*   510 */    90,   83,   84,   73,   86,   96,   88,   42,   43,   44,
 /*   520 */    45,   15,   94,   83,   49,   27,   73,   74,   88,   85,
 /*   530 */   102,  103,   79,   76,   94,   19,   83,   84,   93,   86,
 /*   540 */   106,   88,   95,   73,   74,  106,  106,   94,  106,   79,
 /*   550 */   106,  106,   99,   83,   84,  106,   86,  106,   88,  106,
 /*   560 */    73,   74,  106,  106,   94,  106,   79,   73,   74,   99,
 /*   570 */    83,   84,  106,   86,  106,   88,  106,   83,   84,  106,
 /*   580 */    86,   94,   88,  106,  106,   91,   99,  106,   94,  106,
 /*   590 */    73,   74,  106,  106,  106,  106,  106,  106,  106,  106,
 /*   600 */    83,   84,  106,   86,  106,   88,   73,   74,   91,  106,
 /*   610 */   106,   94,  106,  106,  106,  106,   83,   84,  106,   86,
 /*   620 */   106,   88,  106,  106,   91,  106,  106,   94,   73,   74,
 /*   630 */   106,  106,  106,  106,  106,   73,   74,  106,   83,   84,
 /*   640 */   106,   86,  106,   88,  106,   83,   84,  106,   86,   94,
 /*   650 */    88,   73,   74,   91,   76,  106,   94,  106,  103,  106,
 /*   660 */   106,   83,   84,  106,   86,  106,   88,  106,  106,  106,
 /*   670 */   106,  106,   94,  106,   73,   74,  106,  106,  106,  106,
 /*   680 */   106,   73,   74,  106,   83,   84,  106,   86,  106,   88,
 /*   690 */   106,   83,   84,  106,   86,   94,   88,  106,   73,   74,
 /*   700 */   106,  106,   94,  106,  106,  106,  106,  106,   83,   84,
 /*   710 */   106,   86,  106,   88,  106,   73,   74,  106,  106,   94,
 /*   720 */   106,  106,   73,   74,  106,   83,   84,  106,   86,  106,
 /*   730 */    88,  106,   83,   84,  106,   86,   94,   88,   73,   74,
 /*   740 */   106,  106,  106,   94,  106,  106,  106,  106,   83,   84,
 /*   750 */   106,   86,  106,   88,  106,   73,   74,  106,  106,   94,
 /*   760 */   106,  106,  106,  106,  106,   83,   84,  106,   86,  106,
 /*   770 */    88,  106,   73,   74,  106,  106,   94,  106,  106,  106,
 /*   780 */   106,  106,   83,   84,  106,   86,  106,   88,  106,  106,
 /*   790 */   106,  106,  106,   94,
);
    const YY_SHIFT_USE_DFLT = -25;
    const YY_SHIFT_MAX = 144;
    static public $yy_shift_ofst = array(
 /*     0 */     1,  101,   80,   80,   80,   80,   80,   80,   80,   80,
 /*    10 */    80,   80,  235,  117,  117,  235,  117,  117,  294,  117,
 /*    20 */   117,  117,  117,  117,  117,  117,  117,  117,  176,  219,
 /*    30 */   160,  310,  475,  475,  475,  132,  295,  181,  136,  136,
 /*    40 */   357,  261,    1,  340,  -13,   91,  213,  162,  178,   95,
 /*    50 */   242,  146,  385,  146,  146,  146,  385,  146,  385,  488,
 /*    60 */   289,  488,  289,  498,   -3,   28,  -24,   49,   49,   49,
 /*    70 */    49,   49,   49,   49,   49,   29,  269,   12,   70,  151,
 /*    80 */   269,  359,  221,  269,  249,  -20,   96,  217,  328,  342,
 /*    90 */   324,  -11,  268,  210,  190,  123,  276,  506,  276,  289,
 /*   100 */   516,  289,  276,  276,  289,  276,  276,  289,  193,  170,
 /*   110 */    62,  -25,  -25,  244,  380,  343,  334,  334,  334,  121,
 /*   120 */    -4,  334,   53,  432,  496,  471,  421,  399,  393,  416,
 /*   130 */   406,  436,  463,  472,  466,  495,  483,  451,  456,  170,
 /*   140 */   449,  474,   23,   59,  107,
);
    const YY_REDUCE_USE_DFLT = -53;
    const YY_REDUCE_MAX = 112;
    static public $yy_reduce_ofst = array(
 /*     0 */   -10,  220,  366,  344,  409,  325,  306,   84,  390,  487,
 /*    10 */   453,  470,  428,  517,  533,  555,  494,  562,  578,  649,
 /*    20 */   665,  373,  642,  625,  601,  699,  608,  682,  203,  172,
 /*    30 */   113,  337,  308,  397,  440,  137,  256,  198,  -25,  -52,
 /*    40 */   257,  200,  293,  267,  267,  319,   73,  319,  319,  185,
 /*    50 */   312,  326,  321,  320,  234,  333,  139,  254,  372,  361,
 /*    60 */   143,  253,  345,  383,  352,  352,  352,  352,  352,  352,
 /*    70 */   352,  352,  352,  352,  352,  422,  422,  401,  401,  401,
 /*    80 */   422,  401,  419,  422,  356,  356,  420,  356,  447,  444,
 /*    90 */   444,  356,  444,  444,  444,  356,  356,  445,  356,  215,
 /*   100 */   457,  215,  356,  356,  215,  356,  356,  215,  285,  -30,
 /*   110 */   108,  144,  292,
);
    static public $yyExpectedTokens = array(
        /* 0 */ array(1, 2, 3, 5, 6, 7, 10, 61, 63, 64, 65, ),
        /* 1 */ array(11, 13, 15, 17, 19, 30, 39, 42, 43, 44, 45, 49, ),
        /* 2 */ array(11, 13, 15, 17, 19, 30, 39, 42, 43, 44, 45, 49, ),
        /* 3 */ array(11, 13, 15, 17, 19, 30, 39, 42, 43, 44, 45, 49, ),
        /* 4 */ array(11, 13, 15, 17, 19, 30, 39, 42, 43, 44, 45, 49, ),
        /* 5 */ array(11, 13, 15, 17, 19, 30, 39, 42, 43, 44, 45, 49, ),
        /* 6 */ array(11, 13, 15, 17, 19, 30, 39, 42, 43, 44, 45, 49, ),
        /* 7 */ array(11, 13, 15, 17, 19, 30, 39, 42, 43, 44, 45, 49, ),
        /* 8 */ array(11, 13, 15, 17, 19, 30, 39, 42, 43, 44, 45, 49, ),
        /* 9 */ array(11, 13, 15, 17, 19, 30, 39, 42, 43, 44, 45, 49, ),
        /* 10 */ array(11, 13, 15, 17, 19, 30, 39, 42, 43, 44, 45, 49, ),
        /* 11 */ array(11, 13, 15, 17, 19, 30, 39, 42, 43, 44, 45, 49, ),
        /* 12 */ array(11, 13, 15, 17, 19, 30, 42, 43, 44, 45, 49, ),
        /* 13 */ array(11, 13, 15, 17, 19, 30, 42, 43, 44, 45, 49, ),
        /* 14 */ array(11, 13, 15, 17, 19, 30, 42, 43, 44, 45, 49, ),
        /* 15 */ array(11, 13, 15, 17, 19, 30, 42, 43, 44, 45, 49, ),
        /* 16 */ array(11, 13, 15, 17, 19, 30, 42, 43, 44, 45, 49, ),
        /* 17 */ array(11, 13, 15, 17, 19, 30, 42, 43, 44, 45, 49, ),
        /* 18 */ array(11, 13, 15, 17, 19, 30, 42, 43, 44, 45, 49, ),
        /* 19 */ array(11, 13, 15, 17, 19, 30, 42, 43, 44, 45, 49, ),
        /* 20 */ array(11, 13, 15, 17, 19, 30, 42, 43, 44, 45, 49, ),
        /* 21 */ array(11, 13, 15, 17, 19, 30, 42, 43, 44, 45, 49, ),
        /* 22 */ array(11, 13, 15, 17, 19, 30, 42, 43, 44, 45, 49, ),
        /* 23 */ array(11, 13, 15, 17, 19, 30, 42, 43, 44, 45, 49, ),
        /* 24 */ array(11, 13, 15, 17, 19, 30, 42, 43, 44, 45, 49, ),
        /* 25 */ array(11, 13, 15, 17, 19, 30, 42, 43, 44, 45, 49, ),
        /* 26 */ array(11, 13, 15, 17, 19, 30, 42, 43, 44, 45, 49, ),
        /* 27 */ array(11, 13, 15, 17, 19, 30, 42, 43, 44, 45, 49, ),
        /* 28 */ array(11, 13, 15, 17, 19, 30, 42, 43, 44, 45, 49, ),
        /* 29 */ array(11, 13, 15, 17, 19, 30, 42, 43, 44, 45, 49, ),
        /* 30 */ array(11, 13, 15, 17, 19, 30, 42, 43, 44, 45, 49, ),
        /* 31 */ array(11, 15, 17, 19, 30, 42, 43, 44, 45, 49, ),
        /* 32 */ array(11, 15, 19, 30, 42, 43, 44, 45, 49, ),
        /* 33 */ array(11, 15, 19, 30, 42, 43, 44, 45, 49, ),
        /* 34 */ array(11, 15, 19, 30, 42, 43, 44, 45, 49, ),
        /* 35 */ array(1, 3, 19, 42, 48, ),
        /* 36 */ array(15, 23, 25, 27, 28, ),
        /* 37 */ array(1, 3, 19, 42, 48, ),
        /* 38 */ array(17, 20, 28, ),
        /* 39 */ array(17, 20, 28, ),
        /* 40 */ array(25, 27, ),
        /* 41 */ array(22, 27, ),
        /* 42 */ array(1, 2, 3, 5, 6, 7, 10, 61, 63, 64, 65, ),
        /* 43 */ array(16, 25, 31, 32, 33, 34, 35, 36, 37, 38, ),
        /* 44 */ array(25, 31, 32, 33, 34, 35, 36, 37, 38, ),
        /* 45 */ array(3, 26, 30, 46, 50, ),
        /* 46 */ array(3, 14, 26, 30, ),
        /* 47 */ array(3, 26, 30, 50, ),
        /* 48 */ array(3, 30, 50, ),
        /* 49 */ array(1, 3, 43, ),
        /* 50 */ array(27, 28, ),
        /* 51 */ array(3, 30, ),
        /* 52 */ array(1, 3, ),
        /* 53 */ array(3, 30, ),
        /* 54 */ array(3, 30, ),
        /* 55 */ array(3, 30, ),
        /* 56 */ array(1, 3, ),
        /* 57 */ array(3, 30, ),
        /* 58 */ array(1, 3, ),
        /* 59 */ array(19, ),
        /* 60 */ array(28, ),
        /* 61 */ array(19, ),
        /* 62 */ array(28, ),
        /* 63 */ array(27, ),
        /* 64 */ array(4, 40, 41, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, ),
        /* 65 */ array(16, 40, 41, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, ),
        /* 66 */ array(24, 40, 41, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, ),
        /* 67 */ array(40, 41, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, ),
        /* 68 */ array(40, 41, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, ),
        /* 69 */ array(40, 41, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, ),
        /* 70 */ array(40, 41, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, ),
        /* 71 */ array(40, 41, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, ),
        /* 72 */ array(40, 41, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, ),
        /* 73 */ array(40, 41, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, ),
        /* 74 */ array(40, 41, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, ),
        /* 75 */ array(12, 13, 18, 47, ),
        /* 76 */ array(12, 13, 47, ),
        /* 77 */ array(1, 3, 66, ),
        /* 78 */ array(1, 3, 43, ),
        /* 79 */ array(1, 3, 62, ),
        /* 80 */ array(12, 13, 47, ),
        /* 81 */ array(1, 3, 9, ),
        /* 82 */ array(3, 26, 30, ),
        /* 83 */ array(12, 13, 47, ),
        /* 84 */ array(4, 25, ),
        /* 85 */ array(25, 29, ),
        /* 86 */ array(19, 30, ),
        /* 87 */ array(16, 25, ),
        /* 88 */ array(17, 20, ),
        /* 89 */ array(4, 27, ),
        /* 90 */ array(4, 27, ),
        /* 91 */ array(21, 25, ),
        /* 92 */ array(4, 27, ),
        /* 93 */ array(4, 27, ),
        /* 94 */ array(4, 27, ),
        /* 95 */ array(4, 25, ),
        /* 96 */ array(25, ),
        /* 97 */ array(15, ),
        /* 98 */ array(25, ),
        /* 99 */ array(28, ),
        /* 100 */ array(19, ),
        /* 101 */ array(28, ),
        /* 102 */ array(25, ),
        /* 103 */ array(25, ),
        /* 104 */ array(28, ),
        /* 105 */ array(25, ),
        /* 106 */ array(25, ),
        /* 107 */ array(28, ),
        /* 108 */ array(22, ),
        /* 109 */ array(15, ),
        /* 110 */ array(30, ),
        /* 111 */ array(),
        /* 112 */ array(),
        /* 113 */ array(15, 23, 29, ),
        /* 114 */ array(15, 23, 26, ),
        /* 115 */ array(15, 18, 23, ),
        /* 116 */ array(15, 23, ),
        /* 117 */ array(15, 23, ),
        /* 118 */ array(15, 23, ),
        /* 119 */ array(18, 21, ),
        /* 120 */ array(30, 50, ),
        /* 121 */ array(15, 23, ),
        /* 122 */ array(21, 24, ),
        /* 123 */ array(30, ),
        /* 124 */ array(4, ),
        /* 125 */ array(4, ),
        /* 126 */ array(30, ),
        /* 127 */ array(49, ),
        /* 128 */ array(48, ),
        /* 129 */ array(19, ),
        /* 130 */ array(30, ),
        /* 131 */ array(30, ),
        /* 132 */ array(8, ),
        /* 133 */ array(26, ),
        /* 134 */ array(30, ),
        /* 135 */ array(4, ),
        /* 136 */ array(4, ),
        /* 137 */ array(30, ),
        /* 138 */ array(16, ),
        /* 139 */ array(15, ),
        /* 140 */ array(30, ),
        /* 141 */ array(30, ),
        /* 142 */ array(16, ),
        /* 143 */ array(16, ),
        /* 144 */ array(4, ),
        /* 145 */ array(),
        /* 146 */ array(),
        /* 147 */ array(),
        /* 148 */ array(),
        /* 149 */ array(),
        /* 150 */ array(),
        /* 151 */ array(),
        /* 152 */ array(),
        /* 153 */ array(),
        /* 154 */ array(),
        /* 155 */ array(),
        /* 156 */ array(),
        /* 157 */ array(),
        /* 158 */ array(),
        /* 159 */ array(),
        /* 160 */ array(),
        /* 161 */ array(),
        /* 162 */ array(),
        /* 163 */ array(),
        /* 164 */ array(),
        /* 165 */ array(),
        /* 166 */ array(),
        /* 167 */ array(),
        /* 168 */ array(),
        /* 169 */ array(),
        /* 170 */ array(),
        /* 171 */ array(),
        /* 172 */ array(),
        /* 173 */ array(),
        /* 174 */ array(),
        /* 175 */ array(),
        /* 176 */ array(),
        /* 177 */ array(),
        /* 178 */ array(),
        /* 179 */ array(),
        /* 180 */ array(),
        /* 181 */ array(),
        /* 182 */ array(),
        /* 183 */ array(),
        /* 184 */ array(),
        /* 185 */ array(),
        /* 186 */ array(),
        /* 187 */ array(),
        /* 188 */ array(),
        /* 189 */ array(),
        /* 190 */ array(),
        /* 191 */ array(),
        /* 192 */ array(),
        /* 193 */ array(),
        /* 194 */ array(),
        /* 195 */ array(),
        /* 196 */ array(),
        /* 197 */ array(),
        /* 198 */ array(),
        /* 199 */ array(),
        /* 200 */ array(),
        /* 201 */ array(),
        /* 202 */ array(),
        /* 203 */ array(),
        /* 204 */ array(),
        /* 205 */ array(),
        /* 206 */ array(),
        /* 207 */ array(),
        /* 208 */ array(),
        /* 209 */ array(),
        /* 210 */ array(),
        /* 211 */ array(),
        /* 212 */ array(),
        /* 213 */ array(),
        /* 214 */ array(),
        /* 215 */ array(),
        /* 216 */ array(),
        /* 217 */ array(),
        /* 218 */ array(),
        /* 219 */ array(),
        /* 220 */ array(),
        /* 221 */ array(),
        /* 222 */ array(),
        /* 223 */ array(),
        /* 224 */ array(),
        /* 225 */ array(),
        /* 226 */ array(),
        /* 227 */ array(),
        /* 228 */ array(),
        /* 229 */ array(),
        /* 230 */ array(),
        /* 231 */ array(),
        /* 232 */ array(),
        /* 233 */ array(),
        /* 234 */ array(),
        /* 235 */ array(),
        /* 236 */ array(),
        /* 237 */ array(),
);
    static public $yy_default = array(
 /*     0 */   371,  371,  371,  371,  371,  371,  371,  371,  371,  371,
 /*    10 */   371,  371,  357,  321,  321,  371,  321,  321,  371,  371,
 /*    20 */   371,  371,  371,  371,  371,  371,  371,  371,  371,  371,
 /*    30 */   371,  371,  371,  371,  371,  371,  266,  371,  299,  297,
 /*    40 */   266,  266,  238,  331,  331,  303,  371,  303,  303,  371,
 /*    50 */   266,  371,  371,  371,  371,  371,  371,  371,  371,  371,
 /*    60 */   293,  371,  292,  266,  371,  371,  371,  334,  338,  329,
 /*    70 */   333,  343,  339,  335,  342,  371,  305,  371,  371,  371,
 /*    80 */   272,  371,  371,  327,  371,  358,  371,  371,  315,  371,
 /*    90 */   371,  320,  371,  371,  371,  371,  332,  303,  270,  298,
 /*   100 */   371,  312,  260,  267,  295,  360,  359,  294,  273,  303,
 /*   110 */   371,  325,  325,  271,  271,  371,  326,  271,  304,  371,
 /*   120 */   371,  371,  371,  371,  371,  371,  371,  371,  371,  371,
 /*   130 */   371,  371,  371,  371,  371,  371,  371,  371,  371,  296,
 /*   140 */   371,  371,  371,  371,  371,  352,  353,  281,  240,  330,
 /*   150 */   256,  283,  286,  337,  257,  239,  285,  340,  276,  284,
 /*   160 */   341,  282,  242,  369,  269,  247,  336,  370,  246,  243,
 /*   170 */   244,  245,  262,  261,  302,  274,  368,  241,  367,  268,
 /*   180 */   263,  248,  259,  275,  347,  310,  323,  309,  300,  354,
 /*   190 */   311,  308,  251,  264,  322,  250,  249,  287,  356,  318,
 /*   200 */   316,  291,  306,  307,  313,  366,  355,  319,  317,  365,
 /*   210 */   265,  252,  349,  350,  348,  362,  346,  351,  301,  288,
 /*   220 */   277,  289,  328,  290,  345,  344,  324,  254,  279,  253,
 /*   230 */   280,  255,  278,  361,  363,  364,  258,  314,
);
/* The next thing included is series of defines which control
** various aspects of the generated parser.
**    self::YYNOCODE      is a number which corresponds
**                        to no legal terminal or nonterminal number.  This
**                        number is used to fill in empty slots of the hash 
**                        table.
**    self::YYFALLBACK    If defined, this indicates that one or more tokens
**                        have fall-back values which should be used if the
**                        original value of the token will not parse.
**    self::YYSTACKDEPTH  is the maximum depth of the parser's stack.
**    self::YYNSTATE      the combined number of states.
**    self::YYNRULE       the number of rules in the grammar
**    self::YYERRORSYMBOL is the code number of the error symbol.  If not
**                        defined, then do no error processing.
*/
    const YYNOCODE = 107;
    const YYSTACKDEPTH = 100;
    const YYNSTATE = 238;
    const YYNRULE = 133;
    const YYERRORSYMBOL = 67;
    const YYERRSYMDT = 'yy0';
    const YYFALLBACK = 1;
    /** The next table maps tokens into fallback tokens.  If a construct
     * like the following:
     * 
     *      %fallback ID X Y Z.
     *
     * appears in the grammer, then ID becomes a fallback token for X, Y,
     * and Z.  Whenever one of the tokens X, Y, or Z is input to the parser
     * but it does not parse, the type of the token is changed to ID and
     * the parse is retried before an error is thrown.
     */
    static public $yyFallback = array(
    0,  /*          $ => nothing */
    0,  /*      OTHER => nothing */
    1,  /*  LDELSLASH => OTHER */
    1,  /*       LDEL => OTHER */
    1,  /*       RDEL => OTHER */
    1,  /*        XML => OTHER */
    1,  /*        PHP => OTHER */
    1,  /* SHORTTAGSTART => OTHER */
    1,  /* SHORTTAGEND => OTHER */
    1,  /* COMMENTEND => OTHER */
    1,  /* COMMENTSTART => OTHER */
    1,  /*     NUMBER => OTHER */
    1,  /*       MATH => OTHER */
    1,  /*    UNIMATH => OTHER */
    1,  /*     INCDEC => OTHER */
    1,  /*      OPENP => OTHER */
    1,  /*     CLOSEP => OTHER */
    1,  /*      OPENB => OTHER */
    1,  /*     CLOSEB => OTHER */
    1,  /*     DOLLAR => OTHER */
    1,  /*        DOT => OTHER */
    1,  /*      COMMA => OTHER */
    1,  /*      COLON => OTHER */
    1,  /* DOUBLECOLON => OTHER */
    1,  /*  SEMICOLON => OTHER */
    1,  /*       VERT => OTHER */
    1,  /*      EQUAL => OTHER */
    1,  /*      SPACE => OTHER */
    1,  /*        PTR => OTHER */
    1,  /*       APTR => OTHER */
    1,  /*         ID => OTHER */
    1,  /*     EQUALS => OTHER */
    1,  /*  NOTEQUALS => OTHER */
    1,  /* GREATERTHAN => OTHER */
    1,  /*   LESSTHAN => OTHER */
    1,  /* GREATEREQUAL => OTHER */
    1,  /*  LESSEQUAL => OTHER */
    1,  /*   IDENTITY => OTHER */
    1,  /* NONEIDENTITY => OTHER */
    1,  /*        NOT => OTHER */
    1,  /*       LAND => OTHER */
    1,  /*        LOR => OTHER */
    1,  /*      QUOTE => OTHER */
    1,  /* SINGLEQUOTE => OTHER */
    1,  /*    BOOLEAN => OTHER */
    1,  /*       NULL => OTHER */
    1,  /*         IN => OTHER */
    1,  /*     ANDSYM => OTHER */
    1,  /*   BACKTICK => OTHER */
    1,  /*      HATCH => OTHER */
    1,  /*         AT => OTHER */
    1,  /*      ISODD => OTHER */
    1,  /*   ISNOTODD => OTHER */
    1,  /*     ISEVEN => OTHER */
    1,  /*  ISNOTEVEN => OTHER */
    1,  /*    ISODDBY => OTHER */
    1,  /* ISNOTODDBY => OTHER */
    1,  /*   ISEVENBY => OTHER */
    1,  /* ISNOTEVENBY => OTHER */
    1,  /*    ISDIVBY => OTHER */
    1,  /* ISNOTDIVBY => OTHER */
    0,  /* LITERALSTART => nothing */
    0,  /* LITERALEND => nothing */
    0,  /*  LDELIMTAG => nothing */
    0,  /*  RDELIMTAG => nothing */
    0,  /*   PHPSTART => nothing */
    0,  /*     PHPEND => nothing */
    );
    /**
     * Turn parser tracing on by giving a stream to which to write the trace
     * and a prompt to preface each trace message.  Tracing is turned off
     * by making either argument NULL 
     *
     * Inputs:
     * 
     * - A stream resource to which trace output should be written.
     *   If NULL, then tracing is turned off.
     * - A prefix string written at the beginning of every
     *   line of trace output.  If NULL, then tracing is
     *   turned off.
     *
     * Outputs:
     * 
     * - None.
     * @param resource
     * @param string
     */
    static function Trace($TraceFILE, $zTracePrompt)
    {
        if (!$TraceFILE) {
            $zTracePrompt = 0;
        } elseif (!$zTracePrompt) {
            $TraceFILE = 0;
        }
        self::$yyTraceFILE = $TraceFILE;
        self::$yyTracePrompt = $zTracePrompt;
    }

    /**
     * Output debug information to output (php://output stream)
     */
    static function PrintTrace()
    {
        self::$yyTraceFILE = fopen('php://output', 'w');
        self::$yyTracePrompt = '<br>';
    }

    /**
     * @var resource|0
     */
    static public $yyTraceFILE;
    /**
     * String to prepend to debug output
     * @var string|0
     */
    static public $yyTracePrompt;
    /**
     * @var int
     */
    public $yyidx;                    /* Index of top element in stack */
    /**
     * @var int
     */
    public $yyerrcnt;                 /* Shifts left before out of the error */
    /**
     * @var array
     */
    public $yystack = array();  /* The parser's stack */

    /**
     * For tracing shifts, the names of all terminals and nonterminals
     * are required.  The following table supplies these names
     * @var array
     */
    public $yyTokenName = array( 
  '$',             'OTHER',         'LDELSLASH',     'LDEL',        
  'RDEL',          'XML',           'PHP',           'SHORTTAGSTART',
  'SHORTTAGEND',   'COMMENTEND',    'COMMENTSTART',  'NUMBER',      
  'MATH',          'UNIMATH',       'INCDEC',        'OPENP',       
  'CLOSEP',        'OPENB',         'CLOSEB',        'DOLLAR',      
  'DOT',           'COMMA',         'COLON',         'DOUBLECOLON', 
  'SEMICOLON',     'VERT',          'EQUAL',         'SPACE',       
  'PTR',           'APTR',          'ID',            'EQUALS',      
  'NOTEQUALS',     'GREATERTHAN',   'LESSTHAN',      'GREATEREQUAL',
  'LESSEQUAL',     'IDENTITY',      'NONEIDENTITY',  'NOT',         
  'LAND',          'LOR',           'QUOTE',         'SINGLEQUOTE', 
  'BOOLEAN',       'NULL',          'IN',            'ANDSYM',      
  'BACKTICK',      'HATCH',         'AT',            'ISODD',       
  'ISNOTODD',      'ISEVEN',        'ISNOTEVEN',     'ISODDBY',     
  'ISNOTODDBY',    'ISEVENBY',      'ISNOTEVENBY',   'ISDIVBY',     
  'ISNOTDIVBY',    'LITERALSTART',  'LITERALEND',    'LDELIMTAG',   
  'RDELIMTAG',     'PHPSTART',      'PHPEND',        'error',       
  'start',         'template',      'template_element',  'smartytag',   
  'text',          'variable',      'expr',          'attributes',  
  'statement',     'modifier',      'modparameters',  'ifexprs',     
  'statements',    'varvar',        'foraction',     'value',       
  'array',         'attribute',     'exprs',         'math',        
  'function',      'doublequoted',  'method',        'params',      
  'objectchain',   'arrayindex',    'object',        'indexdef',    
  'varvarele',     'objectelement',  'modparameter',  'ifexpr',      
  'ifcond',        'lop',           'arrayelements',  'arrayelement',
  'doublequotedcontent',  'textelement', 
    );

    /**
     * For tracing reduce actions, the names of all rules are required.
     * @var array
     */
    static public $yyRuleName = array(
 /*   0 */ "start ::= template",
 /*   1 */ "template ::= template_element",
 /*   2 */ "template ::= template template_element",
 /*   3 */ "template_element ::= smartytag",
 /*   4 */ "template_element ::= COMMENTSTART text COMMENTEND",
 /*   5 */ "template_element ::= LITERALSTART text LITERALEND",
 /*   6 */ "template_element ::= LDELIMTAG",
 /*   7 */ "template_element ::= RDELIMTAG",
 /*   8 */ "template_element ::= PHP",
 /*   9 */ "template_element ::= PHPSTART text PHPEND",
 /*  10 */ "template_element ::= SHORTTAGSTART variable SHORTTAGEND",
 /*  11 */ "template_element ::= XML",
 /*  12 */ "template_element ::= OTHER",
 /*  13 */ "smartytag ::= LDEL expr attributes RDEL",
 /*  14 */ "smartytag ::= LDEL statement RDEL",
 /*  15 */ "smartytag ::= LDEL ID attributes RDEL",
 /*  16 */ "smartytag ::= LDEL ID PTR ID attributes RDEL",
 /*  17 */ "smartytag ::= LDEL ID modifier modparameters attributes RDEL",
 /*  18 */ "smartytag ::= LDELSLASH ID attributes RDEL",
 /*  19 */ "smartytag ::= LDELSLASH ID PTR ID RDEL",
 /*  20 */ "smartytag ::= LDEL ID SPACE ifexprs RDEL",
 /*  21 */ "smartytag ::= LDEL ID SPACE statements SEMICOLON ifexprs SEMICOLON DOLLAR varvar foraction RDEL",
 /*  22 */ "foraction ::= EQUAL expr",
 /*  23 */ "foraction ::= INCDEC",
 /*  24 */ "smartytag ::= LDEL ID SPACE DOLLAR varvar IN value RDEL",
 /*  25 */ "smartytag ::= LDEL ID SPACE DOLLAR varvar IN array RDEL",
 /*  26 */ "attributes ::= attributes attribute",
 /*  27 */ "attributes ::= attribute",
 /*  28 */ "attributes ::=",
 /*  29 */ "attribute ::= SPACE ID EQUAL expr",
 /*  30 */ "statements ::= statement",
 /*  31 */ "statements ::= statements COMMA statement",
 /*  32 */ "statement ::= DOLLAR varvar EQUAL expr",
 /*  33 */ "expr ::= ID",
 /*  34 */ "expr ::= exprs",
 /*  35 */ "expr ::= expr modifier modparameters",
 /*  36 */ "exprs ::= array",
 /*  37 */ "exprs ::= value",
 /*  38 */ "exprs ::= UNIMATH value",
 /*  39 */ "exprs ::= exprs math value",
 /*  40 */ "exprs ::= exprs ANDSYM value",
 /*  41 */ "math ::= UNIMATH",
 /*  42 */ "math ::= MATH",
 /*  43 */ "value ::= variable",
 /*  44 */ "value ::= HATCH ID HATCH",
 /*  45 */ "value ::= NUMBER",
 /*  46 */ "value ::= BOOLEAN",
 /*  47 */ "value ::= NULL",
 /*  48 */ "value ::= function",
 /*  49 */ "value ::= OPENP expr CLOSEP",
 /*  50 */ "value ::= SINGLEQUOTE text SINGLEQUOTE",
 /*  51 */ "value ::= SINGLEQUOTE SINGLEQUOTE",
 /*  52 */ "value ::= QUOTE doublequoted QUOTE",
 /*  53 */ "value ::= QUOTE QUOTE",
 /*  54 */ "value ::= ID DOUBLECOLON method",
 /*  55 */ "value ::= ID DOUBLECOLON DOLLAR ID OPENP params CLOSEP",
 /*  56 */ "value ::= ID DOUBLECOLON method objectchain",
 /*  57 */ "value ::= ID DOUBLECOLON DOLLAR ID OPENP params CLOSEP objectchain",
 /*  58 */ "value ::= ID DOUBLECOLON ID",
 /*  59 */ "value ::= ID DOUBLECOLON DOLLAR ID arrayindex",
 /*  60 */ "value ::= ID DOUBLECOLON DOLLAR ID arrayindex objectchain",
 /*  61 */ "variable ::= DOLLAR varvar arrayindex",
 /*  62 */ "variable ::= DOLLAR varvar AT ID",
 /*  63 */ "variable ::= object",
 /*  64 */ "arrayindex ::= arrayindex indexdef",
 /*  65 */ "arrayindex ::=",
 /*  66 */ "indexdef ::= DOT ID",
 /*  67 */ "indexdef ::= DOT exprs",
 /*  68 */ "indexdef ::= OPENB ID CLOSEB",
 /*  69 */ "indexdef ::= OPENB exprs CLOSEB",
 /*  70 */ "varvar ::= varvarele",
 /*  71 */ "varvar ::= varvar varvarele",
 /*  72 */ "varvarele ::= ID",
 /*  73 */ "varvarele ::= LDEL expr RDEL",
 /*  74 */ "object ::= DOLLAR varvar arrayindex objectchain",
 /*  75 */ "objectchain ::= objectelement",
 /*  76 */ "objectchain ::= objectchain objectelement",
 /*  77 */ "objectelement ::= PTR ID arrayindex",
 /*  78 */ "objectelement ::= PTR method",
 /*  79 */ "function ::= ID OPENP params CLOSEP",
 /*  80 */ "method ::= ID OPENP params CLOSEP",
 /*  81 */ "params ::= expr COMMA params",
 /*  82 */ "params ::= expr",
 /*  83 */ "params ::=",
 /*  84 */ "modifier ::= VERT AT ID",
 /*  85 */ "modifier ::= VERT ID",
 /*  86 */ "modparameters ::= modparameters modparameter",
 /*  87 */ "modparameters ::=",
 /*  88 */ "modparameter ::= COLON ID",
 /*  89 */ "modparameter ::= COLON exprs",
 /*  90 */ "ifexprs ::= ifexpr",
 /*  91 */ "ifexprs ::= NOT ifexprs",
 /*  92 */ "ifexprs ::= OPENP ifexprs CLOSEP",
 /*  93 */ "ifexpr ::= expr",
 /*  94 */ "ifexpr ::= expr ifcond expr",
 /*  95 */ "ifexpr ::= ifexprs lop ifexprs",
 /*  96 */ "ifexpr ::= ifexprs ISDIVBY ifexprs",
 /*  97 */ "ifexpr ::= ifexprs ISNOTDIVBY ifexprs",
 /*  98 */ "ifexpr ::= ifexprs ISEVEN",
 /*  99 */ "ifexpr ::= ifexprs ISNOTEVEN",
 /* 100 */ "ifexpr ::= ifexprs ISEVENBY ifexprs",
 /* 101 */ "ifexpr ::= ifexprs ISNOTEVENBY ifexprs",
 /* 102 */ "ifexpr ::= ifexprs ISODD",
 /* 103 */ "ifexpr ::= ifexprs ISNOTODD",
 /* 104 */ "ifexpr ::= ifexprs ISODDBY ifexprs",
 /* 105 */ "ifexpr ::= ifexprs ISNOTODDBY ifexprs",
 /* 106 */ "ifcond ::= EQUALS",
 /* 107 */ "ifcond ::= NOTEQUALS",
 /* 108 */ "ifcond ::= GREATERTHAN",
 /* 109 */ "ifcond ::= LESSTHAN",
 /* 110 */ "ifcond ::= GREATEREQUAL",
 /* 111 */ "ifcond ::= LESSEQUAL",
 /* 112 */ "ifcond ::= IDENTITY",
 /* 113 */ "ifcond ::= NONEIDENTITY",
 /* 114 */ "lop ::= LAND",
 /* 115 */ "lop ::= LOR",
 /* 116 */ "array ::= OPENB arrayelements CLOSEB",
 /* 117 */ "arrayelements ::= arrayelement",
 /* 118 */ "arrayelements ::= arrayelements COMMA arrayelement",
 /* 119 */ "arrayelements ::=",
 /* 120 */ "arrayelement ::= expr",
 /* 121 */ "arrayelement ::= expr APTR expr",
 /* 122 */ "arrayelement ::= ID APTR expr",
 /* 123 */ "doublequoted ::= doublequoted doublequotedcontent",
 /* 124 */ "doublequoted ::= doublequotedcontent",
 /* 125 */ "doublequotedcontent ::= variable",
 /* 126 */ "doublequotedcontent ::= BACKTICK variable BACKTICK",
 /* 127 */ "doublequotedcontent ::= LDEL expr RDEL",
 /* 128 */ "doublequotedcontent ::= OTHER",
 /* 129 */ "text ::= text textelement",
 /* 130 */ "text ::= textelement",
 /* 131 */ "textelement ::= OTHER",
 /* 132 */ "textelement ::= LDEL",
    );

    /**
     * This function returns the symbolic name associated with a token
     * value.
     * @param int
     * @return string
     */
    function tokenName($tokenType)
    {
        if ($tokenType === 0) {
            return 'End of Input';
        }
        if ($tokenType > 0 && $tokenType < count($this->yyTokenName)) {
            return $this->yyTokenName[$tokenType];
        } else {
            return "Unknown";
        }
    }

    /**
     * The following function deletes the value associated with a
     * symbol.  The symbol can be either a terminal or nonterminal.
     * @param int the symbol code
     * @param mixed the symbol's value
     */
    static function yy_destructor($yymajor, $yypminor)
    {
        switch ($yymajor) {
        /* Here is inserted the actions which take place when a
        ** terminal or non-terminal is destroyed.  This can happen
        ** when the symbol is popped from the stack during a
        ** reduce or during error processing or when a parser is 
        ** being destroyed before it is finished parsing.
        **
        ** Note: during a reduce, the only symbols destroyed are those
        ** which appear on the RHS of the rule, but which are not used
        ** inside the C code.
        */
            default:  break;   /* If no destructor action specified: do nothing */
        }
    }

    /**
     * Pop the parser's stack once.
     *
     * If there is a destructor routine associated with the token which
     * is popped from the stack, then call it.
     *
     * Return the major token number for the symbol popped.
     * @param TP_yyParser
     * @return int
     */
    function yy_pop_parser_stack()
    {
        if (!count($this->yystack)) {
            return;
        }
        $yytos = array_pop($this->yystack);
        if (self::$yyTraceFILE && $this->yyidx >= 0) {
            fwrite(self::$yyTraceFILE,
                self::$yyTracePrompt . 'Popping ' . $this->yyTokenName[$yytos->major] .
                    "\n");
        }
        $yymajor = $yytos->major;
        self::yy_destructor($yymajor, $yytos->minor);
        $this->yyidx--;
        return $yymajor;
    }

    /**
     * Deallocate and destroy a parser.  Destructors are all called for
     * all stack elements before shutting the parser down.
     */
    function __destruct()
    {
        while ($this->yyidx >= 0) {
            $this->yy_pop_parser_stack();
        }
        if (is_resource(self::$yyTraceFILE)) {
            fclose(self::$yyTraceFILE);
        }
    }

    /**
     * Based on the current state and parser stack, get a list of all
     * possible lookahead tokens
     * @param int
     * @return array
     */
    function yy_get_expected_tokens($token)
    {
        $state = $this->yystack[$this->yyidx]->stateno;
        $expected = self::$yyExpectedTokens[$state];
        if (in_array($token, self::$yyExpectedTokens[$state], true)) {
            return $expected;
        }
        $stack = $this->yystack;
        $yyidx = $this->yyidx;
        do {
            $yyact = $this->yy_find_shift_action($token);
            if ($yyact >= self::YYNSTATE && $yyact < self::YYNSTATE + self::YYNRULE) {
                // reduce action
                $done = 0;
                do {
                    if ($done++ == 100) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // too much recursion prevents proper detection
                        // so give up
                        return array_unique($expected);
                    }
                    $yyruleno = $yyact - self::YYNSTATE;
                    $this->yyidx -= self::$yyRuleInfo[$yyruleno]['rhs'];
                    $nextstate = $this->yy_find_reduce_action(
                        $this->yystack[$this->yyidx]->stateno,
                        self::$yyRuleInfo[$yyruleno]['lhs']);
                    if (isset(self::$yyExpectedTokens[$nextstate])) {
                        $expected += self::$yyExpectedTokens[$nextstate];
                            if (in_array($token,
                                  self::$yyExpectedTokens[$nextstate], true)) {
                            $this->yyidx = $yyidx;
                            $this->yystack = $stack;
                            return array_unique($expected);
                        }
                    }
                    if ($nextstate < self::YYNSTATE) {
                        // we need to shift a non-terminal
                        $this->yyidx++;
                        $x = new TP_yyStackEntry;
                        $x->stateno = $nextstate;
                        $x->major = self::$yyRuleInfo[$yyruleno]['lhs'];
                        $this->yystack[$this->yyidx] = $x;
                        continue 2;
                    } elseif ($nextstate == self::YYNSTATE + self::YYNRULE + 1) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // the last token was just ignored, we can't accept
                        // by ignoring input, this is in essence ignoring a
                        // syntax error!
                        return array_unique($expected);
                    } elseif ($nextstate === self::YY_NO_ACTION) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // input accepted, but not shifted (I guess)
                        return $expected;
                    } else {
                        $yyact = $nextstate;
                    }
                } while (true);
            }
            break;
        } while (true);
        return array_unique($expected);
    }

    /**
     * Based on the parser state and current parser stack, determine whether
     * the lookahead token is possible.
     * 
     * The parser will convert the token value to an error token if not.  This
     * catches some unusual edge cases where the parser would fail.
     * @param int
     * @return bool
     */
    function yy_is_expected_token($token)
    {
        if ($token === 0) {
            return true; // 0 is not part of this
        }
        $state = $this->yystack[$this->yyidx]->stateno;
        if (in_array($token, self::$yyExpectedTokens[$state], true)) {
            return true;
        }
        $stack = $this->yystack;
        $yyidx = $this->yyidx;
        do {
            $yyact = $this->yy_find_shift_action($token);
            if ($yyact >= self::YYNSTATE && $yyact < self::YYNSTATE + self::YYNRULE) {
                // reduce action
                $done = 0;
                do {
                    if ($done++ == 100) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // too much recursion prevents proper detection
                        // so give up
                        return true;
                    }
                    $yyruleno = $yyact - self::YYNSTATE;
                    $this->yyidx -= self::$yyRuleInfo[$yyruleno]['rhs'];
                    $nextstate = $this->yy_find_reduce_action(
                        $this->yystack[$this->yyidx]->stateno,
                        self::$yyRuleInfo[$yyruleno]['lhs']);
                    if (isset(self::$yyExpectedTokens[$nextstate]) &&
                          in_array($token, self::$yyExpectedTokens[$nextstate], true)) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        return true;
                    }
                    if ($nextstate < self::YYNSTATE) {
                        // we need to shift a non-terminal
                        $this->yyidx++;
                        $x = new TP_yyStackEntry;
                        $x->stateno = $nextstate;
                        $x->major = self::$yyRuleInfo[$yyruleno]['lhs'];
                        $this->yystack[$this->yyidx] = $x;
                        continue 2;
                    } elseif ($nextstate == self::YYNSTATE + self::YYNRULE + 1) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        if (!$token) {
                            // end of input: this is valid
                            return true;
                        }
                        // the last token was just ignored, we can't accept
                        // by ignoring input, this is in essence ignoring a
                        // syntax error!
                        return false;
                    } elseif ($nextstate === self::YY_NO_ACTION) {
                        $this->yyidx = $yyidx;
                        $this->yystack = $stack;
                        // input accepted, but not shifted (I guess)
                        return true;
                    } else {
                        $yyact = $nextstate;
                    }
                } while (true);
            }
            break;
        } while (true);
        $this->yyidx = $yyidx;
        $this->yystack = $stack;
        return true;
    }

    /**
     * Find the appropriate action for a parser given the terminal
     * look-ahead token iLookAhead.
     *
     * If the look-ahead token is YYNOCODE, then check to see if the action is
     * independent of the look-ahead.  If it is, return the action, otherwise
     * return YY_NO_ACTION.
     * @param int The look-ahead token
     */
    function yy_find_shift_action($iLookAhead)
    {
        $stateno = $this->yystack[$this->yyidx]->stateno;
     
        /* if ($this->yyidx < 0) return self::YY_NO_ACTION;  */
        if (!isset(self::$yy_shift_ofst[$stateno])) {
            // no shift actions
            return self::$yy_default[$stateno];
        }
        $i = self::$yy_shift_ofst[$stateno];
        if ($i === self::YY_SHIFT_USE_DFLT) {
            return self::$yy_default[$stateno];
        }
        if ($iLookAhead == self::YYNOCODE) {
            return self::YY_NO_ACTION;
        }
        $i += $iLookAhead;
        if ($i < 0 || $i >= self::YY_SZ_ACTTAB ||
              self::$yy_lookahead[$i] != $iLookAhead) {
            if (count(self::$yyFallback) && $iLookAhead < count(self::$yyFallback)
                   && ($iFallback = self::$yyFallback[$iLookAhead]) != 0) {
                if (self::$yyTraceFILE) {
                    fwrite(self::$yyTraceFILE, self::$yyTracePrompt . "FALLBACK " .
                        $this->yyTokenName[$iLookAhead] . " => " .
                        $this->yyTokenName[$iFallback] . "\n");
                }
                return $this->yy_find_shift_action($iFallback);
            }
            return self::$yy_default[$stateno];
        } else {
            return self::$yy_action[$i];
        }
    }

    /**
     * Find the appropriate action for a parser given the non-terminal
     * look-ahead token $iLookAhead.
     *
     * If the look-ahead token is self::YYNOCODE, then check to see if the action is
     * independent of the look-ahead.  If it is, return the action, otherwise
     * return self::YY_NO_ACTION.
     * @param int Current state number
     * @param int The look-ahead token
     */
    function yy_find_reduce_action($stateno, $iLookAhead)
    {
        /* $stateno = $this->yystack[$this->yyidx]->stateno; */

        if (!isset(self::$yy_reduce_ofst[$stateno])) {
            return self::$yy_default[$stateno];
        }
        $i = self::$yy_reduce_ofst[$stateno];
        if ($i == self::YY_REDUCE_USE_DFLT) {
            return self::$yy_default[$stateno];
        }
        if ($iLookAhead == self::YYNOCODE) {
            return self::YY_NO_ACTION;
        }
        $i += $iLookAhead;
        if ($i < 0 || $i >= self::YY_SZ_ACTTAB ||
              self::$yy_lookahead[$i] != $iLookAhead) {
            return self::$yy_default[$stateno];
        } else {
            return self::$yy_action[$i];
        }
    }

    /**
     * Perform a shift action.
     * @param int The new state to shift in
     * @param int The major token to shift in
     * @param mixed the minor token to shift in
     */
    function yy_shift($yyNewState, $yyMajor, $yypMinor)
    {
        $this->yyidx++;
        if ($this->yyidx >= self::YYSTACKDEPTH) {
            $this->yyidx--;
            if (self::$yyTraceFILE) {
                fprintf(self::$yyTraceFILE, "%sStack Overflow!\n", self::$yyTracePrompt);
            }
            while ($this->yyidx >= 0) {
                $this->yy_pop_parser_stack();
            }
            /* Here code is inserted which will execute if the parser
            ** stack ever overflows */
            return;
        }
        $yytos = new TP_yyStackEntry;
        $yytos->stateno = $yyNewState;
        $yytos->major = $yyMajor;
        $yytos->minor = $yypMinor;
        array_push($this->yystack, $yytos);
        if (self::$yyTraceFILE && $this->yyidx > 0) {
            fprintf(self::$yyTraceFILE, "%sShift %d\n", self::$yyTracePrompt,
                $yyNewState);
            fprintf(self::$yyTraceFILE, "%sStack:", self::$yyTracePrompt);
            for($i = 1; $i <= $this->yyidx; $i++) {
                fprintf(self::$yyTraceFILE, " %s",
                    $this->yyTokenName[$this->yystack[$i]->major]);
            }
            fwrite(self::$yyTraceFILE,"\n");
        }
    }

    /**
     * The following table contains information about every rule that
     * is used during the reduce.
     *
     * <pre>
     * array(
     *  array(
     *   int $lhs;         Symbol on the left-hand side of the rule
     *   int $nrhs;     Number of right-hand side symbols in the rule
     *  ),...
     * );
     * </pre>
     */
    static public $yyRuleInfo = array(
  array( 'lhs' => 68, 'rhs' => 1 ),
  array( 'lhs' => 69, 'rhs' => 1 ),
  array( 'lhs' => 69, 'rhs' => 2 ),
  array( 'lhs' => 70, 'rhs' => 1 ),
  array( 'lhs' => 70, 'rhs' => 3 ),
  array( 'lhs' => 70, 'rhs' => 3 ),
  array( 'lhs' => 70, 'rhs' => 1 ),
  array( 'lhs' => 70, 'rhs' => 1 ),
  array( 'lhs' => 70, 'rhs' => 1 ),
  array( 'lhs' => 70, 'rhs' => 3 ),
  array( 'lhs' => 70, 'rhs' => 3 ),
  array( 'lhs' => 70, 'rhs' => 1 ),
  array( 'lhs' => 70, 'rhs' => 1 ),
  array( 'lhs' => 71, 'rhs' => 4 ),
  array( 'lhs' => 71, 'rhs' => 3 ),
  array( 'lhs' => 71, 'rhs' => 4 ),
  array( 'lhs' => 71, 'rhs' => 6 ),
  array( 'lhs' => 71, 'rhs' => 6 ),
  array( 'lhs' => 71, 'rhs' => 4 ),
  array( 'lhs' => 71, 'rhs' => 5 ),
  array( 'lhs' => 71, 'rhs' => 5 ),
  array( 'lhs' => 71, 'rhs' => 11 ),
  array( 'lhs' => 82, 'rhs' => 2 ),
  array( 'lhs' => 82, 'rhs' => 1 ),
  array( 'lhs' => 71, 'rhs' => 8 ),
  array( 'lhs' => 71, 'rhs' => 8 ),
  array( 'lhs' => 75, 'rhs' => 2 ),
  array( 'lhs' => 75, 'rhs' => 1 ),
  array( 'lhs' => 75, 'rhs' => 0 ),
  array( 'lhs' => 85, 'rhs' => 4 ),
  array( 'lhs' => 80, 'rhs' => 1 ),
  array( 'lhs' => 80, 'rhs' => 3 ),
  array( 'lhs' => 76, 'rhs' => 4 ),
  array( 'lhs' => 74, 'rhs' => 1 ),
  array( 'lhs' => 74, 'rhs' => 1 ),
  array( 'lhs' => 74, 'rhs' => 3 ),
  array( 'lhs' => 86, 'rhs' => 1 ),
  array( 'lhs' => 86, 'rhs' => 1 ),
  array( 'lhs' => 86, 'rhs' => 2 ),
  array( 'lhs' => 86, 'rhs' => 3 ),
  array( 'lhs' => 86, 'rhs' => 3 ),
  array( 'lhs' => 87, 'rhs' => 1 ),
  array( 'lhs' => 87, 'rhs' => 1 ),
  array( 'lhs' => 83, 'rhs' => 1 ),
  array( 'lhs' => 83, 'rhs' => 3 ),
  array( 'lhs' => 83, 'rhs' => 1 ),
  array( 'lhs' => 83, 'rhs' => 1 ),
  array( 'lhs' => 83, 'rhs' => 1 ),
  array( 'lhs' => 83, 'rhs' => 1 ),
  array( 'lhs' => 83, 'rhs' => 3 ),
  array( 'lhs' => 83, 'rhs' => 3 ),
  array( 'lhs' => 83, 'rhs' => 2 ),
  array( 'lhs' => 83, 'rhs' => 3 ),
  array( 'lhs' => 83, 'rhs' => 2 ),
  array( 'lhs' => 83, 'rhs' => 3 ),
  array( 'lhs' => 83, 'rhs' => 7 ),
  array( 'lhs' => 83, 'rhs' => 4 ),
  array( 'lhs' => 83, 'rhs' => 8 ),
  array( 'lhs' => 83, 'rhs' => 3 ),
  array( 'lhs' => 83, 'rhs' => 5 ),
  array( 'lhs' => 83, 'rhs' => 6 ),
  array( 'lhs' => 73, 'rhs' => 3 ),
  array( 'lhs' => 73, 'rhs' => 4 ),
  array( 'lhs' => 73, 'rhs' => 1 ),
  array( 'lhs' => 93, 'rhs' => 2 ),
  array( 'lhs' => 93, 'rhs' => 0 ),
  array( 'lhs' => 95, 'rhs' => 2 ),
  array( 'lhs' => 95, 'rhs' => 2 ),
  array( 'lhs' => 95, 'rhs' => 3 ),
  array( 'lhs' => 95, 'rhs' => 3 ),
  array( 'lhs' => 81, 'rhs' => 1 ),
  array( 'lhs' => 81, 'rhs' => 2 ),
  array( 'lhs' => 96, 'rhs' => 1 ),
  array( 'lhs' => 96, 'rhs' => 3 ),
  array( 'lhs' => 94, 'rhs' => 4 ),
  array( 'lhs' => 92, 'rhs' => 1 ),
  array( 'lhs' => 92, 'rhs' => 2 ),
  array( 'lhs' => 97, 'rhs' => 3 ),
  array( 'lhs' => 97, 'rhs' => 2 ),
  array( 'lhs' => 88, 'rhs' => 4 ),
  array( 'lhs' => 90, 'rhs' => 4 ),
  array( 'lhs' => 91, 'rhs' => 3 ),
  array( 'lhs' => 91, 'rhs' => 1 ),
  array( 'lhs' => 91, 'rhs' => 0 ),
  array( 'lhs' => 77, 'rhs' => 3 ),
  array( 'lhs' => 77, 'rhs' => 2 ),
  array( 'lhs' => 78, 'rhs' => 2 ),
  array( 'lhs' => 78, 'rhs' => 0 ),
  array( 'lhs' => 98, 'rhs' => 2 ),
  array( 'lhs' => 98, 'rhs' => 2 ),
  array( 'lhs' => 79, 'rhs' => 1 ),
  array( 'lhs' => 79, 'rhs' => 2 ),
  array( 'lhs' => 79, 'rhs' => 3 ),
  array( 'lhs' => 99, 'rhs' => 1 ),
  array( 'lhs' => 99, 'rhs' => 3 ),
  array( 'lhs' => 99, 'rhs' => 3 ),
  array( 'lhs' => 99, 'rhs' => 3 ),
  array( 'lhs' => 99, 'rhs' => 3 ),
  array( 'lhs' => 99, 'rhs' => 2 ),
  array( 'lhs' => 99, 'rhs' => 2 ),
  array( 'lhs' => 99, 'rhs' => 3 ),
  array( 'lhs' => 99, 'rhs' => 3 ),
  array( 'lhs' => 99, 'rhs' => 2 ),
  array( 'lhs' => 99, 'rhs' => 2 ),
  array( 'lhs' => 99, 'rhs' => 3 ),
  array( 'lhs' => 99, 'rhs' => 3 ),
  array( 'lhs' => 100, 'rhs' => 1 ),
  array( 'lhs' => 100, 'rhs' => 1 ),
  array( 'lhs' => 100, 'rhs' => 1 ),
  array( 'lhs' => 100, 'rhs' => 1 ),
  array( 'lhs' => 100, 'rhs' => 1 ),
  array( 'lhs' => 100, 'rhs' => 1 ),
  array( 'lhs' => 100, 'rhs' => 1 ),
  array( 'lhs' => 100, 'rhs' => 1 ),
  array( 'lhs' => 101, 'rhs' => 1 ),
  array( 'lhs' => 101, 'rhs' => 1 ),
  array( 'lhs' => 84, 'rhs' => 3 ),
  array( 'lhs' => 102, 'rhs' => 1 ),
  array( 'lhs' => 102, 'rhs' => 3 ),
  array( 'lhs' => 102, 'rhs' => 0 ),
  array( 'lhs' => 103, 'rhs' => 1 ),
  array( 'lhs' => 103, 'rhs' => 3 ),
  array( 'lhs' => 103, 'rhs' => 3 ),
  array( 'lhs' => 89, 'rhs' => 2 ),
  array( 'lhs' => 89, 'rhs' => 1 ),
  array( 'lhs' => 104, 'rhs' => 1 ),
  array( 'lhs' => 104, 'rhs' => 3 ),
  array( 'lhs' => 104, 'rhs' => 3 ),
  array( 'lhs' => 104, 'rhs' => 1 ),
  array( 'lhs' => 72, 'rhs' => 2 ),
  array( 'lhs' => 72, 'rhs' => 1 ),
  array( 'lhs' => 105, 'rhs' => 1 ),
  array( 'lhs' => 105, 'rhs' => 1 ),
    );

    /**
     * The following table contains a mapping of reduce action to method name
     * that handles the reduction.
     * 
     * If a rule is not set, it has no handler.
     */
    static public $yyReduceMap = array(
        0 => 0,
        37 => 0,
        43 => 0,
        45 => 0,
        46 => 0,
        47 => 0,
        48 => 0,
        63 => 0,
        117 => 0,
        1 => 1,
        34 => 1,
        36 => 1,
        41 => 1,
        42 => 1,
        70 => 1,
        90 => 1,
        124 => 1,
        130 => 1,
        131 => 1,
        132 => 1,
        2 => 2,
        64 => 2,
        123 => 2,
        129 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 7,
        8 => 8,
        9 => 9,
        10 => 10,
        11 => 11,
        12 => 12,
        13 => 13,
        14 => 14,
        15 => 15,
        16 => 16,
        17 => 17,
        18 => 18,
        19 => 19,
        20 => 20,
        21 => 21,
        22 => 22,
        23 => 23,
        27 => 23,
        82 => 23,
        120 => 23,
        24 => 24,
        25 => 24,
        26 => 26,
        28 => 28,
        29 => 29,
        30 => 30,
        31 => 31,
        32 => 32,
        33 => 33,
        35 => 35,
        38 => 38,
        39 => 39,
        40 => 40,
        44 => 44,
        49 => 49,
        50 => 50,
        51 => 51,
        53 => 51,
        52 => 52,
        54 => 54,
        55 => 55,
        56 => 56,
        57 => 57,
        58 => 58,
        59 => 59,
        60 => 60,
        61 => 61,
        62 => 62,
        65 => 65,
        87 => 65,
        66 => 66,
        67 => 67,
        68 => 68,
        69 => 69,
        71 => 71,
        72 => 72,
        73 => 73,
        92 => 73,
        74 => 74,
        75 => 75,
        76 => 76,
        77 => 77,
        78 => 78,
        79 => 79,
        80 => 80,
        81 => 81,
        83 => 83,
        84 => 84,
        85 => 85,
        86 => 86,
        88 => 88,
        89 => 89,
        91 => 91,
        93 => 93,
        94 => 94,
        95 => 94,
        96 => 96,
        97 => 97,
        98 => 98,
        103 => 98,
        99 => 99,
        102 => 99,
        100 => 100,
        105 => 100,
        101 => 101,
        104 => 101,
        106 => 106,
        107 => 107,
        108 => 108,
        109 => 109,
        110 => 110,
        111 => 111,
        112 => 112,
        113 => 113,
        114 => 114,
        115 => 115,
        116 => 116,
        118 => 118,
        119 => 119,
        121 => 121,
        122 => 122,
        125 => 125,
        126 => 126,
        127 => 127,
        128 => 128,
    );
    /* Beginning here are the reduction cases.  A typical example
    ** follows:
    **  #line <lineno> <grammarfile>
    **   function yy_r0($yymsp){ ... }           // User supplied code
    **  #line <lineno> <thisfile>
    */
#line 73 "internal.templateparser.y"
    function yy_r0(){ $this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1724 "internal.templateparser.php"
#line 79 "internal.templateparser.y"
    function yy_r1(){$this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;    }
#line 1727 "internal.templateparser.php"
#line 81 "internal.templateparser.y"
    function yy_r2(){$this->_retvalue = $this->yystack[$this->yyidx + -1]->minor.$this->yystack[$this->yyidx + 0]->minor;    }
#line 1730 "internal.templateparser.php"
#line 87 "internal.templateparser.y"
    function yy_r3(){if ($this->compiler->has_code) {
                                            $tmp =''; foreach ($this->prefix_code as $code) {$tmp.=$code;} $this->prefix_code=array();
                                            $this->_retvalue = $this->cacher->processNocacheCode($tmp.$this->yystack[$this->yyidx + 0]->minor, $this->compiler,$this->nocache,true);
                                         } $this->nocache=false;    }
#line 1736 "internal.templateparser.php"
#line 100 "internal.templateparser.y"
    function yy_r4(){ $this->_retvalue = '';    }
#line 1739 "internal.templateparser.php"
#line 103 "internal.templateparser.y"
    function yy_r5(){$this->_retvalue = $this->cacher->processNocacheCode($this->yystack[$this->yyidx + -1]->minor, $this->compiler,false,false);    }
#line 1742 "internal.templateparser.php"
#line 105 "internal.templateparser.y"
    function yy_r6(){$this->_retvalue = $this->cacher->processNocacheCode($this->smarty->left_delimiter, $this->compiler,false,false);    }
#line 1745 "internal.templateparser.php"
#line 107 "internal.templateparser.y"
    function yy_r7(){$this->_retvalue = $this->cacher->processNocacheCode($this->smarty->right_delimiter, $this->compiler,false,false);    }
#line 1748 "internal.templateparser.php"
#line 109 "internal.templateparser.y"
    function yy_r8(){if (!$this->template->security) { 
                                       $this->_retvalue = $this->cacher->processNocacheCode($this->yystack[$this->yyidx + 0]->minor, $this->compiler, false,true);
                                      } elseif ($this->smarty->security_policy->php_handling == SMARTY_PHP_QUOTE) {
                                       $this->_retvalue = $this->cacher->processNocacheCode(htmlspecialchars($this->yystack[$this->yyidx + 0]->minor, ENT_QUOTES), $this->compiler, false, false);
                                      }elseif ($this->smarty->security_policy->php_handling == SMARTY_PHP_PASSTHRU || $this->smarty->security_policy->php_handling == SMARTY_PHP_ALLOW) {
                                       $this->_retvalue = $this->cacher->processNocacheCode("<?php echo '".$this->yystack[$this->yyidx + 0]->minor."';?>\n", $this->compiler, false, false);
                                      }elseif ($this->smarty->security_policy->php_handling == SMARTY_PHP_REMOVE) {
                                       $this->_retvalue = '';
                                      }	    }
#line 1759 "internal.templateparser.php"
#line 119 "internal.templateparser.y"
    function yy_r9(){if (!$this->template->security) { 
                                        $this->_retvalue = $this->cacher->processNocacheCode('<?php '.$this->yystack[$this->yyidx + -1]->minor.' ?>', $this->compiler, false,true);
                                      } elseif ($this->smarty->security_policy->php_handling == SMARTY_PHP_QUOTE) {
                                        $this->_retvalue = $this->cacher->processNocacheCode(htmlspecialchars('<?php '.$this->yystack[$this->yyidx + -1]->minor.' ?>', ENT_QUOTES), $this->compiler, false, false);	
                                      }elseif ($this->smarty->security_policy->php_handling == SMARTY_PHP_PASSTHRU || $this->smarty->security_policy->php_handling == SMARTY_PHP_ALLOW) {
                                        $this->_retvalue = $this->cacher->processNocacheCode("<?php echo '<?php ".$this->yystack[$this->yyidx + -1]->minor." ?>';?>\n", $this->compiler, false, false);
                                      }elseif ($this->smarty->security_policy->php_handling == SMARTY_PHP_REMOVE) {
                                        $this->_retvalue = '';
                                      }	    }
#line 1770 "internal.templateparser.php"
#line 129 "internal.templateparser.y"
    function yy_r10(){if (!$this->template->security) { 
                                        $this->_retvalue = $this->cacher->processNocacheCode($this->compiler->compileTag('print_expression',array('value'=>$this->yystack[$this->yyidx + -1]->minor)), $this->compiler, false,true);
                                      } elseif ($this->smarty->security_policy->php_handling == SMARTY_PHP_QUOTE) {
                                        $this->_retvalue = $this->cacher->processNocacheCode(htmlspecialchars('<?php '.t.' ?>', ENT_QUOTES), $this->compiler, false, false);	
                                      }elseif ($this->smarty->security_policy->php_handling == SMARTY_PHP_PASSTHRU || $this->smarty->security_policy->php_handling == SMARTY_PHP_ALLOW) {
                                        $this->_retvalue = $this->cacher->processNocacheCode("<?php echo '<?php ".t." ?>';?>\n", $this->compiler, false, false);
                                      }elseif ($this->smarty->security_policy->php_handling == SMARTY_PHP_REMOVE) {
                                        $this->_retvalue = '';
                                      }	    }
#line 1781 "internal.templateparser.php"
#line 139 "internal.templateparser.y"
    function yy_r11(){$this->_retvalue = $this->cacher->processNocacheCode("<?php echo '".$this->yystack[$this->yyidx + 0]->minor."';?>\n", $this->compiler, true, true);    }
#line 1784 "internal.templateparser.php"
#line 141 "internal.templateparser.y"
    function yy_r12(){$this->_retvalue = $this->cacher->processNocacheCode($this->yystack[$this->yyidx + 0]->minor, $this->compiler,false,false);    }
#line 1787 "internal.templateparser.php"
#line 149 "internal.templateparser.y"
    function yy_r13(){ $this->_retvalue = $this->compiler->compileTag('print_expression',array_merge(array('value'=>$this->yystack[$this->yyidx + -2]->minor),$this->yystack[$this->yyidx + -1]->minor));    }
#line 1790 "internal.templateparser.php"
#line 151 "internal.templateparser.y"
    function yy_r14(){ $this->_retvalue = $this->compiler->compileTag('assign',$this->yystack[$this->yyidx + -1]->minor);    }
#line 1793 "internal.templateparser.php"
#line 153 "internal.templateparser.y"
    function yy_r15(){ $this->_retvalue =  $this->compiler->compileTag($this->yystack[$this->yyidx + -2]->minor,$this->yystack[$this->yyidx + -1]->minor);    }
#line 1796 "internal.templateparser.php"
#line 155 "internal.templateparser.y"
    function yy_r16(){ $this->_retvalue =  $this->compiler->compileTag($this->yystack[$this->yyidx + -4]->minor,array_merge(array('object_methode'=>$this->yystack[$this->yyidx + -2]->minor),$this->yystack[$this->yyidx + -1]->minor));    }
#line 1799 "internal.templateparser.php"
#line 157 "internal.templateparser.y"
    function yy_r17(){ $this->_retvalue =  '<?php ob_start();?>'.$this->compiler->compileTag($this->yystack[$this->yyidx + -4]->minor,$this->yystack[$this->yyidx + -1]->minor).'<?php echo ';
																					                       if ($this->smarty->plugin_handler->loadSmartyPlugin($this->yystack[$this->yyidx + -3]->minor[0],'modifier')) {
                                                                      $this->_retvalue .= "\$_smarty_tpl->smarty->plugin_handler->".$this->yystack[$this->yyidx + -3]->minor[0] . "(array(ob_get_clean()". $this->yystack[$this->yyidx + -2]->minor ."),'modifier');?>";
                                                                 } else {
                                                                   if ($this->yystack[$this->yyidx + -3]->minor[0] == 'isset' || $this->yystack[$this->yyidx + -3]->minor[0] == 'empty' || is_callable($this->yystack[$this->yyidx + -3]->minor[0])) {
																					                            if (!$this->template->security || $this->smarty->security_handler->isTrustedModifier($this->yystack[$this->yyidx + -3]->minor[0], $this->compiler)) {
																					                              $this->_retvalue .= $this->yystack[$this->yyidx + -3]->minor[0] . "(ob_get_clean()". $this->yystack[$this->yyidx + -2]->minor .");?>";
																					                            }
																					                         } else {
                                                                      $this->compiler->trigger_template_error ("unknown modifier \"" . $this->yystack[$this->yyidx + -3]->minor[0] . "\"");
                                                                 }
                                                              }
                                                                }
#line 1814 "internal.templateparser.php"
#line 171 "internal.templateparser.y"
    function yy_r18(){ $this->_retvalue =  $this->compiler->compileTag($this->yystack[$this->yyidx + -2]->minor.'close',$this->yystack[$this->yyidx + -1]->minor);    }
#line 1817 "internal.templateparser.php"
#line 173 "internal.templateparser.y"
    function yy_r19(){ $this->_retvalue =  $this->compiler->compileTag($this->yystack[$this->yyidx + -3]->minor.'close',array('object_methode'=>$this->yystack[$this->yyidx + -1]->minor));    }
#line 1820 "internal.templateparser.php"
#line 175 "internal.templateparser.y"
    function yy_r20(){ $this->_retvalue =  $this->compiler->compileTag($this->yystack[$this->yyidx + -3]->minor,array('if condition'=>$this->yystack[$this->yyidx + -1]->minor));    }
#line 1823 "internal.templateparser.php"
#line 177 "internal.templateparser.y"
    function yy_r21(){ $this->_retvalue =  $this->compiler->compileTag($this->yystack[$this->yyidx + -9]->minor,array('start'=>$this->yystack[$this->yyidx + -7]->minor,'ifexp'=>$this->yystack[$this->yyidx + -5]->minor,'varloop'=>$this->yystack[$this->yyidx + -2]->minor,'loop'=>$this->yystack[$this->yyidx + -1]->minor));    }
#line 1826 "internal.templateparser.php"
#line 178 "internal.templateparser.y"
    function yy_r22(){ $this->_retvalue = '='.$this->yystack[$this->yyidx + 0]->minor;    }
#line 1829 "internal.templateparser.php"
#line 179 "internal.templateparser.y"
    function yy_r23(){ $this->_retvalue = $this->yystack[$this->yyidx + 0]->minor;    }
#line 1832 "internal.templateparser.php"
#line 182 "internal.templateparser.y"
    function yy_r24(){ $this->_retvalue =  $this->compiler->compileTag($this->yystack[$this->yyidx + -6]->minor,array('from'=>$this->yystack[$this->yyidx + -1]->minor,'item'=>$this->yystack[$this->yyidx + -3]->minor));    }
#line 1835 "internal.templateparser.php"
#line 189 "internal.templateparser.y"
    function yy_r26(){ $this->_retvalue = array_merge($this->yystack[$this->yyidx + -1]->minor,$this->yystack[$this->yyidx + 0]->minor);    }
#line 1838 "internal.templateparser.php"
#line 193 "internal.templateparser.y"
    function yy_r28(){ $this->_retvalue = array();    }
#line 1841 "internal.templateparser.php"
#line 197 "internal.templateparser.y"
    function yy_r29(){ $this->_retvalue = array($this->yystack[$this->yyidx + -2]->minor=>$this->yystack[$this->yyidx + 0]->minor);    }
#line 1844 "internal.templateparser.php"
#line 202 "internal.templateparser.y"
    function yy_r30(){ $this->_retvalue = array($this->yystack[$this->yyidx + 0]->minor);    }
#line 1847 "internal.templateparser.php"
#line 203 "internal.templateparser.y"
    function yy_r31(){ $this->yystack[$this->yyidx + -2]->minor[]=$this->yystack[$this->yyidx + 0]->minor; $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor;    }
#line 1850 "internal.templateparser.php"
#line 205 "internal.templateparser.y"
    function yy_r32(){ $this->_retvalue = array('var' => $this->yystack[$this->yyidx + -2]->minor, 'value'=>$this->yystack[$this->yyidx + 0]->minor);    }
#line 1853 "internal.templateparser.php"
#line 212 "internal.templateparser.y"
    function yy_r33(){ $this->_retvalue = '\''.$this->yystack[$this->yyidx + 0]->minor.'\'';     }
#line 1856 "internal.templateparser.php"
#line 216 "internal.templateparser.y"
    function yy_r35(){             
                                                            if ($this->smarty->plugin_handler->loadSmartyPlugin($this->yystack[$this->yyidx + -1]->minor[0],'modifier')) {
                                                                      $this->_retvalue = "\$_smarty_tpl->smarty->plugin_handler->".$this->yystack[$this->yyidx + -1]->minor[0] . "(array(". $this->yystack[$this->yyidx + -2]->minor . $this->yystack[$this->yyidx + 0]->minor ."),'modifier')";
                                                                 } else {
                                                                   if ($this->yystack[$this->yyidx + -1]->minor[0] == 'isset' || $this->yystack[$this->yyidx + -1]->minor[0] == 'empty' || is_callable($this->yystack[$this->yyidx + -1]->minor[0])) {
																					                            if (!$this->template->security || $this->smarty->security_handler->isTrustedModifier($this->yystack[$this->yyidx + -1]->minor[0], $this->compiler)) {
																					                               $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor[0] . "(". $this->yystack[$this->yyidx + -2]->minor . $this->yystack[$this->yyidx + 0]->minor .")";
																					                            }
																					                         } else {
                                                                      $this->compiler->trigger_template_error ("unknown modifier \"" . $this->yystack[$this->yyidx + -1]->minor[0] . "\"");
                                                                 }
                                                              }
                                                                }
#line 1871 "internal.templateparser.php"
#line 234 "internal.templateparser.y"
    function yy_r38(){ $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor.$this->yystack[$this->yyidx + 0]->minor;     }
#line 1874 "internal.templateparser.php"
#line 236 "internal.templateparser.y"
    function yy_r39(){ $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor . $this->yystack[$this->yyidx + -1]->minor . $this->yystack[$this->yyidx + 0]->minor;     }
#line 1877 "internal.templateparser.php"
#line 238 "internal.templateparser.y"
    function yy_r40(){ $this->_retvalue = '('. $this->yystack[$this->yyidx + -2]->minor . ').(' . $this->yystack[$this->yyidx + 0]->minor. ')';     }
#line 1880 "internal.templateparser.php"
#line 252 "internal.templateparser.y"
    function yy_r44(){$this->_retvalue = '$_smarty_tpl->getConfigVariable(\''. $this->yystack[$this->yyidx + -1]->minor .'\')';    }
#line 1883 "internal.templateparser.php"
#line 263 "internal.templateparser.y"
    function yy_r49(){ $this->_retvalue = "(". $this->yystack[$this->yyidx + -1]->minor .")";     }
#line 1886 "internal.templateparser.php"
#line 266 "internal.templateparser.y"
    function yy_r50(){ $this->_retvalue = "'".$this->yystack[$this->yyidx + -1]->minor."'";     }
#line 1889 "internal.templateparser.php"
#line 267 "internal.templateparser.y"
    function yy_r51(){ $this->_retvalue = "''";     }
#line 1892 "internal.templateparser.php"
#line 269 "internal.templateparser.y"
    function yy_r52(){ $this->_retvalue = "'".str_replace('\"','"',$this->yystack[$this->yyidx + -1]->minor)."'";     }
#line 1895 "internal.templateparser.php"
#line 275 "internal.templateparser.y"
    function yy_r54(){ $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor.'::'.$this->yystack[$this->yyidx + 0]->minor;     }
#line 1898 "internal.templateparser.php"
#line 276 "internal.templateparser.y"
    function yy_r55(){ $this->prefix_number++; $this->prefix_code[] = '<?php $_tmp'.$this->prefix_number.'=$_smarty_tpl->getVariable(\''. $this->yystack[$this->yyidx + -3]->minor .'\')->value;?>'; $this->_retvalue = $this->yystack[$this->yyidx + -6]->minor.'::$_tmp'.$this->prefix_number.'('. $this->yystack[$this->yyidx + -1]->minor .')';     }
#line 1901 "internal.templateparser.php"
#line 278 "internal.templateparser.y"
    function yy_r56(){ $this->_retvalue = $this->yystack[$this->yyidx + -3]->minor.'::'.$this->yystack[$this->yyidx + -1]->minor.$this->yystack[$this->yyidx + 0]->minor;     }
#line 1904 "internal.templateparser.php"
#line 279 "internal.templateparser.y"
    function yy_r57(){ $this->prefix_number++; $this->prefix_code[] = '<?php $_tmp'.$this->prefix_number.'=$_smarty_tpl->getVariable(\''. $this->yystack[$this->yyidx + -4]->minor .'\')->value;?>'; $this->_retvalue = $this->yystack[$this->yyidx + -7]->minor.'::$_tmp'.$this->prefix_number.'('. $this->yystack[$this->yyidx + -2]->minor .')'.$this->yystack[$this->yyidx + 0]->minor;     }
#line 1907 "internal.templateparser.php"
#line 281 "internal.templateparser.y"
    function yy_r58(){ $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor.'::'.$this->yystack[$this->yyidx + 0]->minor;    }
#line 1910 "internal.templateparser.php"
#line 283 "internal.templateparser.y"
    function yy_r59(){ $this->_retvalue = $this->yystack[$this->yyidx + -4]->minor.'::$'.$this->yystack[$this->yyidx + -1]->minor.$this->yystack[$this->yyidx + 0]->minor;    }
#line 1913 "internal.templateparser.php"
#line 285 "internal.templateparser.y"
    function yy_r60(){ $this->_retvalue = $this->yystack[$this->yyidx + -5]->minor.'::$'.$this->yystack[$this->yyidx + -2]->minor.$this->yystack[$this->yyidx + -1]->minor.$this->yystack[$this->yyidx + 0]->minor;    }
#line 1916 "internal.templateparser.php"
#line 292 "internal.templateparser.y"
    function yy_r61(){ if ($this->yystack[$this->yyidx + -1]->minor == '\'smarty\'') { $this->_retvalue =  $this->compiler->compileTag(trim($this->yystack[$this->yyidx + -1]->minor,"'"),$this->yystack[$this->yyidx + 0]->minor);} else {
                                                         $this->_retvalue = '$_smarty_tpl->getVariable('. $this->yystack[$this->yyidx + -1]->minor .')->value'.$this->yystack[$this->yyidx + 0]->minor; $this->nocache=$this->template->getVariable(trim($this->yystack[$this->yyidx + -1]->minor,"'"))->nocache;}    }
#line 1920 "internal.templateparser.php"
#line 295 "internal.templateparser.y"
    function yy_r62(){ $this->_retvalue = '$_smarty_tpl->getVariable('. $this->yystack[$this->yyidx + -2]->minor .')->'.$this->yystack[$this->yyidx + 0]->minor; $this->nocache=$this->template->getVariable(trim($this->yystack[$this->yyidx + -2]->minor,"'"))->nocache;    }
#line 1923 "internal.templateparser.php"
#line 307 "internal.templateparser.y"
    function yy_r65(){return;    }
#line 1926 "internal.templateparser.php"
#line 311 "internal.templateparser.y"
    function yy_r66(){ $this->_retvalue = "['". $this->yystack[$this->yyidx + 0]->minor ."']";    }
#line 1929 "internal.templateparser.php"
#line 312 "internal.templateparser.y"
    function yy_r67(){ $this->_retvalue = "[". $this->yystack[$this->yyidx + 0]->minor ."]";    }
#line 1932 "internal.templateparser.php"
#line 314 "internal.templateparser.y"
    function yy_r68(){ $this->_retvalue = '['.$this->compiler->compileTag('smarty','[\'section\'][\''.$this->yystack[$this->yyidx + -1]->minor.'\'][\'index\']').']';    }
#line 1935 "internal.templateparser.php"
#line 316 "internal.templateparser.y"
    function yy_r69(){ $this->_retvalue = "[". $this->yystack[$this->yyidx + -1]->minor ."]";    }
#line 1938 "internal.templateparser.php"
#line 324 "internal.templateparser.y"
    function yy_r71(){$this->_retvalue = $this->yystack[$this->yyidx + -1]->minor.'.'.$this->yystack[$this->yyidx + 0]->minor;    }
#line 1941 "internal.templateparser.php"
#line 326 "internal.templateparser.y"
    function yy_r72(){$this->_retvalue = '\''.$this->yystack[$this->yyidx + 0]->minor.'\'';    }
#line 1944 "internal.templateparser.php"
#line 328 "internal.templateparser.y"
    function yy_r73(){$this->_retvalue = '('.$this->yystack[$this->yyidx + -1]->minor.')';    }
#line 1947 "internal.templateparser.php"
#line 333 "internal.templateparser.y"
    function yy_r74(){ $this->_retvalue = '$_smarty_tpl->getVariable('. $this->yystack[$this->yyidx + -2]->minor .')->value'.$this->yystack[$this->yyidx + -1]->minor.$this->yystack[$this->yyidx + 0]->minor; $this->nocache=$this->template->getVariable(trim($this->yystack[$this->yyidx + -2]->minor,"'"))->nocache;    }
#line 1950 "internal.templateparser.php"
#line 335 "internal.templateparser.y"
    function yy_r75(){$this->_retvalue  = $this->yystack[$this->yyidx + 0]->minor;     }
#line 1953 "internal.templateparser.php"
#line 337 "internal.templateparser.y"
    function yy_r76(){$this->_retvalue  = $this->yystack[$this->yyidx + -1]->minor.$this->yystack[$this->yyidx + 0]->minor;     }
#line 1956 "internal.templateparser.php"
#line 339 "internal.templateparser.y"
    function yy_r77(){ $this->_retvalue = '->'.$this->yystack[$this->yyidx + -1]->minor.$this->yystack[$this->yyidx + 0]->minor;    }
#line 1959 "internal.templateparser.php"
#line 342 "internal.templateparser.y"
    function yy_r78(){ $this->_retvalue = '->'.$this->yystack[$this->yyidx + 0]->minor;    }
#line 1962 "internal.templateparser.php"
#line 348 "internal.templateparser.y"
    function yy_r79(){if (!$this->template->security || $this->smarty->security_handler->isTrustedPhpFunction($this->yystack[$this->yyidx + -3]->minor, $this->compiler)) {
																					            if ($this->yystack[$this->yyidx + -3]->minor == 'isset' || $this->yystack[$this->yyidx + -3]->minor == 'empty' || is_callable($this->yystack[$this->yyidx + -3]->minor)) {
																					                $this->_retvalue = $this->yystack[$this->yyidx + -3]->minor . "(". $this->yystack[$this->yyidx + -1]->minor .")";
																					            } else {
                                                       $this->compiler->trigger_template_error ("unknown function \"" . $this->yystack[$this->yyidx + -3]->minor . "\"");
                                                      }
                                                    }    }
#line 1971 "internal.templateparser.php"
#line 359 "internal.templateparser.y"
    function yy_r80(){ $this->_retvalue = $this->yystack[$this->yyidx + -3]->minor . "(". $this->yystack[$this->yyidx + -1]->minor .")";    }
#line 1974 "internal.templateparser.php"
#line 363 "internal.templateparser.y"
    function yy_r81(){ $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor.",".$this->yystack[$this->yyidx + 0]->minor;    }
#line 1977 "internal.templateparser.php"
#line 367 "internal.templateparser.y"
    function yy_r83(){ return;    }
#line 1980 "internal.templateparser.php"
#line 372 "internal.templateparser.y"
    function yy_r84(){ $this->_retvalue =  array($this->yystack[$this->yyidx + 0]->minor,true);    }
#line 1983 "internal.templateparser.php"
#line 373 "internal.templateparser.y"
    function yy_r85(){ $this->_retvalue =  array($this->yystack[$this->yyidx + 0]->minor,false);    }
#line 1986 "internal.templateparser.php"
#line 380 "internal.templateparser.y"
    function yy_r86(){ $this->_retvalue = $this->yystack[$this->yyidx + -1]->minor.$this->yystack[$this->yyidx + 0]->minor;    }
#line 1989 "internal.templateparser.php"
#line 384 "internal.templateparser.y"
    function yy_r88(){$this->_retvalue = ',\''.$this->yystack[$this->yyidx + 0]->minor.'\'';    }
#line 1992 "internal.templateparser.php"
#line 385 "internal.templateparser.y"
    function yy_r89(){$this->_retvalue = ','.$this->yystack[$this->yyidx + 0]->minor;    }
#line 1995 "internal.templateparser.php"
#line 392 "internal.templateparser.y"
    function yy_r91(){$this->_retvalue = '!'.$this->yystack[$this->yyidx + 0]->minor;    }
#line 1998 "internal.templateparser.php"
#line 397 "internal.templateparser.y"
    function yy_r93(){$this->_retvalue =$this->yystack[$this->yyidx + 0]->minor;    }
#line 2001 "internal.templateparser.php"
#line 398 "internal.templateparser.y"
    function yy_r94(){$this->_retvalue = $this->yystack[$this->yyidx + -2]->minor.$this->yystack[$this->yyidx + -1]->minor.$this->yystack[$this->yyidx + 0]->minor;    }
#line 2004 "internal.templateparser.php"
#line 400 "internal.templateparser.y"
    function yy_r96(){$this->_retvalue = '!('.$this->yystack[$this->yyidx + -2]->minor.' % '.$this->yystack[$this->yyidx + 0]->minor.')';    }
#line 2007 "internal.templateparser.php"
#line 401 "internal.templateparser.y"
    function yy_r97(){$this->_retvalue = '('.$this->yystack[$this->yyidx + -2]->minor.' % '.$this->yystack[$this->yyidx + 0]->minor.')';    }
#line 2010 "internal.templateparser.php"
#line 402 "internal.templateparser.y"
    function yy_r98(){$this->_retvalue = '!(1 & '.$this->yystack[$this->yyidx + -1]->minor.')';    }
#line 2013 "internal.templateparser.php"
#line 403 "internal.templateparser.y"
    function yy_r99(){$this->_retvalue = '(1 & '.$this->yystack[$this->yyidx + -1]->minor.')';    }
#line 2016 "internal.templateparser.php"
#line 404 "internal.templateparser.y"
    function yy_r100(){$this->_retvalue = '!(1 & '.$this->yystack[$this->yyidx + -2]->minor.' / '.$this->yystack[$this->yyidx + 0]->minor.')';    }
#line 2019 "internal.templateparser.php"
#line 405 "internal.templateparser.y"
    function yy_r101(){$this->_retvalue = '(1 & '.$this->yystack[$this->yyidx + -2]->minor.' / '.$this->yystack[$this->yyidx + 0]->minor.')';    }
#line 2022 "internal.templateparser.php"
#line 411 "internal.templateparser.y"
    function yy_r106(){$this->_retvalue = '==';    }
#line 2025 "internal.templateparser.php"
#line 412 "internal.templateparser.y"
    function yy_r107(){$this->_retvalue = '!=';    }
#line 2028 "internal.templateparser.php"
#line 413 "internal.templateparser.y"
    function yy_r108(){$this->_retvalue = '>';    }
#line 2031 "internal.templateparser.php"
#line 414 "internal.templateparser.y"
    function yy_r109(){$this->_retvalue = '<';    }
#line 2034 "internal.templateparser.php"
#line 415 "internal.templateparser.y"
    function yy_r110(){$this->_retvalue = '>=';    }
#line 2037 "internal.templateparser.php"
#line 416 "internal.templateparser.y"
    function yy_r111(){$this->_retvalue = '<=';    }
#line 2040 "internal.templateparser.php"
#line 417 "internal.templateparser.y"
    function yy_r112(){$this->_retvalue = '===';    }
#line 2043 "internal.templateparser.php"
#line 418 "internal.templateparser.y"
    function yy_r113(){$this->_retvalue = '!==';    }
#line 2046 "internal.templateparser.php"
#line 420 "internal.templateparser.y"
    function yy_r114(){$this->_retvalue = '&&';    }
#line 2049 "internal.templateparser.php"
#line 421 "internal.templateparser.y"
    function yy_r115(){$this->_retvalue = '||';    }
#line 2052 "internal.templateparser.php"
#line 426 "internal.templateparser.y"
    function yy_r116(){ $this->_retvalue = 'array('.$this->yystack[$this->yyidx + -1]->minor.')';    }
#line 2055 "internal.templateparser.php"
#line 428 "internal.templateparser.y"
    function yy_r118(){ $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor.','.$this->yystack[$this->yyidx + 0]->minor;     }
#line 2058 "internal.templateparser.php"
#line 429 "internal.templateparser.y"
    function yy_r119(){ return;     }
#line 2061 "internal.templateparser.php"
#line 431 "internal.templateparser.y"
    function yy_r121(){ $this->_retvalue = $this->yystack[$this->yyidx + -2]->minor.'=>'.$this->yystack[$this->yyidx + 0]->minor;    }
#line 2064 "internal.templateparser.php"
#line 432 "internal.templateparser.y"
    function yy_r122(){ $this->_retvalue = '\''.$this->yystack[$this->yyidx + -2]->minor.'\'=>'.$this->yystack[$this->yyidx + 0]->minor;    }
#line 2067 "internal.templateparser.php"
#line 439 "internal.templateparser.y"
    function yy_r125(){$this->_retvalue = "'.".$this->yystack[$this->yyidx + 0]->minor.".'";    }
#line 2070 "internal.templateparser.php"
#line 440 "internal.templateparser.y"
    function yy_r126(){$this->_retvalue = "'.".$this->yystack[$this->yyidx + -1]->minor.".'";    }
#line 2073 "internal.templateparser.php"
#line 441 "internal.templateparser.y"
    function yy_r127(){$this->_retvalue = "'.(".$this->yystack[$this->yyidx + -1]->minor.").'";    }
#line 2076 "internal.templateparser.php"
#line 442 "internal.templateparser.y"
    function yy_r128(){$this->_retvalue = addcslashes($this->yystack[$this->yyidx + 0]->minor,"'");    }
#line 2079 "internal.templateparser.php"

    /**
     * placeholder for the left hand side in a reduce operation.
     * 
     * For a parser with a rule like this:
     * <pre>
     * rule(A) ::= B. { A = 1; }
     * </pre>
     * 
     * The parser will translate to something like:
     * 
     * <code>
     * function yy_r0(){$this->_retvalue = 1;}
     * </code>
     */
    private $_retvalue;

    /**
     * Perform a reduce action and the shift that must immediately
     * follow the reduce.
     * 
     * For a rule such as:
     * 
     * <pre>
     * A ::= B blah C. { dosomething(); }
     * </pre>
     * 
     * This function will first call the action, if any, ("dosomething();" in our
     * example), and then it will pop three states from the stack,
     * one for each entry on the right-hand side of the expression
     * (B, blah, and C in our example rule), and then push the result of the action
     * back on to the stack with the resulting state reduced to (as described in the .out
     * file)
     * @param int Number of the rule by which to reduce
     */
    function yy_reduce($yyruleno)
    {
        //int $yygoto;                     /* The next state */
        //int $yyact;                      /* The next action */
        //mixed $yygotominor;        /* The LHS of the rule reduced */
        //TP_yyStackEntry $yymsp;            /* The top of the parser's stack */
        //int $yysize;                     /* Amount to pop the stack */
        $yymsp = $this->yystack[$this->yyidx];
        if (self::$yyTraceFILE && $yyruleno >= 0 
              && $yyruleno < count(self::$yyRuleName)) {
            fprintf(self::$yyTraceFILE, "%sReduce (%d) [%s].\n",
                self::$yyTracePrompt, $yyruleno,
                self::$yyRuleName[$yyruleno]);
        }

        $this->_retvalue = $yy_lefthand_side = null;
        if (array_key_exists($yyruleno, self::$yyReduceMap)) {
            // call the action
            $this->_retvalue = null;
            $this->{'yy_r' . self::$yyReduceMap[$yyruleno]}();
            $yy_lefthand_side = $this->_retvalue;
        }
        $yygoto = self::$yyRuleInfo[$yyruleno]['lhs'];
        $yysize = self::$yyRuleInfo[$yyruleno]['rhs'];
        $this->yyidx -= $yysize;
        for($i = $yysize; $i; $i--) {
            // pop all of the right-hand side parameters
            array_pop($this->yystack);
        }
        $yyact = $this->yy_find_reduce_action($this->yystack[$this->yyidx]->stateno, $yygoto);
        if ($yyact < self::YYNSTATE) {
            /* If we are not debugging and the reduce action popped at least
            ** one element off the stack, then we can push the new element back
            ** onto the stack here, and skip the stack overflow test in yy_shift().
            ** That gives a significant speed improvement. */
            if (!self::$yyTraceFILE && $yysize) {
                $this->yyidx++;
                $x = new TP_yyStackEntry;
                $x->stateno = $yyact;
                $x->major = $yygoto;
                $x->minor = $yy_lefthand_side;
                $this->yystack[$this->yyidx] = $x;
            } else {
                $this->yy_shift($yyact, $yygoto, $yy_lefthand_side);
            }
        } elseif ($yyact == self::YYNSTATE + self::YYNRULE + 1) {
            $this->yy_accept();
        }
    }

    /**
     * The following code executes when the parse fails
     * 
     * Code from %parse_fail is inserted here
     */
    function yy_parse_failed()
    {
        if (self::$yyTraceFILE) {
            fprintf(self::$yyTraceFILE, "%sFail!\n", self::$yyTracePrompt);
        }
        while ($this->yyidx >= 0) {
            $this->yy_pop_parser_stack();
        }
        /* Here code is inserted which will be executed whenever the
        ** parser fails */
    }

    /**
     * The following code executes when a syntax error first occurs.
     * 
     * %syntax_error code is inserted here
     * @param int The major type of the error token
     * @param mixed The minor type of the error token
     */
    function yy_syntax_error($yymajor, $TOKEN)
    {
#line 55 "internal.templateparser.y"

    $this->internalError = true;
    $this->yymajor = $yymajor;
    $this->compiler->trigger_template_error();
#line 2197 "internal.templateparser.php"
    }

    /**
     * The following is executed when the parser accepts
     * 
     * %parse_accept code is inserted here
     */
    function yy_accept()
    {
        if (self::$yyTraceFILE) {
            fprintf(self::$yyTraceFILE, "%sAccept!\n", self::$yyTracePrompt);
        }
        while ($this->yyidx >= 0) {
            $stack = $this->yy_pop_parser_stack();
        }
        /* Here code is inserted which will be executed whenever the
        ** parser accepts */
#line 47 "internal.templateparser.y"

    $this->successful = !$this->internalError;
    $this->internalError = false;
    $this->retvalue = $this->_retvalue;
    //echo $this->retvalue."\n\n";
#line 2222 "internal.templateparser.php"
    }

    /**
     * The main parser program.
     * 
     * The first argument is the major token number.  The second is
     * the token value string as scanned from the input.
     *
     * @param int the token number
     * @param mixed the token value
     * @param mixed any extra arguments that should be passed to handlers
     */
    function doParse($yymajor, $yytokenvalue)
    {
//        $yyact;            /* The parser action. */
//        $yyendofinput;     /* True if we are at the end of input */
        $yyerrorhit = 0;   /* True if yymajor has invoked an error */
        
        /* (re)initialize the parser, if necessary */
        if ($this->yyidx === null || $this->yyidx < 0) {
            /* if ($yymajor == 0) return; // not sure why this was here... */
            $this->yyidx = 0;
            $this->yyerrcnt = -1;
            $x = new TP_yyStackEntry;
            $x->stateno = 0;
            $x->major = 0;
            $this->yystack = array();
            array_push($this->yystack, $x);
        }
        $yyendofinput = ($yymajor==0);
        
        if (self::$yyTraceFILE) {
            fprintf(self::$yyTraceFILE, "%sInput %s\n",
                self::$yyTracePrompt, $this->yyTokenName[$yymajor]);
        }
        
        do {
            $yyact = $this->yy_find_shift_action($yymajor);
            if ($yymajor < self::YYERRORSYMBOL &&
                  !$this->yy_is_expected_token($yymajor)) {
                // force a syntax error
                $yyact = self::YY_ERROR_ACTION;
            }
            if ($yyact < self::YYNSTATE) {
                $this->yy_shift($yyact, $yymajor, $yytokenvalue);
                $this->yyerrcnt--;
                if ($yyendofinput && $this->yyidx >= 0) {
                    $yymajor = 0;
                } else {
                    $yymajor = self::YYNOCODE;
                }
            } elseif ($yyact < self::YYNSTATE + self::YYNRULE) {
                $this->yy_reduce($yyact - self::YYNSTATE);
            } elseif ($yyact == self::YY_ERROR_ACTION) {
                if (self::$yyTraceFILE) {
                    fprintf(self::$yyTraceFILE, "%sSyntax Error!\n",
                        self::$yyTracePrompt);
                }
                if (self::YYERRORSYMBOL) {
                    /* A syntax error has occurred.
                    ** The response to an error depends upon whether or not the
                    ** grammar defines an error token "ERROR".  
                    **
                    ** This is what we do if the grammar does define ERROR:
                    **
                    **  * Call the %syntax_error function.
                    **
                    **  * Begin popping the stack until we enter a state where
                    **    it is legal to shift the error symbol, then shift
                    **    the error symbol.
                    **
                    **  * Set the error count to three.
                    **
                    **  * Begin accepting and shifting new tokens.  No new error
                    **    processing will occur until three tokens have been
                    **    shifted successfully.
                    **
                    */
                    if ($this->yyerrcnt < 0) {
                        $this->yy_syntax_error($yymajor, $yytokenvalue);
                    }
                    $yymx = $this->yystack[$this->yyidx]->major;
                    if ($yymx == self::YYERRORSYMBOL || $yyerrorhit ){
                        if (self::$yyTraceFILE) {
                            fprintf(self::$yyTraceFILE, "%sDiscard input token %s\n",
                                self::$yyTracePrompt, $this->yyTokenName[$yymajor]);
                        }
                        $this->yy_destructor($yymajor, $yytokenvalue);
                        $yymajor = self::YYNOCODE;
                    } else {
                        while ($this->yyidx >= 0 &&
                                 $yymx != self::YYERRORSYMBOL &&
        ($yyact = $this->yy_find_shift_action(self::YYERRORSYMBOL)) >= self::YYNSTATE
                              ){
                            $this->yy_pop_parser_stack();
                        }
                        if ($this->yyidx < 0 || $yymajor==0) {
                            $this->yy_destructor($yymajor, $yytokenvalue);
                            $this->yy_parse_failed();
                            $yymajor = self::YYNOCODE;
                        } elseif ($yymx != self::YYERRORSYMBOL) {
                            $u2 = 0;
                            $this->yy_shift($yyact, self::YYERRORSYMBOL, $u2);
                        }
                    }
                    $this->yyerrcnt = 3;
                    $yyerrorhit = 1;
                } else {
                    /* YYERRORSYMBOL is not defined */
                    /* This is what we do if the grammar does not define ERROR:
                    **
                    **  * Report an error message, and throw away the input token.
                    **
                    **  * If the input token is $, then fail the parse.
                    **
                    ** As before, subsequent error messages are suppressed until
                    ** three input tokens have been successfully shifted.
                    */
                    if ($this->yyerrcnt <= 0) {
                        $this->yy_syntax_error($yymajor, $yytokenvalue);
                    }
                    $this->yyerrcnt = 3;
                    $this->yy_destructor($yymajor, $yytokenvalue);
                    if ($yyendofinput) {
                        $this->yy_parse_failed();
                    }
                    $yymajor = self::YYNOCODE;
                }
            } else {
                $this->yy_accept();
                $yymajor = self::YYNOCODE;
            }            
        } while ($yymajor != self::YYNOCODE && $this->yyidx >= 0);
    }
}