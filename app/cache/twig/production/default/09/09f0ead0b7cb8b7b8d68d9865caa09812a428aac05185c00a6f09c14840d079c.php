<?php

/* default_frame.twig */
class __TwigTemplate_7c3c6eabf93aa99bbd2b6d295f92128b7d451afc6caff4c6dd06e3e179b3f661 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'meta_tags' => array($this, 'block_meta_tags'),
            'stylesheet' => array($this, 'block_stylesheet'),
            'main' => array($this, 'block_main'),
            'javascript' => array($this, 'block_javascript'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<!doctype html>
";
        // line 23
        echo "<html lang=\"ja\">
<head>
<meta charset=\"utf-8\">
<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
<title>";
        // line 27
        echo twig_escape_filter($this->env, $this->getAttribute(($context["BaseInfo"] ?? null), "shop_name", array()), "html", null, true);
        if ((array_key_exists("subtitle", $context) &&  !twig_test_empty(($context["subtitle"] ?? null)))) {
            echo " / ";
            echo twig_escape_filter($this->env, ($context["subtitle"] ?? null), "html", null, true);
        } elseif ((array_key_exists("title", $context) &&  !twig_test_empty(($context["title"] ?? null)))) {
            echo " / ";
            echo twig_escape_filter($this->env, ($context["title"] ?? null), "html", null, true);
        }
        echo "</title>
";
        // line 28
        if ( !twig_test_empty($this->getAttribute(($context["PageLayout"] ?? null), "author", array()))) {
            // line 29
            echo "    <meta name=\"author\" content=\"";
            echo twig_escape_filter($this->env, $this->getAttribute(($context["PageLayout"] ?? null), "author", array()), "html", null, true);
            echo "\">
";
        }
        // line 31
        if ( !twig_test_empty($this->getAttribute(($context["PageLayout"] ?? null), "description", array()))) {
            // line 32
            echo "    <meta name=\"description\" content=\"";
            echo twig_escape_filter($this->env, $this->getAttribute(($context["PageLayout"] ?? null), "description", array()), "html", null, true);
            echo "\">
";
        }
        // line 34
        if ( !twig_test_empty($this->getAttribute(($context["PageLayout"] ?? null), "keyword", array()))) {
            // line 35
            echo "    <meta name=\"keywords\" content=\"";
            echo twig_escape_filter($this->env, $this->getAttribute(($context["PageLayout"] ?? null), "keyword", array()), "html", null, true);
            echo "\">
";
        }
        // line 37
        if ( !twig_test_empty($this->getAttribute(($context["PageLayout"] ?? null), "meta_robots", array()))) {
            // line 38
            echo "    <meta name=\"robots\" content=\"";
            echo twig_escape_filter($this->env, $this->getAttribute(($context["PageLayout"] ?? null), "meta_robots", array()), "html", null, true);
            echo "\">
";
        }
        // line 40
        echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
";
        // line 41
        if ( !twig_test_empty($this->getAttribute(($context["PageLayout"] ?? null), "meta_tags", array()))) {
            // line 42
            echo "    ";
            echo $this->getAttribute(($context["PageLayout"] ?? null), "meta_tags", array());
            echo "
";
        }
        // line 44
        $this->displayBlock('meta_tags', $context, $blocks);
        // line 45
        echo "<link rel=\"icon\" href=\"";
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["app"] ?? null), "config", array()), "front_urlpath", array()), "html", null, true);
        echo "/img/common/favicon.ico\">
<link rel=\"stylesheet\" href=\"";
        // line 46
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["app"] ?? null), "config", array()), "front_urlpath", array()), "html", null, true);
        echo "/css/style.css?v=";
        echo twig_escape_filter($this->env, twig_constant("Eccube\\Common\\Constant::VERSION"), "html", null, true);
        echo "\">
<link rel=\"stylesheet\" href=\"";
        // line 47
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["app"] ?? null), "config", array()), "front_urlpath", array()), "html", null, true);
        echo "/css/slick.css?v=";
        echo twig_escape_filter($this->env, twig_constant("Eccube\\Common\\Constant::VERSION"), "html", null, true);
        echo "\">
<link rel=\"stylesheet\" href=\"";
        // line 48
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["app"] ?? null), "config", array()), "front_urlpath", array()), "html", null, true);
        echo "/css/default.css?v=";
        echo twig_escape_filter($this->env, twig_constant("Eccube\\Common\\Constant::VERSION"), "html", null, true);
        echo "\">
<!-- for original theme CSS -->
";
        // line 50
        $this->displayBlock('stylesheet', $context, $blocks);
        // line 51
        echo "
<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js\"></script>
<script>window.jQuery || document.write('<script src=\"";
        // line 53
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["app"] ?? null), "config", array()), "front_urlpath", array()), "html", null, true);
        echo "/js/vendor/jquery-1.11.3.min.js?v=";
        echo twig_escape_filter($this->env, twig_constant("Eccube\\Common\\Constant::VERSION"), "html", null, true);
        echo "\"><\\/script>')</script>

";
        // line 56
        if ($this->getAttribute(($context["PageLayout"] ?? null), "Head", array())) {
            // line 57
            echo "    ";
            // line 58
            echo "    ";
            echo twig_include($this->env, $context, "block.twig", array("Blocks" => $this->getAttribute(($context["PageLayout"] ?? null), "Head", array())));
            echo "
    ";
        }
        // line 62
        echo "
</head>
<body id=\"page_";
        // line 64
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["app"] ?? null), "request", array()), "get", array(0 => "_route"), "method"), "html", null, true);
        echo "\" class=\"";
        echo twig_escape_filter($this->env, ((array_key_exists("body_class", $context)) ? (_twig_default_filter(($context["body_class"] ?? null), "other_page")) : ("other_page")), "html", null, true);
        echo "\">
<div id=\"wrapper\">
    <header id=\"header\">
        <div class=\"container-fluid inner\">
            ";
        // line 69
        echo "            ";
        if ($this->getAttribute(($context["PageLayout"] ?? null), "Header", array())) {
            // line 70
            echo "                ";
            // line 71
            echo "                ";
            echo twig_include($this->env, $context, "block.twig", array("Blocks" => $this->getAttribute(($context["PageLayout"] ?? null), "Header", array())));
            echo "
                ";
            // line 73
            echo "            ";
        }
        // line 74
        echo "            ";
        // line 75
        echo "            <p id=\"btn_menu\"><a class=\"nav-trigger\" href=\"#nav\">Menu<span></span></a></p>
        </div>
    </header>

    <div id=\"contents\" class=\"";
        // line 79
        echo twig_escape_filter($this->env, $this->getAttribute(($context["PageLayout"] ?? null), "theme", array()), "html", null, true);
        echo "\">

        <div id=\"contents_top\">
            ";
        // line 83
        echo "            ";
        if ($this->getAttribute(($context["PageLayout"] ?? null), "ContentsTop", array())) {
            // line 84
            echo "                ";
            // line 85
            echo "                ";
            echo twig_include($this->env, $context, "block.twig", array("Blocks" => $this->getAttribute(($context["PageLayout"] ?? null), "ContentsTop", array())));
            echo "
                ";
            // line 87
            echo "            ";
        }
        // line 88
        echo "            ";
        // line 89
        echo "        </div>

        <div class=\"container-fluid inner\">
            ";
        // line 93
        echo "            ";
        if ($this->getAttribute(($context["PageLayout"] ?? null), "SideLeft", array())) {
            // line 94
            echo "                <div id=\"side_left\" class=\"side\">
                    ";
            // line 96
            echo "                    ";
            echo twig_include($this->env, $context, "block.twig", array("Blocks" => $this->getAttribute(($context["PageLayout"] ?? null), "SideLeft", array())));
            echo "
                    ";
            // line 98
            echo "                </div>
            ";
        }
        // line 100
        echo "            ";
        // line 101
        echo "
            <div id=\"main\">
                ";
        // line 104
        echo "                ";
        if ($this->getAttribute(($context["PageLayout"] ?? null), "MainTop", array())) {
            // line 105
            echo "                    <div id=\"main_top\">
                        ";
            // line 106
            echo twig_include($this->env, $context, "block.twig", array("Blocks" => $this->getAttribute(($context["PageLayout"] ?? null), "MainTop", array())));
            echo "
                    </div>
                ";
        }
        // line 109
        echo "                ";
        // line 110
        echo "
                <div id=\"main_middle\">
                    ";
        // line 112
        $this->displayBlock('main', $context, $blocks);
        // line 113
        echo "                </div>

                ";
        // line 116
        echo "                ";
        if ($this->getAttribute(($context["PageLayout"] ?? null), "MainBottom", array())) {
            // line 117
            echo "                    <div id=\"main_bottom\">
                        ";
            // line 118
            echo twig_include($this->env, $context, "block.twig", array("Blocks" => $this->getAttribute(($context["PageLayout"] ?? null), "MainBottom", array())));
            echo "
                    </div>
                ";
        }
        // line 121
        echo "                ";
        // line 122
        echo "            </div>

            ";
        // line 125
        echo "            ";
        if ($this->getAttribute(($context["PageLayout"] ?? null), "SideRight", array())) {
            // line 126
            echo "                <div id=\"side_right\" class=\"side\">
                    ";
            // line 128
            echo "                    ";
            echo twig_include($this->env, $context, "block.twig", array("Blocks" => $this->getAttribute(($context["PageLayout"] ?? null), "SideRight", array())));
            echo "
                    ";
            // line 130
            echo "                </div>
            ";
        }
        // line 132
        echo "            ";
        // line 133
        echo "
            ";
        // line 135
        echo "            ";
        if ($this->getAttribute(($context["PageLayout"] ?? null), "ContentsBottom", array())) {
            // line 136
            echo "                <div id=\"contents_bottom\">
                    ";
            // line 138
            echo "                    ";
            echo twig_include($this->env, $context, "block.twig", array("Blocks" => $this->getAttribute(($context["PageLayout"] ?? null), "ContentsBottom", array())));
            echo "
                    ";
            // line 140
            echo "                </div>
            ";
        }
        // line 142
        echo "            ";
        // line 143
        echo "
        </div>

        <footer id=\"footer\">
            ";
        // line 148
        echo "            ";
        if ($this->getAttribute(($context["PageLayout"] ?? null), "Footer", array())) {
            // line 149
            echo "                ";
            // line 150
            echo "                ";
            echo twig_include($this->env, $context, "block.twig", array("Blocks" => $this->getAttribute(($context["PageLayout"] ?? null), "Footer", array())));
            echo "
                ";
            // line 152
            echo "            ";
        }
        // line 153
        echo "            ";
        // line 154
        echo "
        </footer>

    </div>

    <div id=\"drawer\" class=\"drawer sp\">
    </div>

</div>

<div class=\"overlay\"></div>

<script src=\"";
        // line 166
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["app"] ?? null), "config", array()), "front_urlpath", array()), "html", null, true);
        echo "/js/vendor/bootstrap.custom.min.js?v=";
        echo twig_escape_filter($this->env, twig_constant("Eccube\\Common\\Constant::VERSION"), "html", null, true);
        echo "\"></script>
<script src=\"";
        // line 167
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["app"] ?? null), "config", array()), "front_urlpath", array()), "html", null, true);
        echo "/js/vendor/slick.min.js?v=";
        echo twig_escape_filter($this->env, twig_constant("Eccube\\Common\\Constant::VERSION"), "html", null, true);
        echo "\"></script>
<script src=\"";
        // line 168
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["app"] ?? null), "config", array()), "front_urlpath", array()), "html", null, true);
        echo "/js/function.js?v=";
        echo twig_escape_filter($this->env, twig_constant("Eccube\\Common\\Constant::VERSION"), "html", null, true);
        echo "\"></script>
<script src=\"";
        // line 169
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["app"] ?? null), "config", array()), "front_urlpath", array()), "html", null, true);
        echo "/js/eccube.js?v=";
        echo twig_escape_filter($this->env, twig_constant("Eccube\\Common\\Constant::VERSION"), "html", null, true);
        echo "\"></script>
<script>
\$(function () {
    \$('#drawer').append(\$('.drawer_block').clone(true).children());
    \$.ajax({
        url: '";
        // line 174
        echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["app"] ?? null), "config", array()), "front_urlpath", array()), "html", null, true);
        echo "/img/common/svg.html',
        type: 'GET',
        dataType: 'html',
    }).done(function(data){
        \$('body').prepend(data);
    }).fail(function(data){
    });
});
</script>
";
        // line 183
        $this->displayBlock('javascript', $context, $blocks);
        // line 184
        echo "</body>
</html>
";
    }

    // line 44
    public function block_meta_tags($context, array $blocks = array())
    {
    }

    // line 50
    public function block_stylesheet($context, array $blocks = array())
    {
    }

    // line 112
    public function block_main($context, array $blocks = array())
    {
    }

    // line 183
    public function block_javascript($context, array $blocks = array())
    {
    }

    public function getTemplateName()
    {
        return "default_frame.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  393 => 183,  388 => 112,  383 => 50,  378 => 44,  372 => 184,  370 => 183,  358 => 174,  348 => 169,  342 => 168,  336 => 167,  330 => 166,  316 => 154,  314 => 153,  311 => 152,  306 => 150,  304 => 149,  301 => 148,  295 => 143,  293 => 142,  289 => 140,  284 => 138,  281 => 136,  278 => 135,  275 => 133,  273 => 132,  269 => 130,  264 => 128,  261 => 126,  258 => 125,  254 => 122,  252 => 121,  246 => 118,  243 => 117,  240 => 116,  236 => 113,  234 => 112,  230 => 110,  228 => 109,  222 => 106,  219 => 105,  216 => 104,  212 => 101,  210 => 100,  206 => 98,  201 => 96,  198 => 94,  195 => 93,  190 => 89,  188 => 88,  185 => 87,  180 => 85,  178 => 84,  175 => 83,  169 => 79,  163 => 75,  161 => 74,  158 => 73,  153 => 71,  151 => 70,  148 => 69,  139 => 64,  135 => 62,  129 => 58,  127 => 57,  125 => 56,  118 => 53,  114 => 51,  112 => 50,  105 => 48,  99 => 47,  93 => 46,  88 => 45,  86 => 44,  80 => 42,  78 => 41,  75 => 40,  69 => 38,  67 => 37,  61 => 35,  59 => 34,  53 => 32,  51 => 31,  45 => 29,  43 => 28,  32 => 27,  26 => 23,  23 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "default_frame.twig", "/var/www/html/liv-motor/src/Eccube/Resource/template/default/default_frame.twig");
    }
}
